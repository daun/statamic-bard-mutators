# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

- `composer test` — run the Pest test suite
- `composer test -- --filter='wraps blockquotes'` — run a single test by name
- `composer lint` — check code style (Pint, dry run)
- `composer format` — auto-format with Pint
- `composer analyse` — run PHPStan/Larastan at level 5 against `src/`

CI (`.github/workflows/ci.yml`) currently only runs `composer lint`; tests and analysis must be run locally.

## Architecture

This package is a collection of plugins for the third-party [Bard Mutator addon](https://github.com/jacksleight/statamic-bard-mutator) (`jacksleight/statamic-bard-mutator`). It is **not** a standalone Statamic addon — it has no service provider of its own. Consumers register each mutator manually via `Mutator::plugin(new SomeMutator())` in their app.

Each class in `src/` extends `JackSleight\StatamicBardMutator\Plugins\Plugin` and declares a `protected array $types` listing the Bard node/mark types it targets (e.g. `['blockquote']`, `['link']`, `['heading']`). Plugins implement one of two hook methods, and the choice matters:

- **`process(object $item, object $info)`** — mutates the parsed ProseMirror/Tiptap node tree before rendering. Use this when you need to restructure content (split, wrap, extract children). Mutate `$item` in place, or use `Data::morph()` / `Data::clone()` / `Data::html()` from `JackSleight\StatamicBardMutator\Support\Data` to replace nodes with arbitrary HTML wrappers. `SemanticBlockquotes` and `WrapTables` use this hook.
- **`render(array $value, object $info, array $params): array`** — mutates the array form of a single rendered tag (`[tagName, attrs, ...]`). Use this for attribute-only changes. `MarkExternalLinks`, `MarkAssetLinks`, `GenerateHeadingIds` use this hook.

When `process()` rewrites a node into a different node type (e.g. blockquote → figure), guard against re-entry by checking `$info->parent->type` — see `SemanticBlockquotes::process()` for the pattern.

## Tests

Tests use Pest on top of Orchestra Testbench via Statamic's `AddonTestCase`. The shared `Tests\TestCase` boots `JackSleight\StatamicBardMutator\ServiceProvider` (the addon under integration), registers a fixture set of Bard nodes/marks in `$nodes` and `$marks`, and exposes helpers:

- `getTestValue(array $doc)` — wrap a ProseMirror-style document in a `Statamic\Fields\Value` backed by a Bard fieldtype
- `renderTestValue($value)` — augment the value through Bard's `Augmentor` and return the rendered HTML string

The typical test pattern: build a value, assert baseline HTML, call `Mutator::plugin(SomePlugin::class)` (or `new SomePlugin(...)` with options), then assert the mutated HTML. Plugins must be registered *after* the value is built, since `Mutator::registerAllExtensions()` is already called in `setUp()`.

## Supported versions

PHP `^8.1`, Statamic `^5.0 || ^6.0`, Bard Mutator `^3.0`. Don't introduce syntax or APIs that break the lower bounds.
