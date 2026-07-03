# Installation

## Requirements

- PHP 8.2 or higher
- Symfony 6.4 or 7.0
- Twig 3.8 or higher
- Symfony UX TwigComponent 2.0 or 3.0

Optional but commonly used:

- `symfony/ux-live-component` for live component stories
- `pentatrion/vite-bundle`, `symfony/webpack-encore-bundle`, or `symfony/asset-mapper` for asset pipelines

## Install the bundle

```bash
composer require --dev storybook/symfony-bundle
```

If Symfony Flex is enabled, the bundle is registered automatically. Otherwise, add it manually to `config/bundles.php`:

```php
return [
    // ...
    Storybook\SymfonyBundle\StorybookBundle::class => ['storybook' => true],
];
```

The bundle is only enabled in the `storybook` environment so it never affects production.

## Install the Storybook framework

In your Symfony project's root, install the Storybook framework and the dev server:

```bash
yarn add -D @storybook/symfony-vite storybook
```

Or use the Storybook CLI:

```bash
npx storybook@latest init
```

## Add routes

The bundle exposes a `StorybookController` with attribute-based routes. Add it to a routing file in the `storybook` environment:

```yaml
# config/routes/storybook.yaml
storybook:
  resource: Storybook\SymfonyBundle\Controller\StorybookController
  type: attribute
```

## Configure the environment

Storybook boots Symfony in a dedicated `storybook` environment. Keep it minimal so the container compiles quickly.

```yaml
# config/packages/storybook/framework.yaml
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

## Start Storybook

```bash
yarn storybook
```

The framework starts the PHP server, warms the Symfony cache, and opens Storybook.
