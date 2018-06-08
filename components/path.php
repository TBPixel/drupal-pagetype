<?php

use TBPixel\PageType\Page;


/**
 * Callback to attach path field to page entity
 */
function pagetype_path_attach_field(array &$form, array &$state) : void
{
    // Support path module
    $path = [];
    $page = $state[Page::ENTITY_NAME];

    $path_defaults = [
        'pid'       => null,
        'source'    => ($page->id) ? "pages/{$page->id}" : null,
        'alias'     => '',
        'language'  => $page->language
    ];

    $uri = entity_uri(Page::ENTITY_NAME, $page);

    $conditions = [
        'source'    => $uri['path'],
        'language'  => $page->language
    ];

    $page->path = ($result = path_load($conditions)) ? $result : [];
    $page->path += $path_defaults;

    $form['path'] = [
        '#type'             => 'fieldset',
        '#title'            => t('URL path settings'),
        '#collapsible'      => true,
        '#collapsed'        => empty($page->path['alias']),
        '#group'            => 'additional_settings',
        '#access'           => user_access('create url aliases') || user_access('administer url aliases'),
        '#weight'           => 30,
        '#tree'             => true,
        '#element_validate' => ['path_form_element_validate'],
        '#attributes' => [
            'class' => ['path-form']
        ],
        '#attached' => [
            'js' => [drupal_get_path('module', 'path') . '/path.js']
        ]
    ];

    $form['path']['alias'] = [
        '#type'             => 'textfield',
        '#title'            => t('URL alias'),
        '#default_value'    => $page->path['alias'],
        '#maxlength'        => 255,
        '#description'      => t('Optionally specify an alternative URL by which this content can be accessed. For example, type "about" when writing an about page. Use a relative path and don\'t add a trailing slash or the URL alias won\'t work.')
    ];

    $form['path']['pid'] = [
        '#type'     => 'value',
        '#value'    => $page->path['pid']
    ];

    $form['path']['source'] = [
        '#type'     => 'value',
        '#value'    => $page->path['source']
    ];

    $form['path']['language'] = [
        '#type'     => 'value',
        '#value'    => $page->path['language']
    ];
}


/**
 * Callback to insert path for page entity
 */
function pagetype_page_path_insert(Page $page) : void
{
    if (!isset($page->path) || !isset($page->path['alias'])) return;

    $page->path['alias'] = trim($page->path['alias']);

    if (empty($page->path['alias'])) return;

    $uri = entity_uri(Page::ENTITY_NAME, $page);

    $page->path['source']   = $uri['path'];
    $page->path['language'] = $page->language;

    path_save($page->path);
}

/**
 * Callback to update path for page entity
 */
function pagetype_page_path_update(Page $page) : void
{
    if (!isset($page->path)) return;

    $page->path['alias'] = isset($page->path['alias']) ? trim($path['alias']) : '';

    if (!empty($page->path['pid']) && empty($page->path['alias'])) path_delete($page->path['pid']);

    pagetype_page_path_insert($page);
}


/**
 * Callback to delete path for page entity
 */
function pagetype_page_path_delete(Page $page) : void
{
    $uri = entity_uri(Page::ENTITY_NAME, $page);

    path_delete([
        'source' => $uri['path']
    ]);
}
