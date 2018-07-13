<?php

/**
 * @file
 * Defines forms for custom pagetype types.
 */

use TBPixel\PageType\Page;
use TBPixel\PageType\Bundle;

/**
 * Page type create / edit form.
 */
function pagetype_type_form(array $form, array &$state, string $type = NULL) : array {
  if ($type) {
    $machineName = str_replace('-', '_', $type);
    $bundle      = Bundle::find($machineName);
  }
  else {
    $bundle = new Bundle('', '');
  }

  $state[Page::ENTITY_NAME . '_bundle'] = $bundle;

  $form['machine_name'] = [
    '#type'          => 'textfield',
    '#title'         => 'Machine Name',
    '#default_value' => $bundle->machineName ?? '',
    '#required'      => TRUE,
  ];

  $form['name'] = [
    '#type'          => 'textfield',
    '#title'         => 'Name',
    '#default_value' => $bundle->name ?? '',
    '#required'      => TRUE,
  ];

  $form['plural'] = [
    '#type'          => 'textfield',
    '#title'         => 'Plural Name',
    '#default_value' => $bundle->plural ?? '',
  ];

  $form['description'] = [
    '#type'          => 'textarea',
    '#title'         => 'Description',
    '#default_value' => $bundle->description ?? '',
  ];

  $form['has_continuity'] = [
    '#type'          => 'checkbox',
    '#title'         => 'Has Continuity',
    '#description'   => t('Determines whether the pagetype can be reused (like a node) or not.'),
    '#default_value' => $bundle->hasContinuity ?? FALSE,
  ];

  $form['actions'] = [
    '#type'     => 'actions',
  ];

  $form['actions']['submit'] = [
    '#type'     => 'submit',
    '#submit'   => ['pagetype_type_form_submit'],
    '#value'    => ($bundle === NULL) ? 'Create page type' : 'Save page type',
    '#weight'   => 5,
  ];

  if (!empty($bundle->machineName)) {
    $form['actions']['delete'] = [
      '#type'     => 'submit',
      '#submit'   => ['pagetype_type_form_delete_submit'],
      '#value'    => 'delete',
      '#weight'   => 15,
    ];
  }

  return $form;
}

/**
 * Page type form validation handler.
 */
function pagetype_type_form_validate(array $form, array &$state) : void {
  $values = $state['values'];

  if (strlen($values['name']) > 255) {
    form_set_error('name', t('Name may not be greater than 255 characters!'));
  }
  if (strlen($values['plural']) > 255) {
    form_set_error('plural', t('Plural Name may not be greater than 255 characters!'));
  }
  if (strlen($values['machine_name']) > 32) {
    form_set_error('machine_name', t('Machine name may not be greater than 32 characters!'));
  }
}

/**
 * Page type form submit handler.
 */
function pagetype_type_form_submit(array $form, array &$state) : void {
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
        t('The page-type: @type has been saved.', ['@type' => $type->machineName])
    );

  $state['redirect'] = 'admin/structure/page-types';
}

/**
 * Redirection to delete confirmation form.
 */
function pagetype_type_form_delete_submit(array $form, array &$state) : void {
  $destination = [];

  if (isset($_GET['destination'])) {
    $destination = drupal_get_destination();
    unset($_GET['destination']);
  }

  $bundle = $state[Page::ENTITY_NAME . '_bundle'];

  $state['redirect'] = [
    "admin/structure/page-types/manage/{$bundle->machineName}/delete",
        [
          'query' => $destination,
        ],
  ];
}

/**
 * Confirmation form for the deletion of a page type.
 */
function pagetype_type_delete_confirm(array $form, array &$state, string $type) : array {
  $form['pagetype'] = [
    '#type'     => 'value',
    '#value'    => $type,
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
 * Confirmation of the form delete.
 */
function pagetype_type_delete_confirm_submit(array $form, array &$state) : void {
  if ($state['values']['confirm']) {
    Bundle::delete($state['values']['pagetype']);
    cache_clear_all();
  }

  $state['redirect'] = 'admin/structure/page-types';
}
