# Mintlify Style

This file owns Storybook Symfony Bundle editorial, Mintlify MDX component, frontmatter, and formatting rules for `/docs`. It does **not** own doc-type identification, mode routing, or intervention logic — those belong in `docs-strategy.md`.

## Voice and Tone

### Point of View

- Second person ("you") for addressing the reader — this is the default.
- First person plural ("we") when speaking from the project's perspective (e.g. "We recommend…") or walking through something together (e.g. "Let's take a look…").
- Do not use first person singular ("I") or third person for the reader ("the user").

### Tone

- Professional but conversational — write as if explaining to a colleague, not a textbook.
- Encouraging without being excessive.
- Solution-focused — emphasise what readers *can* accomplish rather than limitations.
- Direct and confident — state recommendations clearly ("We recommend…") rather than hedging unnecessarily.

### Sentence Structure

- Prefer active voice over passive ("The bundle renders the component" not "The component is rendered by the bundle").
- Use short, direct sentences for emphasis and introductions.
- Longer sentences are acceptable when explaining complex relationships, but avoid run-ons.
- Lead with purpose — open sections by stating what something is or why it matters, not with background.

### Instructions

- Use imperative mood for step-by-step instructions: "Run this command", "Add the following", "Create a new file".
- Use suggestive phrasing for optional or alternative approaches: "You can also…", "You might want to…".
- Use declarative phrasing to introduce code examples with context: "To define the args of a single story, use the `args` key:".

### Contractions

- Use contractions naturally (don't, can't, won't, you'll, it's, we're) — they reinforce the conversational tone.
- Avoid contractions in callout warnings or other serious/cautionary contexts where precision matters.

### Technical Terms

- Define key terms on first use, then use them freely afterward.
- Link to related concepts rather than re-explaining them inline.
- Assume basic PHP, Symfony, and web development knowledge — don't over-explain fundamentals.
- Use backticks for all code-like terms (see [Inline Formatting](#inline-formatting)).

### Hedging

- Use "can" for capabilities and "may" or "might" for conditional outcomes.
- Use "should" for recommendations, "must" for requirements.
- Use "typically" or "generally" when describing common patterns that have exceptions.
- Don't hedge when the statement is straightforward — say "This adds…" not "This should add…".

### Word Choice

- Use British English spelling consistently: `behaviour`, `colour`, `favour`, `centre`, `organise`, `realise`, `travelling`.
- Avoid minimizing language ("simply", "just", "easily", "obviously") — what's simple for one reader may not be for another.
- Use "powerful", "useful", or "great" sparingly and only when warranted.
- Be specific rather than vague — "renders in under 2 seconds" over "renders quickly".

### Introducing Examples

- Set up *why* before showing *how* — provide a brief sentence of context before code blocks.
- Use patterns like: "Here's how you could…", "For example, if you…", "To do X, use Y:".
- End the lead-in sentence with a colon when the code block directly follows.

### Section Openings

- Open with a 1–2 sentence summary of what the section covers and why it matters.
- Get to the point quickly — minimise preamble.
- The opening sentence should work as a standalone definition or value statement.

### Paragraph Length

- Keep paragraphs to 2–4 sentences for scannability.
- Introductory paragraphs should be 1–2 sentences.
- Break up longer explanations with headings, lists, or callouts.

## Headings

- H1 via frontmatter `title` only; never use `# Heading` in the body.
- H2/H3 use sentence case (capitalise first word and proper nouns only).
- Don't skip heading levels.

## Links

- Internal: relative paths to `.mdx` files, e.g. `[text](../path/to/file.mdx)`.
- External: full URLs, always wrapped in markdown link syntax (no bare URLs in prose).
- Link to the local Storybook monorepo source in `/Users/ghamalwa/WebstormProjects/storybook/code/` only when the bundle docs directly depend on renderer/framework behaviour.

## Lists

- Unordered lists use `-` (not `*` or `+`).
- Ordered lists use `1.`, `2.`, etc.

## Inline Formatting

- Backticks for file paths, function names, variable names, component names, CLI commands, config keys, type names.
- Bold for UI labels and emphasis; italics sparingly.

## Custom Components

Mintlify callout components are first-class. Use the element form, not the generic `<Callout>` form, unless you need a custom icon or colour.

### Callouts

- `<Note>` — supporting information, additional context.
- `<Tip>` — helpful suggestions, best practices.
- `<Warning>` — caution, potential issues, important caveats.
- `<Info>` — important information that is not a warning.
- `<Danger>` — destructive or risky actions.
- `<Check>` — confirmed status or success conditions.

Do not use `<Callout variant="...">` unless the official Mintlify docs show that the generic `Callout` component accepts a `variant` prop. Prefer `<Note>`, `<Tip>`, `<Warning>`, `<Info>`, `<Danger>`, and `<Check>` instead.

### Cards

- Use `<CardGroup>` to group related cards, or `<Columns>` where Mintlify recommends multi-column layouts.
- `<CardGroup>` is preserved for backward compatibility; prefer `<Columns>` for new layouts if the Mintlify docs indicate it is the current recommendation.
- Each `<Card>` should have a `title`, optional `icon`, and optional `href`.

### Other Components

- `<Steps>` — numbered sequential instructions.
- `<Tabs>` / `<Tab>` — switchable content groups.
- `<CodeGroup>` — grouped code examples.

When in doubt, verify the component's props and behaviour against the official Mintlify documentation rather than assuming parity with another docs platform.

## Frontmatter

- Values are not wrapped in quotes, unless the value contains special characters (e.g. `&`, `|`, `:`, commas) that require quoting.
- When quoting is needed, use single quotes.
- Only use `sidebar.title` when it differs from `title`. If it matches, omit it.

**Good example:**

```yaml
---
title: Configuration
---
```

**Bad example:**

```yaml
---
title: "Configuration"
---
```

## Block JSX Elements

Block-level JSX elements (e.g. `<Note>`, `<Warning>`, `<CardGroup>`, `<details>`) follow these rules:

- New line before and after elements (unless the content before/after is a comment, in which case there should be no new line between the comment and the element).
- New line before and after content inside elements (`<summary>` is an exception — no new line between the opening `<details>` tag and the `<summary>` tag).
- Content is not indented, except when the content would normally be indented (e.g. nested list items, content inside code blocks).

**Good example:**

```mdx
<Note>

This is a note.

- This is a list item inside the note
  - This is a nested list item inside the note

```json
{
  "key": "value"
}
```

</Note>
```

## Validation

There is no automated docs checker in this repo. Validate manually:

- Confirm all internal links resolve to real `.mdx` files.
- Confirm all Mintlify components are used according to the official Mintlify docs.
- Confirm British English spelling throughout.
- Confirm code examples match the actual PHP source in `src/` and the JavaScript source in `/Users/ghamalwa/WebstormProjects/storybook/code/` when referenced.
