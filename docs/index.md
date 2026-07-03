# Storybook Symfony Bundle

The Storybook Symfony Bundle is the PHP backend for Storybook's Symfony/Twig framework. It renders [Symfony UX TwigComponent](https://symfony.com/bundles/ux-twig-component), plain Twig templates, controller fragments, and [Symfony UX Live Component](https://symfony.com/bundles/ux-live-component) in isolation for Storybook, and returns the styles, scripts, and import maps the component needs.

Learn more about Storybook at [storybook.js.org](https://storybook.js.org).

## What it does

When you run Storybook with `@storybook/symfony-vite`, the frontend asks this bundle to:

- Render a component with story args (`POST /_storybook/render/{id}`).
- Return the styles and scripts that belong to the component's asset entrypoint.
- Report health so the Storybook dev server knows the PHP backend is ready (`GET /_storybook/health`).
- List discoverable components and expose source code for the docs panel (`GET /_storybook/index`, `GET /_storybook/source/{id}`).

The bundle is intentionally small. It relies on Symfony UX TwigComponent by default and can also render plain Twig templates, controller fragments, and Symfony UX Live Components.

## Documentation

- [Installation](./installation.md)
- [Configuration](./configuration.md)
- [Component adapters](./adapters.md)
- [Asset pipelines](./asset-pipelines.md)
- [Endpoints](./endpoints.md)
- [Development](./development.md)

## Quick start

```bash
composer require --dev storybook/symfony-bundle
```

Enable the bundle in `config/bundles.php` if Symfony Flex did not register it:

```php
return [
    // ...
    Storybook\SymfonyBundle\StorybookBundle::class => ['storybook' => true],
];
```

Add the bundle's routes in the `storybook` environment:

```yaml
# config/routes/storybook.yaml
storybook:
  resource: Storybook\SymfonyBundle\Controller\StorybookController
  type: attribute
```

Configure the bundle:

```yaml
# config/packages/storybook/storybook.yaml
storybook:
  asset_pipeline: auto
  entrypoint: app
```

Then start Storybook with `@storybook/symfony-vite`.
