# Development

## Running the bundle tests

The bundle uses PHPUnit. Install dependencies and run the tests:

```bash
composer install
composer test
```

## CI

The bundle repository includes a GitHub Actions workflow that validates `composer.json` and runs the PHPUnit test suite on every push and pull request.

## Contributing

This bundle is part of the Storybook Symfony/Twig integration. Issues, feature requests, and pull requests are welcome in the [storybook-symfony-bundle](https://github.com/storybookjs/storybook-symfony-bundle) repository.

## Releasing

The bundle is versioned independently from the Storybook monorepo. Follow [Semantic Versioning](https://semver.org/) and tag releases after updating `CHANGELOG.md`.
