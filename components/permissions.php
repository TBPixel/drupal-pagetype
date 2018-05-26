<?php

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
 *
 */
function pagetype_access(string $op, $page = null, $account = null) : bool
{
    // TODO: Implement access callback logic

    return true;
}
