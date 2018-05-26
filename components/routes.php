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
        'title'             => t('Manage Pages'),
        'description'       => t('Find and manage pages.'),
        'page callback'     => 'drupal_get_form',
        'weight'            => -9,
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
        $routes['admin/pages/' . $type->uri()] = [
            'title'             => $type->plural,
            'page callback'     => 'drupal_get_form',
            'page arguments'    => ['pagetype_admin_pages', $type->machine_name],
            'access arguments'  => ['administer pages']
        ];

        if ($type->has_continuity)
        {
            $routes['admin/pages/' . $type->uri() . '/add'] = [
                'title'             => t('Create @type', ['@type' => $type->name]),
                'page callback'     => 'page_new_form',
                'page arguments'    => [$type->machine_name],
                'access arguments'  => ['administer pages'],
                'type'              => MENU_LOCAL_ACTION
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

    $routes['admin/structure/page-types/manage/%page_type'] = [
        'title'             => t('Edit Page Type'),
        'page callback'     => 'drupal_get_form',
        'page arguments'    => ['pagetype_type_form', 4],
        'access arguments'  => ['administer pages']
    ];

    $routes['admin/structure/page-types/manage/%page_type/edit'] = [
        'title'             => 'Edit',
        'type'              => MENU_DEFAULT_LOCAL_TASK,
        'access arguments'  => ['administer pages']
    ];

    $routes['admin/structure/page-types/manage/%page_type/delete'] = [
        'title'             => 'Delete',
        'page arguments'    => ['pagetype_type_delete_confirm', 4],
        'access arguments'  => ['administer pages'],
    ];


    /**
     * Page CRUD routes
     */
    $routes['pages/%'] = [
        'title callback'    => 'pagetype_page_title',
        'title arguments'   => [1],
        'page callback'     => 'pagetype_page_preview',
        'page arguments'    => [1],
        'access arguments'  => ['administer pages']
    ];

    $routes['pages/%/view'] = [
        'title' => 'View',
        'type'  => MENU_DEFAULT_LOCAL_TASK
    ];

    $routes['pages/%page/edit'] = [
        'title'             => 'Edit',
        'page callback'     => 'drupal_get_form',
        'page arguments'    => ['page_form', 1],
        'access arguments'  => ['administer pages'],
        'type'              => MENU_LOCAL_TASK,
        'context'           => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE
    ];

    $routes['pages/%page/delete'] = [
        'title'             => 'Delete',
        'page callback'     => 'drupal_get_form',
        'page arguments'    => ['page_delete_confirm_form', 1],
        'access arguments'  => ['administer pages'],
        'type'              => MENU_LOCAL_TASK,
        'context'           => MENU_CONTEXT_INLINE
    ];


    return $routes;
}