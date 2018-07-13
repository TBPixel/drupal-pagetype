<?php


/**
 * hook_help()
 */
function pagetype_help(string $path, $arg) : string
{
    if ($path !== 'admin/help#pagetype') return '';


    $filepath = drupal_get_path('module', 'pagetype') . '/README.md';
    $readme   = file_exists($filepath) ? file_get_contents($filepath) : null;

    if (module_exists('markdown'))
    {
        $filters = module_invoke('markdown', 'filter_info');
        $info    = $filters['filter_markdown'];

        if (function_exists($info['process callback']))
        {
            $output = $info['process callback']($readme, NULL);
        }
        else
        {
            $output = '<pre>' . $readme . '</pre>';
        }
    }
    else
    {
        $output = '<pre>' . $readme . '</pre>';
    }


    return $output;
}
