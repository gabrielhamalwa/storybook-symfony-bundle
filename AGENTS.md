# Storybook Symfony Bundle Agent Instructions

Keep this file, `AGENTS.md`, up to date when the bundle's architecture, tooling, workflows, or contributor guidance changes.

This file is the canonical instruction source for coding agents. Files like `CLAUDE.md` should point here instead of duplicating instructions.

## Repository Overview

This is a PHP/Symfony bundle. The git root is the repo root, the source lives in `src/`, tests live in `tests/`, and the Mintlify documentation lives in `docs/`. The default branch is `main`.

- **Base branch**: `main` (all PRs should target `main`)
- **PHP**: `8.2` or higher (see `composer.json`)
- **Symfony**: `6.4`, `7.x`, or `8.x`
- **Package Manager**: Composer
- **Test Runner**: Pest (runs the existing PHPUnit-style test classes via `pest-plugin-drift`)
- **CI**: GitHub Actions (Linux)
- **Documentation**: Mintlify (`docs/`, `docs.json`)

## Repository Structure

```text
storybook-symfony-bundle/
├── .github/                  # GitHub configs and workflows
├── docs/                     # Mintlify documentation (MDX)
├── src/                      # PHP source code
│   ├── Asset/                # Asset pipeline extractors
│   ├── Component/            # Component adapters and resolver
│   ├── Controller/           # StorybookController and endpoints
│   ├── DependencyInjection/  # Symfony DI extension and compiler passes
│   ├── Dto/                  # Request/response and asset DTOs
│   ├── EventListener/        # CORS and static form-data listeners
│   ├── Indexer/              # Component indexing and Twig prop parsing
│   ├── Resources/config/     # Service definitions
│   └── StorybookBundle.php   # Bundle class
├── tests/                    # Pest/PHPUnit test classes
├── composer.json             # Composer manifest
├── phpstan.neon.dist         # PHPStan configuration
└── phpunit.xml.dist          # PHPUnit configuration
```

## Architecture

### Component adapters

The bundle renders four kinds of components through `Component\ComponentResolver`:

| Adapter          | `adapter` value  | Identifier / trigger                                                  | Notes                                    |
| ---------------- | ---------------- | --------------------------------------------------------------------- | ---------------------------------------- |
| Twig component   | `twig_component` | `componentId` such as `Button`                                        | Default. Uses Symfony UX TwigComponent.  |
| Plain template   | `template`       | `componentId` ending in `.twig` or a `template` field                 | Renders the template with story args.    |
| Controller fragment | `controller`  | `componentId` containing `::` or a `controller` field                 | Renders a Symfony controller fragment.   |
| Live component   | `live`           | `adapter: live` or renderer sends `live: true`                        | Requires `symfony/ux-live-component`.    |

See `src/Component/ComponentResolver.php` for the resolution order.

### Asset pipelines

`AssetExtractorInterface` extracts the styles and scripts for the configured `entrypoint`. The supported pipelines are:

- Pentatrion Vite (`PentatrionViteAssetPipeline`)
- Webpack Encore (`EncoreAssetPipeline`)
- AssetMapper (`AssetMapperPipeline`)
- None (`NullAssetPipeline`)

When `asset_pipeline: auto` is configured, `StorybookExtension` detects the installed pipeline by checking for known Symfony services in that order.

### Endpoints

`StorybookController` exposes endpoints under `/_storybook`:

- `GET /_storybook/health` — backend readiness
- `POST /_storybook/render/{id}` — render a component and return HTML + assets
- `GET /_storybook/index` — list discoverable Twig components
- `GET /_storybook/source/{id}` — return template and class source

### Indexer

`ComponentIndexer` scans `src/Twig/Components` (configurable) for classes with `#[AsTwigComponent]` or `#[AsLiveComponent]`, extracts prop metadata from PHP constructor parameters and public properties, and merges it with `{% props %}` declarations parsed by `TwigPropsParser`. The renderer consumes this to generate CSF stories when auto-discovery is enabled.

### Related packages

The JavaScript renderer and framework live in the adjacent Storybook monorepo at `/Users/ghamalwa/WebstormProjects/storybook`:

- `code/renderers/symfony/` — `@storybook/symfony` browser renderer
- `code/frameworks/symfony-vite/` — `@storybook/symfony-vite` framework

## Common Commands

Run commands from the repository root unless stated otherwise.

### Install and test

```bash
composer install
composer validate --strict
composer test
```

### Run a single test file or test case

```bash
vendor/bin/pest tests/Asset/PentatrionViteAssetPipelineTest.php
vendor/bin/pest --filter testName
```

### Common task scenarios

| Scenario                                   | Command                                      |
| ------------------------------------------ | -------------------------------------------- |
| Install dependencies                       | `composer install`                           |
| Validate `composer.json`                   | `composer validate --strict`                 |
| Run the full test suite                    | `composer test`                              |
| Run tests for one class                    | `vendor/bin/pest <path-to-test>.php`         |
| Update lock file to latest compatible deps | `composer update`                            |
| Run static analysis                        | `composer phpstan`                           |

## How To Work In This Repo

### For normal code changes

1. Install if needed: `composer install`
2. Make changes under `src/`
3. Add or update tests under `tests/`
4. Run `composer validate --strict` and `composer test`
5. Update `docs/` and `README.md` if behaviour or public API changes

### For documentation changes

1. Edit files under `docs/` (Mintlify MDX)
2. Use British English spelling throughout
3. Verify Mintlify component usage against the official Mintlify docs
4. Cross-check technical claims against the PHP source in `src/` and the JavaScript source in `/Users/ghamalwa/WebstormProjects/storybook/code/`

## Testing Expectations

- Use Pest to run tests. The test classes remain PHPUnit-style; there is no JavaScript test suite in this repo.
- Tests cover asset pipelines, adapters, the controller, event listeners, dependency injection, and indexing.
- Prefer real objects over mocks unless testing integration boundaries (file system, external services).
- Tests that touch `node:fs` or `node:fs/promises` do not exist in this repo; do not introduce Node-style test patterns.

### Writing tests

- Extend the appropriate Symfony `KernelTestCase` or `TestCase` base classes as shown in existing tests.
- Use `tests/Fixtures/` for shared fixture classes and templates.
- Keep tests deterministic; avoid `/tmp` paths or global state unless the test explicitly cleans up.

## Quality and Logging

After changing files:

1. Run `composer validate --strict` to check the Composer manifest.
2. Run `composer test` to run the Pest suite.
3. Run `composer phpstan` to run static analysis.
4. Run `vendor/bin/pest <affected-test-file>` for targeted verification.

There is no automated PHP formatter configured in this repo. Follow the existing code style:

- `declare(strict_types=1);` at the top of every PHP file.
- `final` classes and `readonly` where appropriate.
- Named constructor arguments when constructing DTOs.
- Backslash-prefixed global functions (`\is_array`, `\InvalidArgumentException`) matching existing conventions.
- Do not add `console.log`, `console.warn`, or `console.error` to PHP code.

## Documentation

The project uses Mintlify. Pages are `.mdx` files in `docs/` and navigation is configured in `docs.json`.

- Use British English spelling (e.g. `behaviour`, `colour`, `favour`, `centre`).
- Use Mintlify callout components: `<Note>`, `<Tip>`, `<Warning>`, `<Info>`, `<Danger>`.
- Group navigation cards with `<Columns>`; `<CardGroup>` is deprecated.
- Use Lucide icon names for `icon` frontmatter, `<Card icon="...">`, and `docs.json` group/anchor icons. Keep `icons.library` set to `lucide` in `docs.json`. Verify the name resolves on the Mintlify Lucide CDN (`https://d3gk2c5xim1je2.cloudfront.net/lucide/v<version>/<name>.svg`) before using it. If a Lucide name is unavailable (for example `github`), use a Lucide static URL such as `https://cdn.jsdelivr.net/npm/lucide-static@latest/icons/<name>.svg`. Avoid Font Awesome, Tabler, or other non-Lucide sources unless they are unavoidable.
- Do not invent Mintlify component APIs; verify against the official Mintlify documentation.
- Reference PHP source code in `src/` and the Storybook monorepo in `/Users/ghamalwa/WebstormProjects/storybook/code/` rather than writing from memory.

## Troubleshooting

- If tests fail with missing dependencies, run `composer install` first.
- If `composer validate --strict` fails, fix `composer.json` before running tests.
- The `storybook` environment is used for runtime; tests exercise the bundle through a dedicated test kernel.

## Environment Variables

| Variable              | Purpose                              |
| --------------------- | ------------------------------------ |
| `SYMFONY_ENV`         | Symfony environment used at runtime  |
| `APP_ENV`             | Symfony environment                  |

## Commands To Avoid

- **DO NOT RUN** `yarn task dev` or `yarn start` — these are Storybook monorepo commands and do not apply here.

## Git Commit Messages

- Do NOT add AI marketing or attribution lines to commit messages. Specifically, never include `Generated with [Devin](https://devin.ai)` or `Co-Authored-By: Devin <...>` / `Co-authored-by: Devin <...>` trailers.
- Commit messages should only describe the change and its motivation.

## Code Authoring Principles

These are recurring failure modes in agent-authored changes to this repo. Apply them when writing or reviewing code, not just when asked.

- **Comments are maintenance docs, not an investigation transcript.** Explain *why* for the next maintainer. Do not commit internal ticket / acceptance-criteria codes, the narrative of how you figured something out, or cross-file line references that will rot. One or two sentences of rationale beats a paragraph of evidence.
- **Verify environment assumptions empirically before encoding them.** If a design rests on "the bundler strips X" or "this metadata is empty here", prove it with a throwaway probe before building on it.
- **Encode assumptions with static checks first.** If an assumption is expected to always hold, prefer making it impossible via PHP types. When static checks are not practical, add a cheap runtime assertion close to the boundary so violations fail loudly at the source.
- **Avoid redundant tests already covered elsewhere.** Do not add tests for code patterns already guaranteed by PHP type declarations or existing tests.
- **Test contract boundaries, not implementation trivia.** A test should fail when the contract changes and pass when the implementation changes within the contract. Do not assert exact output strings unless the format itself is the contract.
- **Prefer explicit over clever.** Avoid deeply nested ternary expressions and magic strings. Use named constants and small methods.
