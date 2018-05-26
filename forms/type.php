<?php

use TBPixel\PageType\Bundle;


/**
 * Page type create / edit form
 */
function pagetype_type_form(array $form, array &$state, string $type = null) : array
{
    if ($type)
    {
        $machine_name = str_replace('-', '_', $type);
        $bundle       = Bundle::find($machine_name);
    }
    else $bundle = new Bundle('', '');


    $form['machine_name'] = [
        '#type'          => 'textfield',
        '#title'         => 'Machine Name',
        '#default_value' => $bundle->machine_name ?? '',
        '#required'      => true
    ];

    $form['name'] = [
        '#type'          => 'textfield',
        '#title'         => 'Name',
        '#default_value' => $bundle->name ?? '',
        '#required'      => true
    ];

    $form['plural'] = [
        '#type'          => 'textfield',
        '#title'         => 'Plural Name',
        '#default_value' => $bundle->plural ?? ''
    ];

    $form['description'] = [
        '#type'          => 'textarea',
        '#title'         => 'Description',
        '#default_value' => $bundle->description ?? '',
    ];

    $form['has_continuity'] = [
        '#type'          => 'checkbox',
        '#title'         => 'Has Continuity',
        '#description'   => 'Determines whether the pagetype can be reused (like a node) or not.',
        '#default_value' => $bundle->has_continuity ?? false
    ];

    $form['submit'] = [
        '#type'     => 'submit',
        '#value'    => ($bundle === null) ? 'Create page type' : 'Save page type'
    ];


    return $form;
}


/**
 * Page type form validation handler
 */
function pagetype_type_form_validate(array $form, array &$state) : void
{
    $values = $state['values'];

    if (strlen($values['name']) > 255) form_set_error('name', 'Name may not be greater than 255 characters!');
    if (strlen($values['plural']) > 255) form_set_error('plural', 'Plural Name may not be greater than 255 characters!');
    if (strlen($values['machine_name']) > 32) form_set_error('machine_name', 'Machine name may not be greater than 32 characters!');
}


/**
 * Page type form submit handler
 */
function pagetype_type_form_submit(array $form, array &$state) : void
{
    $values = $state['values'];

    $type = new Bundle(
        $values['machine_name'],
        $values['name']
    );

    $type->setPlural($values['plural']);
    $type->setDescription($values['description']);
    $type->setHasContinuity((bool) $values['has_continuity']);

    $type->save();

    cache_clear_all();

    drupal_set_message(
        t('The page-type: @type has been saved.', ['@type' => $type->machine_name])
    );


    $state['redirect'] = 'admin/structure/page-types';
}



/**
 * Confirmation form for the deletion of a page type
 */
function pagetype_type_delete_confirm(array $form, array &$state, string $type) : array
{
    $form['pagetype'] = [
        '#type'     => 'value',
        '#value'    => $type
    ];


    return confirm_form(
        $form,
        t('Are you sure you want to delete @type', ['@type' => $type]),
        "admin/structure/page-types/manage/{$type}",
        t('This action cannot be undone.'),
        t('Confirm'),
        t('Cancel')
    );
}


/**
 * Confirmation of the form delete 
 */
function pagetype_type_delete_confirm_submit(array $form, array &$state) : void
{
    if ($state['values']['confirm'])
    {
        Bundle::delete($state['values']['pagetype']);
        cache_clear_all();        
    }


    $state['redirect'] = 'admin/structure/page-types';
}
