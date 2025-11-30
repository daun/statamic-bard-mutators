# 🧱  Statamic Bard Mutators

A collection of mutators for transforming [Statamic Bard](https://statamic.dev/fieldtypes/bard) content.

All mutators are implemented as plugins for the [Bard Mutator Addon](https://statamic.com/addons/jacksleight/bard-mutator).

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

## Mutators

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

// Optionally customize which heading levels to generate IDs for
new GenerateHeadingIds(levels: [2, 3]);
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
