---
name: open-pr
description: Opens a pull request from the current branch using the PR template. Use when the user asks to open a PR, create a pull request, or invokes /open-pr.
allowed-tools: Bash, Read, AskQuestion
---

# Open Pull Request

Opens a draft PR from the current branch following the bundle's conventions.

## Workflow

### 1. Gather context

Run in parallel: `git status`, `git diff`, `git log --oneline <base>...HEAD` (after step 2), `git branch -vv`.

Push first if needed: `git push -u origin HEAD`.

### 2. Detect base branch

```bash
git fetch origin
bash .agents/skills/open-pr/scripts/detect-base-branch.sh
```

The script detects the best base branch from tracked upstream, reflog, or the closest `origin/*` ancestor. The default branch is `main`.

### 3. Draft title and body

**Title:** Concise, descriptive sentence-case title — see the `pr` skill for guidance.

**Body:** Read `.github/pull_request_template.md`. Copy it **exactLY** (keep all placeholder lines). Fill in:

- `Closes #` when an issue is linked
- **Changelog** entries for user-facing changes
- **Testing / Reviewing** steps or a checklist
- Leave the author checklist unchecked for the maintainer

### 4. Create the PR

```bash
gh pr create \
  --draft \
  --base "<detected-base>" \
  --title "<Title>" \
  --body "$(cat <<'EOF'
<FILLED_TEMPLATE>
EOF
)" \
  --assignee @me
```

### 5. Report

Share the PR URL and confirm CI is expected to pass.

## Notes

- Always draft; always assign `@me`.
- The default branch is `main`.
