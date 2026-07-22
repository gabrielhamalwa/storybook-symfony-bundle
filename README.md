# Storybook Symfony Bundle

[![CI](https://github.com/gabrielhamalwa/storybook-symfony-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/gabrielhamalwa/storybook-symfony-bundle/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/storybook/symfony-bundle/v/stable)](https://packagist.org/packages/storybook/symfony-bundle)
[![PHP Version Require](https://poser.pugx.org/storybook/symfony-bundle/require/php)](https://packagist.org/packages/storybook/symfony-bundle)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)

Symfony bundle that provides the application runtime for Storybook's Symfony/Twig framework. It renders Twig components in isolation, exposes component metadata, and extracts normalized assets for the Storybook renderer. The same bundle runs through a local PHP server during development and through PHP WebAssembly in a self-contained static build.

Learn more about Storybook at [storybook.js.org](https://storybook.js.org/?ref=readme).

## Documentation

Full documentation is in the [`docs`](./docs) directory:

- [Overview](./docs/index.mdx)
- [Installation](./docs/getting-started/installation.mdx)
- [Configuration](./docs/getting-started/configuration.mdx)
- [Component adapters](./docs/usage/adapters.mdx)
- [Asset pipelines](./docs/usage/asset-pipelines.mdx)
- [Endpoints](./docs/usage/endpoints.mdx)
- [Development](./docs/development/setup.mdx)

## What it does

This bundle is the PHP side of the Storybook Symfony/Twig integration. When you run Storybook with `@storybook/symfony-vite`, the frontend asks this bundle to:

- Render a Twig component, plain Twig template, controller fragment, or Symfony UX Live Component with a given set of args (`POST /_storybook/render/{id}`).
- Return the styles and scripts that belong to the component's asset entrypoint.
- Report health so the Storybook dev server knows the PHP backend is ready (`GET /_storybook/health`).
- List discoverable components and expose source code to development tools (`GET /_storybook/index`, `GET /_storybook/source/{id}`).

The bundle is intentionally small. It relies on Symfony UX TwigComponent by default and can also render plain Twig templates, controller fragments, and Symfony UX Live Components.

## Requirements

- PHP 8.2 or higher
- Symfony 6.4, 7.x, or 8.x
- Twig 3.8 or higher
- Symfony UX TwigComponent 2.0 or 3.0

## Installation

Install the bundle with Composer:

```bash
composer require --dev storybook/symfony-bundle
```

Enable the bundle in `config/bundles.php` if it is not registered automatically by Symfony Flex:

```php
return [
    // ...
    Storybook\SymfonyBundle\StorybookBundle::class => ['storybook' => true],
];
```

## Symfony configuration

### Routes

Add the bundle's controller to a routing file in the `storybook` environment, for example `config/routes/storybook.yaml`:

```yaml
storybook:
  resource: Storybook\SymfonyBundle\Controller\StorybookController
  type: attribute
```

The routes are attributed in `StorybookController`, so `type: attribute` is required.

### Isolated environment

Storybook boots Symfony in a dedicated `storybook` environment. Keep that environment minimal so the container compiles quickly. A typical `config/packages/storybook/framework.yaml` looks like this:

```yaml
framework:
  router:
    utf8: true
    strict_requirements: ~
  test: false
  session:
    enabled: false
```

You also need Twig, TwigComponent, and Stimulus enabled in that environment:

```yaml
# config/packages/storybook/twig.yaml
twig:
  default_path: '%kernel.project_dir%/templates'

# config/packages/storybook/twig_component.yaml
twig_component:
  anonymous_template_directory: 'components/'
  defaults:
    App\Twig\Components\: 'components/'

# config/packages/storybook/stimulus.yaml
stimulus:
  controllers_path: '%kernel.project_dir%/assets/controllers'
  controller_jsons_path: '%kernel.project_dir%/assets/controllers.json'
```

If you use AssetMapper, also add `config/packages/storybook/assets.yaml`:

```yaml
framework:
  asset_mapper:
    paths:
      assets/
    importmap_path: '%kernel.project_dir%/importmap.php'
```

## Bundle configuration

The bundle is configured under the `storybook` key in `config/packages/storybook/storybook.yaml`:

```yaml
storybook:
  asset_pipeline: auto
  entrypoint: app
  cors_allowed_origins: []
```

| Option | Type | Default | Description |
| --- | --- | --- | --- |
| `asset_pipeline` | `auto`, `pentatrion_vite`, `encore`, `asset_mapper`, `none` | `auto` | Asset pipeline to use for extracting styles and scripts. |
| `entrypoint` | `string` | `app` | Entrypoint name used to look up the component's assets. |
| `cors_allowed_origins` | `string[]` | `[]` | Origins allowed to call an explicitly configured browser-reachable backend. Standard static builds do not need CORS. |

When `asset_pipeline` is `auto`, the bundle detects the installed pipeline by checking for known Symfony services in this order:

1. Pentatrion Vite (`Pentatrion\ViteBundle\Service\EntrypointsLookupCollection`)
2. Webpack Encore (`webpack_encore.entrypoint_lookup_collection`)
3. AssetMapper (`asset_mapper.importmap.generator`)
4. None (`NullAssetPipeline`)

### Pentatrion Vite

No extra configuration is required if the bundle is installed and `pentatrion/vite-bundle` is present. The bundle reads the `entrypoint` from Vite's `entrypoints.json` and returns the matching CSS and module scripts.

### Webpack Encore

When `symfony/webpack-encore-bundle` is installed, the bundle reads from the Encore entrypoint lookup. Configure your `webpack.config.cjs` (or `webpack.config.js`) to build an `app` entrypoint that includes your Stimulus bootstrap and component styles.

```yaml
storybook:
  asset_pipeline: auto
  entrypoint: app
```

### AssetMapper

When `symfony/asset-mapper` is installed, the bundle reads the import map and eager entrypoint imports. Make sure the `app` asset is registered in `importmap.php` and any CSS files are mapped as eager imports.

```yaml
# config/packages/asset_mapper.yaml
framework:
  asset_mapper:
    paths:
      assets/
    importmap_path: '%kernel.project_dir%/importmap.php'
```

```yaml
storybook:
  asset_pipeline: auto
  entrypoint: app
```

### No asset pipeline

If your components do not require CSS or JavaScript, set `asset_pipeline: none`:

```yaml
storybook:
  asset_pipeline: none
```

## Component adapters

The bundle can render four different kinds of components. The adapter is selected from the `adapter` field in the request body or auto-detected from the provided identifier:

| Adapter | `adapter` value | Identifier / trigger | Notes |
| --- | --- | --- | --- |
| Twig component | `twig_component` (or omitted) | `componentId` such as `Button` | Default. Renders with Symfony UX TwigComponent. |
| Plain Twig template | `template` | `componentId` ending in `.twig` or a `template` field | Renders the template directly with the story args as variables. |
| Controller fragment | `controller` | `componentId` containing `::` or a `controller` field | Renders a Symfony controller fragment (`ControllerName::action`). |
| Live component | `live` | `adapter: live` or the renderer sends `adapter: live` when `live: true` is set | Requires `symfony/ux-live-component`. Falls back to an error if the package is not installed. |

When `adapter` is omitted, the bundle picks the first matching rule in this order: `template`, `controller`, `twig_component`. The `live` adapter must be requested explicitly.

## Endpoints

All endpoints are mounted under the `/_storybook` prefix.

### `GET /_storybook/health`

Returns a simple status object used by the Storybook framework to wait for the PHP backend:

```json
{
  "status": "ok"
}
```

### `POST /_storybook/render/{id}`

Renders the component identified by the request body and returns the resulting HTML plus assets.

Request body fields:

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `componentId` | `string` | Yes for Twig and Live components; optional for template/controller | Component name, template path, or controller reference. |
| `adapter` | `string` | No | Force a specific adapter: `twig_component`, `template`, `controller`, or `live`. |
| `template` | `string` | Only when `adapter` is `template` | Twig template path. If it starts with `templates/`, the prefix is stripped. |
| `controller` | `string` | Only when `adapter` is `controller` | Controller reference such as `App\Controller\AlertController::fragment`. |
| `args` | `object` | No | Props passed to the component or template. |
| `globals` | `object` | No | Storybook globals reserved for custom adapters and future integrations. |

Twig component example:

```json
{
  "componentId": "Button",
  "args": {
    "label": "Save",
    "variant": "primary"
  }
}
```

Plain template example:

```json
{
  "componentId": "components/Alert.html.twig",
  "adapter": "template",
  "args": {
    "message": "Saved successfully"
  }
}
```

Controller fragment example:

```json
{
  "controller": "App\\Controller\\AlertController::fragment",
  "adapter": "controller",
  "args": {
    "message": "Saved successfully"
  }
}
```

Live component example:

```json
{
  "componentId": "Notification",
  "adapter": "live",
  "args": {
    "message": "Saved successfully"
  }
}
```

Response body:

```json
{
  "html": "<button data-controller=\"button\" class=\"btn btn-primary\">Save</button>",
  "assets": {
    "pipeline": "pentatrion-vite",
    "styles": [
      { "url": "/build/assets/app.css" }
    ],
    "scripts": [
      { "url": "/build/assets/app.js", "type": "module" }
    ],
    "importmap": {
      "imports": {
        "@hotwired/stimulus": "/build/assets/stimulus.js"
      }
    }
  },
  "metadata": {
    "component": "Button"
  }
}
```

The `id` path parameter is the Storybook story ID; the actual component identifier is passed in the JSON body.

### `GET /_storybook/index`

Lists Twig components for the experimental auto-discovery indexer:

```json
{
  "components": [
    {
      "id": "Button",
      "type": "twig_component",
      "title": "Components/Button",
      "props": []
    }
  ]
}
```

### `GET /_storybook/source/{id}`

Returns the Twig template and PHP class source for development tooling. The Storybook docs panel generates a copy-pastable Twig invocation from each story instead.

```json
{
  "template": "<button>{{ label }}</button>",
  "class": "final class Button { /* ... */ }"
}
```

## Migration

If you are migrating from an iframe-based Symfony/Storybook integration such as `sensiolabs/StorybookBundle`, read the Storybook framework's [migration guide](https://github.com/storybookjs/storybook/blob/next/docs/get-started/frameworks/symfony-vite-migration.mdx). It covers removing iframe patches, migrating `.stories.json` files to `.stories.ts`, and configuring the minimal `storybook` environment shown above.

## Testing

The bundle uses PHPUnit. Run the test suite from the bundle root:

```bash
./vendor/bin/phpunit
```

Tests cover the asset pipelines, the compiler pass, the controller, and the CORS listener.

## Integration with Storybook

This bundle is used together with the JavaScript packages from the Storybook monorepo:

- `@storybook/symfony` — browser-side renderer that calls the endpoints above.
- `@storybook/symfony-vite` — framework that starts the PHP server, wires the renderer, and manages the Vite builder.

See the `@storybook/symfony-vite` README for the full quick start guide.

## License

This project is licensed under the Apache License 2.0 — see the [LICENSE](LICENSE) file for details.
