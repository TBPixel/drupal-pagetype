<?php

use TBPixel\PageType\Bundle;


/**
 * hook_menu()
 */
function pagetype_menu() : array
{
    /**
     * Page Content Management
     */
    $routes['admin/pages'] = [
        'title'             => t('Pages'),
        'description'       => t('Find and manage pages.'),
        'page callback'     => 'drupal_get_form',
        'weight'            => -16,
        'page arguments'    => ['pagetype_admin_pages'],
        'access arguments'  => ['administer pages']
    ];

    $routes['admin/pages/overview'] = [
        'title' => t('Manage Pages'),
        'type'  => MENU_DEFAULT_LOCAL_TASK
    ];

    /** @var Bundle $type */
    foreach (Bundle::build() as $type)
    {
        if ($type->has_continuity)
        {
            $routes['admin/pages/' . $type->uri()] = [
                'title'             => $type->plural,
                'page callback'     => 'drupal_get_form',
                'page arguments'    => ['pagetype_admin_pages', $type->machine_name],
                'access arguments'  => ['administer pages']
            ];

            $routes['admin/pages/' . $type->uri() . '/add'] = [
                'title'             => t('Create @type', ['@type' => $type->name]),
                'page callback'     => 'pagetype_new_form',
                'page arguments'    => [$type->machine_name],
                'access callback'   => 'pagetype_access',
                'access arguments'  => ['create'],
                'type'              => MENU_LOCAL_ACTION
            ];
        }
        else
        {
            $routes['admin/pages/' . $type->uri()] = [
                'title'             => $type->plural,
                'page callback'     => 'pagetype_single_form',
                'page arguments'    => [$type->machine_name],
                'access callback'   => 'pagetype_access',
                'access arguments'  => ['create']
            ];
        }
    }


    /**
     * Page Type Management
     */
    $routes['admin/structure/page-types'] = [
        'title'             => t('Page types'),
        'description'       => t('Create, edit and delete page types.'),
        'page callback'     => 'drupal_get_form',
        'page arguments'    => ['pagetype_admin_types'],
        'access arguments'  => ['administer pages']
    ];

    $routes['admin/structure/page-types/manage'] = [
        'title'             => 'Manage',
        'type'              => MENU_DEFAULT_LOCAL_TASK,
        'weight'            => -10,
        'access arguments'  => ['administer pages']
    ];

    $routes['admin/structure/page-types/add'] = [
        'title'             => t('Add page type'),
        'type'              => MENU_LOCAL_ACTION,
        'page callback'     => 'drupal_get_form',
        'page arguments'    => ['pagetype_type_form'],
        'access arguments'  => ['administer pages']
    ];

    $routes['admin/structure/page-types/manage/%pagetype_type'] = [
        'title'             => t('Edit Page Type'),
        'page callback'     => 'drupal_get_form',
        'page arguments'    => ['pagetype_type_form', 4],
        'access arguments'  => ['administer pages']
    ];

    $routes['admin/structure/page-types/manage/%pagetype_type/edit'] = [
        'title'             => 'Edit',
        'type'              => MENU_DEFAULT_LOCAL_TASK,
        'access arguments'  => ['administer pages']
    ];

    $routes['admin/structure/page-types/manage/%pagetype_type/delete'] = [
        'title'             => 'Delete',
        'page arguments'    => ['pagetype_type_delete_confirm', 4],
        'access arguments'  => ['administer pages'],
    ];


    /**
     * Page CRUD routes
     */
    $routes['pages/%pagetype'] = [
        'title'             => t('Page'),
        'page callback'     => 'pagetype_page_view',
        'page arguments'    => [1],
        'access callback'   => 'pagetype_access',
        'access arguments'  => ['view', 1]
    ];

    $routes['pages/%pagetype/view'] = [
        'title' => 'View',
        'type'  => MENU_DEFAULT_LOCAL_TASK
    ];

    $routes['pages/%pagetype/edit'] = [
        'title'             => 'Edit',
        'page callback'     => 'drupal_get_form',
        'page arguments'    => ['pagetype_form', 1],
        'access callback'   => 'pagetype_access',
        'access arguments'  => ['update', 1],
        'type'              => MENU_LOCAL_TASK,
        'context'           => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE
    ];

    $routes['pages/%pagetype/delete'] = [
        'title'             => 'Delete',
        'page callback'     => 'drupal_get_form',
        'page arguments'    => ['pagetype_delete_confirm_form', 1],
        'access callback'   => 'pagetype_access',
        'access arguments'  => ['delete', 1],
        'type'              => MENU_LOCAL_TASK,
        'context'           => MENU_CONTEXT_INLINE
    ];


    return $routes;
}
