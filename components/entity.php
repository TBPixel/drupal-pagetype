<?php

use TBPixel\PageType\Page;
use TBPixel\PageType\Bundle;
use TBPixel\PageType\Exceptions\MissingBundle;


/**
 * hook_entity_info()
 */
function pagetype_entity_info() : array
{
    $types['page'] = [
        'label'             => t('Page'),
        'plural label'      => t('Pages'),
        'base table'        => 'pages',
        'module'            => 'pagetype',
        'access callback'   => 'pagetype_access',
        'uri callback'      => 'entity_class_uri',
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
        $types['page']['bundles'][$type->machine_name] = [
            'label' => $type->name,
            'admin' => [
                'path'              => 'admin/structure/page-types/manage/%page_type',
                'real path'         => 'admin/structure/page-types/manage/' . $type->uri(),
                'bundle argument'   => 4,
                'access arguments'  => ['administer pages']
            ]
        ];
    }


    return $types;
}



/**
 * Generates a title for a page
 */
function pagetype_page_title(int $id) : string
{
    $page = Page::findOne($id);


    return $page->title ?? '';
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
        field_info_instances('page', $page->type)
    );

    foreach ($fields as $field)
    {
        $build[$field] = field_view_field('page', $page, $field);
    }


    return drupal_render($build);
}


/**
 * Page load callback
 */
function page_load(int $id) : ?Page
{
    return Page::findOne($id);
}


/**
 * Page load multiple callback
 */
function page_load_multiple(array $ids) : array
{
    return Page::find($ids);
}


/**
 * Page type load callback
 */
function page_type_load(string $uri) : string
{
    $machine_name = str_replace('-', '_', $uri);

    if (is_null($bundle = Bundle::find($machine_name))) throw new MissingBundle('page', $machine_name);


    return $bundle->machine_name;
}
