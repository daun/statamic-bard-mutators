# 🧱  Statamic Bard Mutators

A collection of mutators for transforming [Statamic Bard](https://statamic.dev/fieldtypes/bard) content.

All mutators are implemented as plugins for the [Bard Mutator Addon](https://statamic.com/addons/jacksleight/bard-mutator).

## Installation

Install the package via composer:

```bash
composer require daun/statamic-bard-mutators
```

## Registration

Register any mutators you want to use in your app's service provider. Options can be passed
as arguments to the mutator's constructor.

```php
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        new \Daun\BardMutators\MarkExternalLinks();
        new \Daun\BardMutators\GenerateHeadingIds(levels: [2, 3]);
    }
}
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
new \Daun\BardMutators\WrapTables();

// Optionally use a custom tag or add a class to the wrapper element
new \Daun\BardMutators\WrapTables(
    tag: 'section',
    class: 'table'
);
```

## License

[MIT](https://opensource.org/licenses/MIT)
