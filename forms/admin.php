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
    $session = $_SESSION['pagetype_overview_filter'] ?? [];
    $filters = pagetype_filters();

    if ($filter) unset($filters['type']);


    $form['filters'] = [
        '#type'     => 'fieldset',
        '#title'    => t('Show only items where'),
        '#theme'    => 'exposed_filters__node'
    ];

    $i = 0;

    foreach ($session as $type => $value)
    {
        if ($type === 'search') continue;

        $value = $filters[$type]['options'][$value];

        $t_args = [
            '@property' => $filters[$type]['title'],
            '@value'    => $value
        ];

        if ($i++)
        {
            $form['filters']['current'][] = [
                '#markup' => t('and where @property is @value', $t_args)
            ];
        }
        else
        {
            $form['filters']['current'][] = [
                '#markup' => t('where @property is @value', $t_args)
            ];
        }

        if (in_array($type, ['type', 'language'])) unset($filters[$type]);
    }


    $form['filters']['status'] = [
        '#type'     => 'container'
    ];

    $form['filters']['status']['filters'] = [
        '#type'     => 'container'
    ];

    $form['filters']['status']['filters']['search'] = [
        '#type'             => 'textfield',
        '#title'            => 'Search',
        '#size'             => 60,
        '#default_value'    => $session['search'] ?? ''
    ];

    foreach ($filters as $key => $filter)
    {
        $form['filters']['status']['filters'][$key] = [
            '#type'             => 'select',
            '#options'          => $filter['options'],
            '#title'            => $filter['title'],
            '#default_value'    => '[any]'
        ];
    }

    $form['filters']['status']['actions'] = [
        '#type'     => 'actions'
    ];

    $form['filters']['status']['actions']['submit'] = [
        '#type'     => 'submit',
        '#value'    => t('Filter')
    ];

    if (count($session))
    {
        $form['filters']['status']['actions']['undo'] = [
            '#type'     => 'submit',
            '#value'    => t('Undo')
        ];

        $form['filters']['status']['actions']['reset'] = [
            '#type'     => 'submit',
            '#value'    => t('Reset')
        ];
    }

    drupal_add_js('misc/form.js');


    return $form;
}


/**
 * Handle form submission of the pagetype filters
 */
function pagetype_admin_pages_filter_form_submit(array $form, array &$state) : void
{
    $filters = pagetype_filters();

    switch($state['values']['op'])
    {
        case t('Filter'):
            foreach($filters as $type => $filter)
            {
                if (isset($state['values'][$type]) && $state['values'][$type] !== '[any]')
                {
                    $flat_options = form_options_flatten($filters[$type]['options']);

                    if (isset($flat_options[$state['values'][$type]]))
                    {
                        $_SESSION['pagetype_overview_filter'][$type] = $state['values'][$type];
                    }
                }
            }

            if (isset($state['values']['search']) && !empty($state['values']['search']))
            {
                $_SESSION['pagetype_overview_filter']['search'] = $state['values']['search'];
            }
        break;
        case t('Undo'):
            array_pop($_SESSION['pagetype_overview_filter']);
        break;
        case t('Reset'):
            $_SESSION['pagetype_overview_filter'] = [];
        break;
    }
}


/**
 * Return an array of filters available for the pagetype entity
 */
function pagetype_filters() : array
{
    $types = [];

    foreach (Bundle::build() as $key => $type)
    {
        $types[$type->machine_name] = $type->name;
    }


    $filters['status'] = [
        'title'     => t('Status'),
        'options'   => [
            '[any]'         => t('Any'),
            'published'     => t('Published'),
            'unpublished'   => t('Unpublished')
        ]
    ];

    $filters['type'] = [
        'title'     => t('Type'),
        'options'   => [
            '[any]'     => t('Any')
        ] + $types
    ];


    return $filters;
}


/**
 * Applies filters for pagetype overview based on session
 */
function pagetype_build_filter_query(SelectQueryInterface $query)
{
    $filters = $_SESSION['pagetype_overview_filter'] ?? [];

    foreach ($filters as $type => $value)
    {
        if ($type === 'search')
        {
            $value = strtolower($value);

            $query->condition("p.title", "%{$value}%", 'LIKE');
        }
        else
        {
            $query->condition("p.{$type}", $value);
        }
    }
}


/**
 * Form builder: Builds the page admin overview form.
 */
function pagetype_admin_pages_pages(?string $filter) : array
{
    $admin_access = user_access('administer pages');
    $form         = [];


    // Build the sortable table header.
    $header = [
        'title' => [
            'data'  => t('Title'),
            'field' => 'p.title'
        ],
        'type' => [
            'data'  => t('Type'),
            'field' => 'p.type'
        ],
        'status' => [
            'data'  => t('Status'),
            'field' => 'p.status'
        ],
        'created' => [
            'data'  => t('Created'),
            'field' => 'p.created',
            'sort'  => 'DESC'
        ],
        'operations' => [
            'data'  => t('Operations')
        ]
    ];


    $query = db_select(Page::TABLE, 'p')->extend('PagerDefault')->extend('TableSort');

    $query->fields('p',['id']);

    pagetype_build_filter_query($query);
    if ($filter) $query->condition('type', $filter);

    $ids = $query
        ->limit(50)
        ->orderByHeader($header)
        ->execute()
        ->fetchCol();

    if (count($ids) <= 0) return $form;


    $pages       = pagetype_load_multiple($ids);
    $destination = drupal_get_destination();

    // Prepare the list of nodes.
    $options = [];

    foreach ($pages as $page)
    {
        $uri = entity_uri(Page::ENTITY_NAME, $page);

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
            'status'     => $page->status,
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


/**
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
