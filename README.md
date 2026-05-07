# 🧱  Statamic Bard Mutators

A collection of mutators for transforming [Statamic Bard](https://statamic.dev/fieldtypes/bard) content.

Mutators are implemented as plugins for Jack Sleight's [Bard Mutator Addon](https://statamic.com/addons/jacksleight/bard-mutator).

## Mutators

- [Lazy Load Images](#lazy-load-images) — add `loading=lazy` and `decoding=async` to images
- [Generate Heading IDs](#generate-heading-ids) — add `id` to headings
- [Insert Heading Anchors](#insert-heading-anchors) — insert anchor links into headings
- [Normalize Heading Levels](#normalize-heading-levels) — close gaps in the heading hierarchy
- [Shift Heading Levels](#shift-heading-levels) — shift or clamp heading levels
- [Mark External Links](#mark-external-links) — add `target` and `rel` to external links
- [Mark Asset Links](#mark-asset-links) — add `download` to asset links
- [Semantic Blockquotes](#semantic-blockquotes) — wrap blockquotes in `figure` with `figcaption`
- [Wrap Tables](#wrap-tables) — move tables into a horizontally scrollable container
- [Remove List Item Paragraphs](#remove-list-item-paragraphs) — remove `p` wrappers around `li` text

[See the full list of mutators →](#all-mutators)

## Installation

Install the package via composer:

```bash
composer require daun/statamic-bard-mutators
```

## Registration

Register any mutators you want to use from the `Mutator` facade. Options can be passed as arguments
to the constructor. You can read more about
[class-based mutator plugins](https://jacksleight.dev/docs/bard-mutator/plugins#class-based-plugins)
in the addon readme.

```php
use JackSleight\StatamicBardMutator\Facades\Mutator;
use Daun\BardMutators\MarkExternalLinks;

Mutator::plugin(new MarkExternalLinks());
```

## All Mutators

### Mark External Links

Mark external links with `target="_blank"` and `rel="external"`.

```html
<!-- Before -->
<a href="https://example.com">External link</a>

<!-- After -->
<a href="https://example.com" target="_blank" rel="external">External link</a>
```

```php
new MarkExternalLinks();

// Optionally customize the `target` and `rel` attributes
new MarkExternalLinks(
    target: '_blank',
    rel: 'noopener noreferrer'
);
```

### Mark Asset Links

Mark links to assets with `download="filename.ext"`.

```html
<!-- Before -->
<a href="/assets/video.mp4">Download video</a>

<!-- After -->
<a href="/assets/video.mp4" download="video.mp4">Download video</a>
```

```php
new MarkAssetLinks();

// Use original filename as download filename hint
// Requires `daun/statamic-original-filename` package
new MarkAssetLinks(
    useOriginalFilename: true
);
```

### Generate Heading IDs

Adds an `id` attribute to headings based on their content.

```html
<!-- Before -->
<h2>Heading</h2>

<!-- After -->
<h2 id="heading">Heading</h2>
```

```php
new GenerateHeadingIds();

// Customize heading levels to generate IDs for and add a prefix to generated IDs
new GenerateHeadingIds(
    levels: [2, 3],
    prefix: 'section-'
);
```

### Insert Heading Anchors

Insert an anchor link inside each heading pointing to its own `id`. Anchors are
only added to headings that already have an `id` — register `GenerateHeadingIds`
beforehand if you want every heading to be anchored.

```html
<!-- Before -->
<h2 id="introduction">Introduction</h2>

<!-- After -->
<h2 id="introduction">
    <a href="#introduction" aria-label="Permalink to Introduction">
        <span aria-hidden="true">#</span>
    </a>
    Introduction
</h2>
```

The icon is wrapped in `<span aria-hidden="true">` so a screen reader announces
only the link's `aria-label`, not the icon.

```php
// Register GenerateHeadingIds first so headings get an id to anchor to.
Mutator::plugin(new GenerateHeadingIds());
Mutator::plugin(new InsertHeadingAnchors());

// Append the anchor instead of prepending it.
new InsertHeadingAnchors(behavior: 'append');

// Customize the icon (text, emoji, or raw HTML for an inline SVG).
new InsertHeadingAnchors(icon: '🔗');
new InsertHeadingAnchors(icon: '<svg viewBox="0 0 16 16"><path d="…"/></svg>');

// Customize the accessible label. Use `{text}` as a placeholder for the
// resolved heading text.
new InsertHeadingAnchors(label: 'Jump to {text}');

// Limit which heading levels are anchored, add a class.
new InsertHeadingAnchors(
    levels: [2, 3],
    class: 'heading-anchor',
);
```

### Semantic Blockquotes

Wraps blockquotes in a `figure` element and moves the author/source into a `figcaption` element.

```html
<!-- Before -->
<blockquote>
    <p>Quote</p>
    <p>— Author</p>
</blockquote>

<!-- After -->
<figure>
    <blockquote>
        <p>Quote</p>
    </blockquote>
    <figcaption>
        Author
    </figcaption>
</figure>
```

```php
new SemanticBlockquotes();

// Optionally add a class to the figure element
new SemanticBlockquotes(
    class: 'quote'
);
```

## Wrap Tables

Wraps tables in a `div` element to allow for horizontal scrolling on smaller screens.

```html
<!-- Before -->
<table>...</table>

<!-- After -->
<div class="table-wrapper">
    <table>...</table>
</div>
```

```php
new WrapTables();

// Optionally use a custom tag or add a class to the wrapper element
new WrapTables(
    tag: 'section',
    class: 'table'
);
```

### Normalize Heading Levels

Close skip-level gaps in the heading hierarchy by pulling deep headings up
(e.g. `<h2>` followed by `<h4>` becomes `<h2>` followed by `<h3>`). The first
heading is left at whatever level it starts, and going back up to a shallower
level is always allowed.

```html
<!-- Before -->
<h2>Section</h2>
<h4>Subsection</h4>

<!-- After -->
<h2>Section</h2>
<h3>Subsection</h3>
```

```php
new NormalizeHeadingLevels();
```

This pairs naturally with `ShiftHeadingLevels` — register `NormalizeHeadingLevels`
first to clean the hierarchy, then `ShiftHeadingLevels` to position the cleaned
tree (e.g. `min: 2` to keep `<h1>` reserved for the page title).

### Shift Heading Levels

Shift heading levels up or down. Useful when a Bard field is rendered under a page
`<h1>` and headings inside the field should start lower in the outline.

```html
<!-- Before -->
<h1>Section</h1>
<h2>Subsection</h2>

<!-- After: shift: 1 -->
<h2>Section</h2>
<h3>Subsection</h3>
```

```php
// Shift every heading down (or up, with negative values). Clamped to h6.
new ShiftHeadingLevels(shift: 1);

// Clamp every heading to be at least h2 (e.g. to keep h1 reserved for the page title).
new ShiftHeadingLevels(min: 2);

// Shift the entire document so its shallowest heading becomes h2,
// preserving relative hierarchy. Mutually exclusive with `shift`.
new ShiftHeadingLevels(start: 2);

// Combine: shift down, then clamp at h2.
new ShiftHeadingLevels(shift: 1, min: 2);
```

### Lazy Load Images

Add `loading="lazy"` and `decoding="async"` to images for better page performance.

```html
<!-- Before -->
<img src="photo.jpg" alt="A photo">

<!-- After -->
<img src="photo.jpg" alt="A photo" loading="lazy" decoding="async">
```

```php
new LazyLoadImages();

// Skip lazy loading on the first image if it sits at the top of the document
// (i.e. is the first node, or appears in the first paragraph or figure).
// Useful for avoiding lazy loading on a likely LCP image.
new LazyLoadImages(
    skipFirst: true
);

// Switch to lazysizes.js markup. The image gets a `lazyload` class, its `src`
// is moved to `data-src`, and the native `loading`/`decoding` attributes are
// not added. Pass an optional class name to override `lazyload`.
(new LazyLoadImages())->usingLazysizes();
(new LazyLoadImages())->usingLazysizes('lazy-load');

// Combine with skipFirst: the LCP image is left untouched (no lazyload class,
// no data-src swap) so the browser loads it eagerly.
(new LazyLoadImages(skipFirst: true))->usingLazysizes();
```

```html
<!-- After: usingLazysizes() -->
<img class="lazyload" data-src="photo.jpg" alt="A photo">
```

## Remove List Item Paragraphs

Remove the paragraphs that tiptap automatically adds inside list items.

```html
<!-- Before -->
<li>
    <p>List item</p>
</li>

<!-- After -->
<li>
    List item
</li>
```

```php
new RemoveListItemParagraphs();
```

## License

[MIT](https://opensource.org/licenses/MIT)
