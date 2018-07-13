<?php

/**
 * @file
 * Defines access permissions for the pagetype module.
 */

use TBPixel\PageType\Page;

/**
 * Hook_permission()
 */
function pagetype_permission() : array {
  $permissions['administer pages'] = [
    'title' => t('Administer Pages'),
    'description' => t('Administer pagetype page entities'),
  ];

  return $permissions;
}

/**
 * Pagetype access check and callback.
 */
function pagetype_access(string $op, $page = NULL, $account = NULL) : bool {
  if ($op === 'view' && ($page instanceof Page) && $page->status === 'published') {
    return TRUE;
  }
  else {
    return user_access('administer pages', $account);
  }
}
