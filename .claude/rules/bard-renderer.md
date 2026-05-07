# Bard / Tiptap renderer behavior

## Common mistakes

- ❌ Setting `attrs->id` only in process → ✅ also copy to `$value[1]['id']` in render
- ❌ Asserting an empty-string marker attr in rendered HTML → ✅ use it for tree inspection only
- ❌ Guessing attribute order in `toEqual` assertions → ✅ render a baseline first, copy from it
- ❌ Assuming `Data::html('<svg>...')` is a tag → ✅ non-tag-name first arg means raw HTML content
- ❌ Injecting children through render tuples → ✅ use `process` with `Data::html()`

The Tiptap PHP renderer (used by Statamic's Bard fieldtype) has a few non-obvious behaviors worth caching.

## Heading nodes declare only `level`

Tiptap's `Heading` node declares only the `level` attribute. Setting `$item->attrs->id` in a `process` hook is invisible to the renderer — Tiptap only emits declared attrs. To surface a non-declared attr on a heading you also need a render hook that copies it into the output tuple.

`GenerateHeadingIds` is the canonical example: it sets `attrs->id` in process (so other plugins can read it) and also copies it to `$value[1]['id']` in render (so it appears in HTML).

## Empty-string attribute values are dropped at render

The renderer filters out attribute values that are empty strings (and null). This means tree-level marker attrs like `data-bmu-anchor=""` survive in the parsed node tree (good for re-entry guards) but disappear from rendered HTML.

Consequence: never assert the presence of an empty-string marker in render-output tests. Use it for tree inspection only.

## Attribute order in render output

Existing attrs from the node come first; render-hook additions are appended. So the output is always `<img src="x" loading="lazy">`, never `<img loading="lazy" src="x">`. Bake a baseline `renderTestValue()` and copy from it before writing `toEqual` assertions.

## `Data::html()` is mode-switching

The first arg of `JackSleight\StatamicBardMutator\Support\Data::html()` is interpreted differently based on its shape:

- Matches `/^[a-z][a-z0-9-]*$/i` → treated as a tag name; produces `[tag, attrs, ...content]`
- Otherwise → treated as raw HTML content

Useful for injecting SVG or other arbitrary markup into a process-hook node tree:

```php
Data::html('a', $attrs, [
    Data::html('<svg viewBox="0 0 16 16"><path d="..."/></svg>'),
]);
```

## Render tuples don't compose cleanly with child injection

A render hook receives `[tagName, attrs, ...]` where the `0` placeholder represents children. The renderer expects nested *tag tuples*, not pre-rendered HTML strings, so injecting a child element through a render hook is awkward.

Rule of thumb: if you need to add or wrap children, use a process hook with `Data::html()` instead.
