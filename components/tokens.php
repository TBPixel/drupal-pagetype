<?php


/**
 * hook_token_info()
 */
function pagetype_token_info() : array
{
    $tokens['page']['title'] = [
        'name'          => t('Title'),
        'description'   => t('The title of the page.')
    ];


    return [
        'tokens' => $tokens
    ];
}


/**
 * hook_tokens()
 */
function pagetype_tokens(string $type, array $tokens, array $data = [], array $options = []) : array
{
    $replacements = [];

    if ($type !== 'page') return $replacements;

    $page = $data['page'];


    foreach ($tokens as $name => $original)
    {
        switch($name)
        {
            case 'title':
                $replacements[$original] = $page->title;
                break;
        }
    }


    return $replacements;
}
