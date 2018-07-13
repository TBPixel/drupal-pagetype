<?php

/**
 * @file
 */

use TBPixel\PageType\Page;


/**
 * Hook_token_info()
 */
function pagetype_token_info() : array {
  $types[PAGE::ENTITY_NAME] = [
    'name'          => t('Page Types'),
    'description'   => t('Tokens related to page types.'),
    'needs-data'    => PAGE::ENTITY_NAME,
  ];

  $tokens[Page::ENTITY_NAME]['title'] = [
    'name'          => t('Title'),
    'description'   => t('The title of the page.'),
  ];

  return [
    'types'  => $types,
    'tokens' => $tokens,
  ];
}

/**
 * Hook_tokens()
 */
function pagetype_tokens(string $type, array $tokens, array $data = [], array $options = []) : array {
  $replacements = [];

  if ($type !== Page::ENTITY_NAME) {
    return $replacements;
  }

  $page = $data[Page::ENTITY_NAME];

  foreach ($tokens as $name => $original) {
    switch ($name) {
      case 'title':
        $replacements[$original] = $page->title;
        break;
    }
  }

  return $replacements;
}
