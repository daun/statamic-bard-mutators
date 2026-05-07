# Plugin design conventions

## Common mistakes

- ❌ Naming a plugin `Auto*` → ✅ verb+noun (`Insert*`, `Mark*`, `Generate*`)
- ❌ Fluent setters or static factories → ✅ constructor named-args
- ❌ Walking the doc tree on every render call → ✅ memoize by `spl_object_id($info->root)`
- ❌ Duplicating config across plugins → ✅ set canonical form on the node tree, read it elsewhere

## Naming

Plugin classes follow `Verb + Noun`: `Generate*`, `Mark*`, `Wrap*`, `Insert*`, `Normalize*`, `Shift*`, `LazyLoad*`, etc. Avoid `Auto*` — every plugin is automatic by virtue of being a plugin, so the prefix carries no information.

When two plugins do related work, use the same noun and different verbs to signal a family with non-overlapping responsibilities. `ShiftHeadingLevels` (positioning) + `NormalizeHeadingLevels` (canonicalizing) is one example.

## Configuration

Constructor named-args only. No fluent setters or static factories — except where deliberately chosen by the maintainer (currently just `LazyLoadImages::usingLazysizes()`).

## Hook choice

The architecture section in `CLAUDE.md` covers the basic process-vs-render decision. Two further patterns emerged from this codebase:

**Document-wide state from a per-node hook**: when a render hook needs information computed across the whole document (e.g. "is this the first image", "what's the shallowest heading level"), walk `$info->root` once and memoize by `spl_object_id($info->root)`. See `ShiftHeadingLevels::resolveOffset()` and `NormalizeHeadingLevels::computeLevels()`.

**Process runs before render fully** — across the entire document tree, not interleaved per node. Consequence: a render-hook plugin cannot see attrs set by another render-hook plugin on a different node. A process-hook plugin running later in registration order *can* see what earlier process-hook plugins set on the same node.

## Coupling smell

If two plugins must agree on the same config value (a slug prefix, an id format, etc.) to interoperate, that's a smell. Don't duplicate the option across both APIs. Instead, surface the canonical form on the node tree by having the producing plugin set it in `process`, and have the consuming plugin read from there.

Example: `GenerateHeadingIds` sets `$item->attrs->id` in `process`. `InsertHeadingAnchors` reads `$item->attrs->id` and skips headings without one. Neither plugin needs to know about the other's `prefix` config.
