<?php

use TBPixel\PageType\Page;


/**
 * hook_permission()
 */
function pagetype_permission() : array
{
    $permissions['administer pages'] = [
        'title' => t('Administer Pages'),
        'description' => t('Administer pagetype page entities')
    ];


    return $permissions;
}


/**
 * Pagetype access check and callback
 */
function pagetype_access(string $op, $page = null, $account = null) : bool
{
    if ($op === 'view' && ($page instanceof Page) && $page->status === 'published') return true;
    else return user_access('administer pages', $account);
}
