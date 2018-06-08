<?php

use TBPixel\PageType\Bundle;
use TBPixel\PageType\Page;


/**
 * hook_pathauto()
 */
function pagetype_pathauto(string $operation)
{
    $settings = [];

    if ($operation !== 'settings') return (object) $settings;

    $settings['module']                 = 'pagetype';
    $settings['token_type']             = Page::ENTITY_NAME;
    $settings['patterndefault']         = '['. Page::ENTITY_NAME .':title]';
    $settings['groupheader']            = t('Page paths');
    $settings['patterndescr']           = t('Default path pattern');
    // TODO: Write batch update callback
    $settings['batch_update_callback']  = 'node_pathauto_bulk_update_batch_process';


    /** @var Bundle $type */
    foreach (Bundle::build() as $type)
    {
        $settings['patternitems'][$type->machine_name] = t('Default pattern for @type page type.', ['@type' => $type->machine_name]);
    }


    return (object) $settings;
}


/**
 * hook_path_alias_types()
 */
function pagetype_path_alias_types()
{
    return [
        'pages/' => t('Page')
    ];
}


/**
 * Update the URL aliases for an individual Page.
 *
 * @param $page
 *   A Page object.
 * @param $op
 *   Operation being performed on the Page ('insert', 'update' or 'bulkupdate').
 * @param $options
 *   An optional array of additional options.
 */
function pagetype_page_update_alias(Page $page, string $operation, array $options = [])
{
    // Skip processing if the user has disabled pathauto for the page
    if (
        isset($page->path['pathauto']) &&
        empty($page->path['pathauto']) &&
        empty($options['force'])
    ) return false;

    $options += [
        'language' => pathauto_entity_language(Page::ENTITY_NAME, $page)
    ];

    // Skip processing if the Page has no pattern
    if (!pathauto_pattern_load_by_entity(Page::ENTITY_NAME, $page->type, $options['language'])) return false;

    module_load_include('inc', 'pathauto');
    $uri = entity_uri(Page::ENTITY_NAME, $page);


    return pathauto_create_alias(
        Page::ENTITY_NAME,
        $operation,
        $uri['path'],
        [Page::ENTITY_NAME => $page],
        $page->type,
        $options['language']
    );
}


/**
 * Callback to alter form, adding pathauto field if pathauto is enabled
 */
// function pagetype_pathauto_attach_field(array &$form, array &$state) : void
// {
//     if (!module_exists('pathauto')) return;

//     $page       = $state[Page::ENTITY_NAME];
//     $langcode   = pathauto_entity_language(Page::ENTITY_NAME, $page);

//     pathauto_field_attach_form(Page::ENTITY_NAME, $page, $form, $state, $langcode);
// }
