# Asset pipelines

The bundle extracts the styles and scripts that belong to the configured `entrypoint` for the rendered component. It supports the most common Symfony asset pipelines.

## Pentatrion Vite

When `pentatrion/vite-bundle` is installed, the bundle reads the configured entrypoint from Vite's `entrypoints.json` and returns the matching CSS and module scripts. In development, the bundle uses the Vite dev server URLs returned by `vite-plugin-symfony`; in production, it uses the built manifest paths.

Make sure the Vite dev server is running and the `entrypoints.json` is generated before rendering stories.

Example `config/packages/storybook/storybook.yaml`:

```yaml
storybook:
  asset_pipeline: auto
  entrypoint: app
```

## Webpack Encore

When `symfony/webpack-encore-bundle` is installed, the bundle reads from the Encore entrypoint lookup for the configured entrypoint. Configure your `webpack.config.cjs` to build an `app` entrypoint that includes your Stimulus bootstrap and component styles.

Example `config/packages/storybook/storybook.yaml`:

```yaml
storybook:
  asset_pipeline: auto
  entrypoint: app
```

## AssetMapper

When `symfony/asset-mapper` is installed, the bundle reads the import map and eager entrypoint imports. Make sure the `app` asset is registered in `importmap.php` and any CSS files are mapped as eager imports.

The Storybook framework's post-install cache pre-warm also runs `asset-map:compile` when AssetMapper is available, so the import map is generated before the first render.

Example `config/packages/storybook/storybook.yaml`:

```yaml
storybook:
  asset_pipeline: auto
  entrypoint: app
```

## No asset pipeline

If your components do not require CSS or JavaScript, set `asset_pipeline: none`:

```yaml
storybook:
  asset_pipeline: none
```

The renderer will not inject any styles or scripts.
