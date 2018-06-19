<?php

use TBPixel\PageType\Page;
use TBPixel\PageType\Bundle;


/**
 * Returns a pagetype form given the type
 */
function pagetype_new_form(string $type) : array
{
    $page = new Page($type);


    return drupal_get_form('pagetype_form', $page);
}


/**
 * Returns a pagetype form given the type
 */
function pagetype_single_form(string $type) : array
{
    $page = Page::findOneBy([
        'type' => $type
    ]);

    if (!$page) $page = new Page($type);


    return drupal_get_form('pagetype_form', $page);
}


/**
 * Page entity form structure
 */
function pagetype_form(array $form, array &$state, Page $page) : array
{
    $bundle = Bundle::find($page->type);

    $state[Page::ENTITY_NAME]               = $page;
    $state[Page::ENTITY_NAME . '_bundle']   = $bundle;

    $form['title'] = [
        '#title'         => t('Title'),
        '#type'          => 'textfield',
        '#default_value' => $page->title ?? '',
        '#required'      => TRUE,
        '#weight'        => -5
    ];


    $form['options'] = [
        '#type'         => 'fieldset',
        '#title'        => t('Publishing options'),
        '#group'        => 'additional_settings',
        '#collapsible'  => true,
        '#collapsed'    => true,
        '#weight'       => 95,
    ];

    $form['options']['published'] = [
        '#title'            => t('Published'),
        '#type'             => 'checkbox',
        '#default_value'    => ($page->status === 'published') ? true : false
    ];



    $form['actions'] = [
        '#type' => 'actions'
    ];

    $form['actions']['submit'] = [
        '#type'     => 'submit',
        '#submit'   => [Page::ENTITY_NAME . '_form_submit'],
        '#value'    => (!empty($page->id)) ? t('Update page') : t('Create page'),
        '#weight'   => 5
    ];

    if (!empty($page->id))
    {
        $form['actions']['delete'] = [
            '#type'     => 'submit',
            '#submit'   => [Page::ENTITY_NAME . '_form_delete_submit'],
            '#value'    => t('Delete'),
            '#weight'   => 15
        ];
    }

    pagetype_path_attach_field($form, $state);
    field_attach_form(Page::ENTITY_NAME, $page, $form, $state);


    return $form;
}


/**
 * Submit handler for the project add/edit form.
 */
function pagetype_form_submit(array $form, array &$state) : void
{
    $values = $state['values'];
    /** @var Page $page */
    $page   = $state[Page::ENTITY_NAME];
    $fields = array_keys(
        field_info_instances(Page::ENTITY_NAME, $page->type)
    );

    $status = $values['published'] ? 'published' : 'unpublished';

    $page->setTitle($values['title']);
    $page->setStatus($status);

    foreach ($fields as $field)
    {
        $page->{$field} = $values[$field];
    }

    $page->save();

    drupal_set_message(
        t('The page: @title has been saved.', ['@title' => $page->title])
    );

    $state['redirect'] = 'admin/pages';
}


/**
 * Form to handle redirection to the confirm deletion form
 */
function pagetype_form_delete_submit(array $form, array &$state) : void
{
    $destination = [];

    if (isset($_GET['destination']))
    {
        $destination = drupal_get_destination();
        unset($_GET['destination']);
    }

    $page = $state[Page::ENTITY_NAME];


    $state['redirect'] = [
        "pages/{$page->id}/delete",
        [
            'query' => $destination
        ]
    ];
}


/**
 * Form to confirm deletion of pages
 */
function pagetype_delete_confirm_form(array $form, array &$state, Page $page) : array
{
    $state[Page::ENTITY_NAME] = $page;


    return confirm_form(
        $form,
        t('Are you sure you want to delete @title', ['@title' => $page->title]),
        "pages/{$page->id}",
        t('This action cannot be undone.'),
        t('Delete'),
        t('Cancel')
    );
}


/**
 * Submit handler for project delete form
 */
function pagetype_delete_confirm_form_submit(array $form, array &$state) : void
{
    /** @var Page $page */
    $page = $state[Page::ENTITY_NAME];

    if ($state['values']['confirm'])
    {
        $page->delete();

        cache_clear_all();

        $display = [
            '@type'     => $page->type,
            '@title'    => $page->title
        ];

        watchdog('pagetype', '@type: deleted @title.', $display);
        drupal_set_message(
            t('@type @title has been deleted.', $display)
        );
    }

    $state['redirect'] = 'admin/pages';
}
