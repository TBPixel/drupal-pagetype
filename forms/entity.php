<?php

use TBPixel\PageType\Page;


/**
 * Returns a pagetype form given the
 */
function page_new_form(string $type) : array
{
    $page = new Page($type);


    return drupal_get_form('page_form', $page);
}


/**
 * Page entity form structure
 */
function page_form(array $form, array &$state, Page $page) : array
{
    $state['page'] = $page;

    $form['title'] = [
        '#title'         => t('Title'),
        '#type'          => 'textfield',
        '#default_value' => $page->title ?? '',
        '#required'      => TRUE,
        '#weight'        => -50
    ];

    field_attach_form('page', $page, $form, $state);

    $form['status'] = [
        '#title'        => t('Page Status'),
        '#type'         => 'fieldset',
        '#collapsible'  => true,
        '#collapsed'    => false,
        '#weight'       => 49
    ];
        $form['status']['published'] = [
            '#title'            => t('Published'),
            '#type'             => 'checkbox',
            '#default_value'    => ($page->status === 'published') ? true : false
        ];

    $form['submit'] = [
        '#type'     => 'submit',
        '#value'    => (isset($page->id) && !empty($page->id)) ? t('Update page') : t('Create page'),
        '#weight'   => 50
    ];


    return $form;
}


/**
 * Submit handler for the project add/edit form.
 */
function page_form_submit(array $form, array &$state) : void
{
    $values = $state['values'];
    /** @var Page $page */
    $page   = $state['page'];
    $fields = array_keys(
        field_info_instances('page', $page->type)
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
 * hook_form_FORM_ID_alter()
 */
function pagetype_form_page_form_alter(array &$form, array &$state) : void
{
    pagetype_path_attach_field($form, $state);
    pagetype_pathauto_attach_field($form, $state);
}


/**
 * Form to confirm deletion of pages
 */
function page_delete_confirm_form(array $form, array &$state, Page $page) : array
{
    return $form;
}


/**
 * Submit handler for project delete form
 */
function page_delete_confirm_form_submit(array $form, array &$state) : void
{

}
