<?php

use TBPixel\PageType\Page;
use TBPixel\PageType\Bundle;
use TBPixel\PageType\Exceptions\MissingBundle;


/**
 * hook_entity_info()
 */
function pagetype_entity_info() : array
{
    $types['pagetype'] = [
        'label'             => t('Page'),
        'plural label'      => t('Pages'),
        'base table'        => 'pages',
        'module'            => 'pagetype',
        'access callback'   => 'pagetype_access',
        'uri callback'      => 'pagetype_uri',
        'fieldable'         => true,
        'entity keys' => [
            'id'     => 'id',
            'label'  => 'title',
            'bundle' => 'type'
        ],
        'bundle keys' => [
            'bundle' => 'type'
        ],
        'view modes' => [
            'full' => [
                'label'             => t('Full page'),
                'custom settings'   => false
            ],
            'teaser' => [
                'label'             => t('Teaser'),
                'custom settings'   => true
            ]
        ],
        'bundles' => []
    ];


    /** @var Bundle $type */
    foreach(Bundle::build() as $type)
    {
        $types[Page::ENTITY_NAME]['bundles'][$type->machine_name] = [
            'label' => $type->name,
            'admin' => [
                'path'              => 'admin/structure/page-types/manage/%pagetype_type',
                'real path'         => 'admin/structure/page-types/manage/' . $type->uri(),
                'bundle argument'   => 4,
                'access arguments'  => ['administer pages']
            ]
        ];
    }


    return $types;
}


/**
 * hook_pagetype_insert()
 */
function pagetype_pagetype_insert(Page $page)
{
    pagetype_page_path_insert($page);

    if (module_exists('pathauto')) pagetype_page_update_alias($page, 'insert');
}


/**
 * hook_pagetype_update()
 */
function pagetype_pagetype_update(Page $page)
{
    pagetype_page_path_update($page);

    if (module_exists('pathauto')) pagetype_page_update_alias($page, 'update');
}


/**
 * hook_pagetype_delete()
 */
function pagetype_pagetype_delete(Page $page)
{
    pagetype_page_path_delete($page);

    if (module_exists('pathauto'))
    {
        $uri = entity_uri(Page::ENTITY_NAME, $page);

        pathauto_entity_path_delete_all(
            Page::ENTITY_NAME,
            $page,
            $uri['path']
        );
    }
}


/**
 * Generates the page entity machine uri
 */
function pagetype_uri(Page $page) : array
{
    return [
        'path' => "pages/{$page->id}"
    ];
}


/**
 * Generates a page view
 */
function pagetype_page_view(int $id, $view_mode = 'full') : array
{
    $page = Page::findOne($id);

    $page->content  = [];

    drupal_set_title($page->title);
    field_attach_prepare_view(Page::ENTITY_NAME, [$page->id => $page], $view_mode);
    entity_prepare_view(Page::ENTITY_NAME, [$page->id => $page]);

    $page->content += field_attach_view(Page::ENTITY_NAME, $page, $view_mode);

    $page->content += [
        '#theme'     => Page::ENTITY_NAME,
        '#element'   => $page,
        '#view_mode' => $view_mode,
        '#language'  => $page->language,
    ];


    return $page->content;
}


/**
 * Generates a page preview
 */
function pagetype_page_preview(int $id) : string
{
    $page = Page::findOne($id);

    drupal_set_title($page->title);


    $build['title'] = [
        '#type' => 'markup',
        '#markup' => check_plain($page->title),
        '#prefix' => '<h1 class="page__title">',
        '#suffix' => '</h1>'
    ];

    $fields = array_keys(
        field_info_instances(Page::ENTITY_NAME, $page->type)
    );

    foreach ($fields as $field)
    {
        $build[$field] = field_view_field(Page::ENTITY_NAME, $page, $field);
    }


    return drupal_render($build);
}


/**
 * Page load callback
 */
function pagetype_load(int $id) : ?Page
{
    return Page::findOne($id);
}


/**
 * Page load multiple callback
 */
function pagetype_load_multiple(array $ids) : array
{
    return Page::find($ids);
}


/**
 * Page type load callback
 */
function pagetype_type_load(string $uri) : string
{
    $machine_name = str_replace('-', '_', $uri);

    if (is_null($bundle = Bundle::find($machine_name))) throw new MissingBundle(Page::ENTITY_NAME, $machine_name);


    return $bundle->machine_name;
}
