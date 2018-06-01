# Drupal 7 PageType

The pagetype module provides a reusable page entity type for one-off pages that need to be fieldable.


## About the pagetype module

The `pagetype` module provides a "node like" page entity type. This entity type is fieldable, and bundles can be managed via the `Structure->Page Types` admin menu.

The page entity works much the same as the node entity, in that pages are given a default visible path (pages/{page-id} by default) and can be fully customized with their own set of fields. The core different between Nodes and Pages is that Pages, by default, have *no continuity*.

This means that pages are more suited for one-off, custom layouts with unique field needs. This isn't to say that pages can't be given continuity, but it's an opt-in feature.


## Who is this for?

PageType is mainly a developer-focused module. It integrates with much of Drupal 7's core functionality in much the same way that the node module does, but being an add-on feature means this module is better suited for *developers* than *users*. It's less likely that themes will integrate smoothly with pages unless they're customized.


## API / Hooks

There's one core hook used to create page types, and that is `hook_pagetype_info()`. This hook is expected to return an array of information on the pagetype.

```php

/**
 * hook_pagetype_info()
 */
function hook_pagetype_info() : array
{
    $types['MACHINE_NAME'] = [
        'machine_name'      => 'MACHINE_NAME', // Required
        'name'              => t('Page type name'), // Required
        'plural'            => t('Page type pluralized name'), // Optional, DEFAULT: pagetype.name
        'description'       => t('Page type description.'), // Optional, DEFAULT: ''
        'has_continuity'    => 1 // (Whether the page type is re-usable or a one-off) Optional, DEFAULT: 0
    ];


    return $types;
}
```


## Attaching fields

Fields can be attached following the same method as Node, either through the ui at `structure/page-types/PAGETYPE/manage` or via [field_create_field()](https://api.drupal.org/api/drupal/modules!field!field.crud.inc/function/field_create_field/7.x) and [field_create_instance()](https://api.drupal.org/api/drupal/modules%21field%21field.crud.inc/function/field_create_instance/7.x).

This is best done on `hook_install()` and `hook_uninstall()`.
