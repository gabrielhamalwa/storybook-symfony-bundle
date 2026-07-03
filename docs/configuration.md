# Configuration

The bundle is configured under the `storybook` key in `config/packages/storybook/storybook.yaml`:

```yaml
storybook:
  environment: storybook
  project_dir: '%kernel.project_dir%'
  public_dir: '%kernel.project_dir%/public'
  asset_pipeline: auto
  entrypoint: app
```

| Option | Type | Default | Description |
| --- | --- | --- | --- |
| `environment` | `string` | `storybook` | Symfony environment used when the Storybook framework boots the PHP server. |
| `project_dir` | `string` | `%kernel.project_dir%` | Path to the Symfony project root. |
| `public_dir` | `string` | `%kernel.project_dir%/public` | Path to the public directory. |
| `asset_pipeline` | `auto`, `pentatrion_vite`, `encore`, `asset_mapper`, `none` | `auto` | Asset pipeline to use for extracting styles and scripts. |
| `entrypoint` | `string` | `app` | Entrypoint name used to look up the component's assets. |

## Asset pipeline auto-detection

When `asset_pipeline` is `auto`, the bundle detects the installed pipeline by checking for known Symfony services in this order:

1. Pentatrion Vite (`Pentatrion\ViteBundle\Service\EntrypointsLookupCollection`)
2. Webpack Encore (`webpack_encore.entrypoint_lookup_collection`)
3. AssetMapper (`asset_mapper.importmap.generator`)
4. None (`NullAssetPipeline`)

## Disabling the pipeline

If your components do not require CSS or JavaScript, set `asset_pipeline: none`:

```yaml
storybook:
  asset_pipeline: none
```

## Environment-specific settings

Because the bundle is only enabled in the `storybook` environment, you can keep all Storybook-specific configuration under `config/packages/storybook/` without affecting `dev`, `test`, or `prod`.
