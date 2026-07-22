---
name: pr
description: Creates a pull request following the bundle's conventions. Use when creating PRs, opening pull requests, or submitting changes for review.
allowed-tools: Bash, Read
---

# Create Pull Request

Creates a PR following the Storybook Symfony Bundle conventions.

## Base branch

The default branch is `main`. PRs should target `main` unless the user explicitly asks for a different base.

## Title format

Use a concise, descriptive title in sentence case:

- `Fix prop extraction for constructor-promoted properties`
- `Add CORS support for existing server integrations`
- `Document asset pipeline response shape`

## PR body

Read `.github/pull_request_template.md` from the repository root.

Copy that template **EXACTLY**, including all placeholder lines. Fill in the relevant sections based on the changes, but keep all section headings intact.

### Testing / Reviewing

The **Testing / Reviewing** section is mandatory — never leave it empty. Write steps for a separate maintainer, not a log of how you tested.

Each step should be:

- Clear and easy to follow
- Copy-pasteable shell commands where applicable
- Explicit about what behaviour to inspect (expected outcome, not just "check it works")
- Lists areas most likely to regress and worth extra scrutiny

**Verify your own steps first** — run through them locally before opening the PR.

## Command

Create PRs in draft mode:

```bash
gh pr create --draft --title "<Title>" --body "<FILLED_TEMPLATE>"
```

If the repository uses labels that match the change (e.g. `bug`, `documentation`, `enhancement`), add them with `--label`.
