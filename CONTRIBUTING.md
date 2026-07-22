# Contributing

## Contribution model

Storybook Symfony Bundle is an open source project. It is maintained by the project maintainer and by community contributors.

## Code of conduct

[Code of conduct](./CODE_OF_CONDUCT.md)

## Prerequisites

Before contributing, install the following tools:

- [PHP](https://www.php.net/downloads.php) 8.2 or higher, as required by `composer.json`
- [Composer](https://getcomposer.org/doc/00-intro.md)
- [Git](https://github.com/git-guides/install-git)
- [Symfony CLI](https://symfony.com/download) (optional, for local Symfony projects)

On Windows, follow the [Composer Windows installation guide](https://getcomposer.org/doc/00-intro.md#installation-windows).

A code editor is also required. Common choices include [VS Code](https://code.visualstudio.com/) and [PhpStorm](https://www.jetbrains.com/phpstorm/).

Once those tools are installed, contributions can begin.

## Start contributing

### Setting up the environment

#### Fork the repository

[Forking a repository](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/working-with-forks/fork-a-repo#forking-a-repository)

#### Clone the fork

[Cloning a repository](https://docs.github.com/en/repositories/creating-and-managing-repositories/cloning-a-repository#cloning-a-repository)

#### Add the upstream remote

After cloning the fork, add the upstream remote:

```sh
git remote add upstream git@github.com:gabrielhamalwa/storybook-symfony-bundle.git
git remote -v
```

Expected output:

```sh
origin  git@github.com:<github_username>/storybook-symfony-bundle.git (fetch)
origin  git@github.com:<github_username>/storybook-symfony-bundle.git (push)
upstream  git@github.com:gabrielhamalwa/storybook-symfony-bundle.git (fetch)
upstream  git@github.com:gabrielhamalwa/storybook-symfony-bundle.git (push)
```

### Making a contribution

#### Find or create an issue

Review the [issues list](https://github.com/gabrielhamalwa/storybook-symfony-bundle/issues) for work to undertake. If a suitable issue exists, add a comment to indicate intent.

If no suitable issue exists, [create one](https://github.com/gabrielhamalwa/storybook-symfony-bundle/issues/new/choose). Issues are used to track work and streamline the contribution process.

Possible starting points:

- [Good first issues](https://github.com/gabrielhamalwa/storybook-symfony-bundle/issues?q=label%3A%22good+first+issue%22)
- [Open bugs](https://github.com/gabrielhamalwa/storybook-symfony-bundle/issues?q=label%3A%22bug%22)
- [Enhancements open for contribution](https://github.com/gabrielhamalwa/storybook-symfony-bundle/issues?q=is%3Aissue%20state%3Aopen%20label%3A%22enhancement%22)

Bug reports should use the bug report template. Feature ideas and enhancements should start as a [GitHub Discussion](https://github.com/gabrielhamalwa/storybook-symfony-bundle/discussions) before a pull request is opened.

#### AI-assisted contributions

Generative AI tools, coding assistants, LLMs, and agentic workflows may be used to explore, draft, or refactor code and documentation. The contributor is responsible for any AI-assisted changes submitted in a pull request.

Before submitting:

- Review every generated file, line, and suggestion.
- Run the test suite and any relevant manual checks to confirm the change works.
- Ensure the output follows this repository's existing conventions unless otherwise necessary.
- Only submit changes that have been human-reviewed and understood.

#### Create a working branch

Before making changes, create a branch from `main` to keep work organised and separate from the main codebase.

```sh
git pull origin main
git checkout -b <branch_name>
```

#### Build and run the test suite

From the root directory, run:

```sh
composer install
composer validate --strict
composer test
```

The bundle does not include a development server. It is used by the `@storybook/symfony-vite` renderer in a Storybook project. To test changes in a real project, install the local copy with a Composer [path repository](https://getcomposer.org/doc/05-repositories.md#path) or run the Pest suite.

Refer to [`docs/development/setup.mdx`](./docs/development/setup.mdx) and [`README.md`](./README.md) for guidance on project structure and conventions.

#### Test

Run the test suite:

```sh
composer test
```

For detailed testing information, see [`docs/development/setup.mdx`](./docs/development/setup.mdx), [`phpunit.xml.dist`](./phpunit.xml.dist), and [`tests/Pest.php`](./tests/Pest.php).

#### Create a pull request

When changes are ready for review, commit and push the branch. Use descriptive commit messages.

Then create a pull request to the main repository following GitHub's [guide to creating a pull request from a fork](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/working-with-forks/creating-a-pull-request-from-a-fork).

See [How to write the perfect pull request](https://github.com/blog/1943-how-to-write-the-perfect-pull-request) for guidance on writing a pull request description.

Before a pull request is merged, maintainers will review it. Ensure CI is passing. If issues arise, consult the CI logs or raise a question in an issue.

#### Licence

All contributions are accepted under the [Apache-2.0](./LICENSE) licence. By submitting a pull request, the contributor agrees that the contribution is licensed under Apache-2.0.

#### Update a pull request

Monitor activity on the pull request. Reviews are requested automatically. Maintainers will review the work, add comments, ask questions, and suggest changes. The process may take several iterations.

To make a change, add further commits to the branch and push them to the fork. The pull request will update automatically.

Reviewers will check that CI is passing. If issues arise, consult the CI logs or raise a question in an issue.

Once the changes are complete and approved, a maintainer will merge them.

## FAQ

### Who can contribute?

A public GitHub account is required.

- Development: bug fixes, adapters, asset pipelines, and other code changes. Refer to [`docs/development/setup.mdx`](./docs/development/setup.mdx) for setup and conventions.
- Documentation: improvements to `README.md` and files under `docs/`. Use British English spelling and Lucide icon names throughout.
- Testing: additions or improvements to test coverage.

### Other ways to contribute

- Community: open a [GitHub Discussion](https://github.com/gabrielhamalwa/storybook-symfony-bundle/discussions) or a [GitHub issue](https://github.com/gabrielhamalwa/storybook-symfony-bundle/issues) to engage with maintainers and ask questions.
- Report bugs: opening a well-documented issue is valuable even if a fix is not provided.

### What is the difference between a feature and an enhancement?

A feature introduces new functionality. An enhancement improves existing functionality. Both are discussed in GitHub Discussions before a pull request is opened.

### Do I need to cover every adapter?

The bundle supports multiple adapters: Twig components, plain Twig templates, controller fragments, and Symfony UX Live Components. If a bug fix or feature applies to more than one adapter, consider updating the others or opening an issue to keep them in sync.

### How can I test components or share a bug reproduction?

Provide a minimal Symfony project, a failing test, or a code snippet in the issue or pull request. This helps reproduce and verify behaviour.

### Can I be assigned to an issue?

Issues are only assigned to team members and core maintainers. To claim an issue, add a comment stating intent to work on it. If no pull request is created within two weeks, the issue is considered available for others.

### When can I work on an issue that someone else has said they are working on?

If an issue does not have a pull request within two weeks, it is available for others to work on.

### Why are issues only assigned to team members and core maintainers?

An assignee indicates that work is in progress or planned for the current sprint. Restricting assignment to the team ensures that this expectation can be maintained.
