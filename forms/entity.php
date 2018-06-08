<?php

use TBPixel\PageType\Page;


/**
 * Returns a pagetype form given the
 */
function pagetype_new_form(string $type) : array
{
    $page = new Page($type);


    return drupal_get_form('pagetype_form', $page);
}


/**
 * Page entity form structure
 */
function pagetype_form(array $form, array &$state, Page $page) : array
{
    $state[Page::ENTITY_NAME] = $page;

    $form['title'] = [
        '#title'         => t('Title'),
        '#type'          => 'textfield',
        '#default_value' => $page->title ?? '',
        '#required'      => TRUE,
        '#weight'        => -50
    ];

    pagetype_path_attach_field($form, $state);
    field_attach_form(Page::ENTITY_NAME, $page, $form, $state);

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
 * Form to confirm deletion of pages
 */
function pagetype_delete_confirm_form(array $form, array &$state, Page $page) : array
{
    return $form;
}


/**
 * Submit handler for project delete form
 */
function pagetype_delete_confirm_form_submit(array $form, array &$state) : void
{

}
