<?php

use TBPixel\PageType\Page;
use TBPixel\PageType\Bundle;


/**
 * hook_theme()
 */
function pagetype_theme() : array
{
    $themes['pagetype_admin_types'] = [
        'variables' => [
            'type' => null
        ]
    ];


    return $themes;
}


/**
 * Return a themed output for pagetype types
 */
function theme_pagetype_admin_types(array $variables) : string
{
    $type = $variables['type'];

    if (!$type instanceof Bundle) return '';

    $output = check_plain($type->name);
    $output .= "<div><small>Machine name: {$type->machine_name}</small></div>";
    $output .= '<div class="description">' . filter_xss_admin($type->description) . '</div>';


    return $output;
}


/**
 * hook_preprocess_html()
 */
function pagetype_preprocess_html(array &$vars) : void
{
    if (!(strpos($path = current_path(), 'pages/') === 0)) return;

    $id   = explode('/', $path);
    $id   = end($id);

    if (!is_numeric($id) || is_null($page = Page::findOne($id))) return;

    $class = drupal_clean_css_identifier(
        'page--' . str_replace('_', '-', $page->type)
    );

    array_unshift($vars['classes_array'], $class);
}
