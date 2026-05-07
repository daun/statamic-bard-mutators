---
paths:
  - "tests/**/*.php"
---

# Testing patterns

## Common mistakes

- ❌ Writing `toEqual` HTML by hand → ✅ render a baseline first, then copy
- ❌ Asserting empty-string marker attrs → ✅ test user-visible attrs with `toContain`
- ❌ Registering consumer before producer → ✅ producer plugin (e.g. `GenerateHeadingIds`) first

The basic test pattern is documented in `CLAUDE.md` (build value → assert baseline → register plugin → assert mutated). A few finer points:

## Bake a baseline before asserting full HTML

When using `toEqual` against full rendered HTML, render the unmutated value first to capture the actual output, then copy from it. Saves an iteration on attribute-order or whitespace mismatches. The Tiptap renderer's attribute order isn't always intuitive (existing attrs first, render-hook additions appended).

For attribute-presence checks where order doesn't matter, prefer `toContain`.

## Empty-string attrs aren't visible in rendered HTML

If a plugin sets a marker attr like `data-bmu-anchor=""` for tree-level re-entry detection, it won't appear in the rendered output. Don't assert it. Test the actual user-visible behavior instead.

## Plugin registration order matters

Process-hook plugins run in the order they were registered with `Mutator::plugin()`. When a plugin depends on attrs set by another (e.g. `InsertHeadingAnchors` reads `attrs->id` set by `GenerateHeadingIds`), register the producer first in your test setup — same as in app code.

## Helper convention

Test files define small inline helpers like `imageNode()`, `headingNode()`, `paragraphNode()` to keep fixtures readable. New tests should follow that pattern; helpers are scoped per file via plain functions.
