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
    $settings['batch_update_callback']  = 'pagetype_pathauto_bulk_update_batch_process';


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
 * Update the URL aliases for multiple pages.
 */
function pagetype_page_update_alias_multiple(array $ids, string $operation, array $options = [])
{
    $options += ['message' => false];

    $pages = pagetype_load_multiple($ids);

    foreach ($pages as $page)
    {
        pagetype_page_update_alias($page, $operation, $options);
    }

    if (!empty($options['message']))
    {
        drupal_set_message(
            format_plural(count($ids), 'Update URL alias for 1 page.', 'Updated URL alias for @count pages.')
        );
    }
}



/**
 * Batch pathauto update callback
 */
function pagetype_pathauto_bulk_update_batch_process(array &$context) : void
{
    if (!isset($context['sandbox']['current']))
    {
        $context['sandbox']['count']    = 0;
        $context['sandbox']['current']  = 0;
    }


    $query = db_select(Page::TABLE, 'p');
    $query->leftJoin('url_alias', 'ua', "CONCAT('pages/', p.id) = ua.source");
    $query->addField('p', 'id');
    $query->isNull('ua.source');
    $query->condition('p.id', $context['sandbox']['current'], '>');
    $query->orderBy('p.id');
    $query->addTag('pathauto_bulk_update');
    $query->addMetaData('entity', Page::ENTITY_NAME);


    // Get the total amount of items to process
    if (!isset($context['sandbox']['total']))
    {
        $context['sandbox']['total'] = $query->countQuery()->execute()->fetchField();

        // If there are no pages to update, stop immediately
        if (!$context['sandbox']['total'])
        {
            $context['finished'] = 1;


            return;
        }
    }

    $query->range(0, 25);
    $ids = $query->execute()->fetchCol();

    pagetype_page_update_alias_multiple($ids, 'bulkupdate');

    $context['sandbox']['count']    += count($ids);
    $context['sandbox']['current']  = max($ids);
    $context['message']             = t('Update alias for page @id.', ['@id' => end($ids)]);

    if ($context['sandbox']['count'] != $context['sandbox']['total'])
    {
        $context['finished'] = $context['sandbox']['count'] / $context['sandbox']['total'];
    }
}
