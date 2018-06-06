<?php

use TBPixel\PageType\Page;
use TBPixel\PageType\Bundle;


/**
 * Page callback: Admin form for pages.
 */
function pagetype_admin_pages(array $form, array &$state, string $filter = null) : array
{
    $form['filter']     = pagetype_admin_pages_filter_form($filter);
    $form['#submit'][]  = 'pagetype_admin_pages_filter_form_submit';
    $form['admin']      = pagetype_admin_pages_pages($filter);


    return $form;
}


/**
 * Returns the page admin filters form array
 */
function pagetype_admin_pages_filter_form(?string $filter) : array
{
    return [];
}


/**
 *
 */
function pagetype_admin_pages_filter_form_submit(array $form, array &$state) : void
{

}


/**
 * Form builder: Builds the page admin overview form.
 */
function pagetype_admin_pages_pages(?string $filter) : array
{
    $admin_access = user_access('administer pages');
    $form         = [];


    // Build the sortable table header.
    $header = array(
        'title'         => ['data' => t('Title'), 'field' => 'p.title'],
        'type'          => ['data' => t('Type'), 'field' => 'p.type'],
        'operations'    => ['data' => t('Operations')],
        'created'       => ['data' => t('Created'), 'field' => 'p.created', 'sort' => 'DESC']
    );


    $query = db_select(Page::TABLE, 'p')->extend('PagerDefault')->extend('TableSort');

    $query->fields('p',['id']);

    if ($filter) $query->condition('type', $filter);

    $ids = $query
        ->limit(50)
        ->orderByHeader($header)
        ->execute()
        ->fetchCol();

    if (count($ids) <= 0) return $form;


    $pages       = page_load_multiple($ids);
    $destination = drupal_get_destination();

    // Prepare the list of nodes.
    $options = [];

    foreach ($pages as $page)
    {
        $uri = entity_uri('page', $page);

        $operations['edit'] = [
            'title' => t('edit'),
            'href'  => $uri['path'] . '/edit',
            'query' => $destination
        ];

        $operations['delete'] = [
            'title' => t('delete'),
            'href'  => $uri['path'] . '/delete',
            'query' => $destination
        ];

        $options[$page->id] = [
            'title'      => [
                'data' => [
                    '#type'  => 'link',
                    '#title' => $page->title,
                    '#href'  => $page->uri()
                ]
            ],
            'type'       => $page->type,
            'created'    => $page->created->format('F-d, Y'),
            'operations' => [
                'data' => [
                    '#theme' => 'links__node_operations',
                    '#links' => $operations,
                    '#attributes' => [
                        'class' => ['links', 'inline']
                    ]
                ]
            ]
        ];
    }


    $form['pages'] = [
        '#type'     => 'tableselect',
        '#header'   => $header,
        '#options'  => $options,
        '#empty'    => t('No pages available.'),
    ];

    $form['pager'] = [
        '#markup' => theme('pager')
    ];


    return $form;
}


/**$state
 * Page callback: Admin form for page types.
 */
function pagetype_admin_types(array $form, array &$state) : array
{
    $types = Bundle::build();
    $can_manage_fields = module_exists('field_ui') && user_access('administer fields');


    $header = [
        t('Name'), [
            'data'      => t('Operations'),
            'colspan'   => $can_manage_fields ? 4 : 2
        ]
    ];
    $rows = [];


    /** @var Bundle $type */
    foreach ($types as $type)
    {
        $uri = $type->uri();
        $row = [
            theme('pagetype_admin_types', ['type' => $type])
        ];


        $row[] = [
            'data' => l(
                t('edit'), "admin/structure/page-types/manage/{$uri}"
            )
        ];

        $row[] = [
            'data' => l(
                t('delete'), "admin/structure/page-types/manage/{$uri}/delete"
            )
        ];


        if ($can_manage_fields)
        {
            $row[] = [
                'data' => l(
                    t('manage fields'), "admin/structure/page-types/manage/{$uri}/fields"
                )
            ];

            $row[] = [
                'data' => l(
                    t('manage display'), "admin/structure/page-types/manage/{$uri}/display"
                )
            ];
        }

        $rows[] = $row;
    }


    $form['pagetype_table'] = [
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => t('No page types available.</a>')
    ];



    return $form;
}
