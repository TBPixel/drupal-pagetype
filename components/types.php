<?php

/**
 * @file
 * Defines the built-in types supported by the pagetype module.
 */

/**
 * Hook_pagetype_info()
 */
function pagetype_pagetype_info() : array {
  $types['basic'] = [
    'machine_name'      => 'basic',
    'name'              => t('Basic Page'),
    'plural'            => t('Basic Pages'),
    'description'       => t('A basic page for creating simple, one-off content.'),
    'has_continuity'    => 1,
  ];

  return $types;
}
