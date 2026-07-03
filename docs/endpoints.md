# Endpoints

All endpoints are mounted under the `/_storybook` prefix.

## `GET /_storybook/health`

Returns a simple status object used by the Storybook framework to wait for the PHP backend:

```json
{
  "status": "ok"
}
```

## `POST /_storybook/render/{id}`

Renders the component identified by the request body and returns the resulting HTML plus assets.

Request body fields:

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `componentId` | `string` | Yes for Twig and Live components; optional for template/controller | Component name, template path, or controller reference. |
| `adapter` | `string` | No | Force a specific adapter: `twig_component`, `template`, `controller`, or `live`. |
| `template` | `string` | Only when `adapter` is `template` | Twig template path. If it starts with `templates/`, the prefix is stripped. |
| `controller` | `string` | Only when `adapter` is `controller` | Controller reference such as `App\Controller\AlertController::fragment`. |
| `args` | `object` | No | Props passed to the component or template. |
| `globals` | `object` | No | Additional global variables passed to the render context. |

Response:

```json
{
  "html": "<button ...>Save</button>",
  "assets": {
    "pipeline": "pentatrion-vite",
    "styles": [{ "url": "/build/app.css" }],
    "scripts": [{ "url": "/build/app.js", "type": "module" }],
    "importmap": null
  },
  "metadata": {
    "component": "components-button--primary"
  }
}
```

## `GET /_storybook/index`

Lists discoverable components and templates. Used by the Storybook indexer when `experimental_symfonyAutoDiscovery` is enabled.

## `GET /_storybook/source/{id}`

Returns the Twig source for a component. The ID is the component name or path. Used by the docs panel to show the source code of a rendered component.
