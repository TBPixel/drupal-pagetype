<?php

/**
 * @file
 * Defines the custom entity type and associated loaders / callbacks.
 */

use TBPixel\PageType\Page;
use TBPixel\PageType\Bundle;
use TBPixel\PageType\Exceptions\MissingBundle;

/**
 * Hook_entity_info()
 */
function pagetype_entity_info() : array {
  $types['pagetype'] = [
    'label'             => t('Page'),
    'plural label'      => t('Pages'),
    'base table'        => 'pages',
    'module'            => 'pagetype',
    'access callback'   => 'pagetype_access',
    'uri callback'      => 'pagetype_uri',
    'fieldable'         => TRUE,
    'entity keys' => [
      'id'     => 'id',
      'label'  => 'title',
      'bundle' => 'type',
    ],
    'bundle keys' => [
      'bundle' => 'type',
    ],
    'view modes' => [
      'full' => [
        'label'             => t('Full page'),
        'custom settings'   => FALSE,
      ],
      'teaser' => [
        'label'             => t('Teaser'),
        'custom settings'   => TRUE,
      ],
    ],
    'bundles' => [],
  ];

  /** @var \TBPixel\PageType\Bundle $type */
  foreach (Bundle::build() as $type) {
    $types[Page::ENTITY_NAME]['bundles'][$type->machineName] = [
      'label' => $type->name,
      'admin' => [
        'path'              => 'admin/structure/page-types/manage/%pagetype_type',
        'real path'         => 'admin/structure/page-types/manage/' . $type->uri(),
        'bundle argument'   => 4,
        'access arguments'  => ['administer pages'],
      ],
    ];
  }

  return $types;
}

/**
 * Hook_entity_load()
 */
function pagetype_entity_load(array &$entities, string $type) : void {
  if ($type !== Page::ENTITY_NAME) {
    return;
  }

  $pages = Page::mapDbResults($entities);

  foreach ($entities as $key => $entity) {
    $entities[$key] = $pages[$key];
  }
}

/**
 * Hook_pagetype_insert()
 */
function pagetype_pagetype_insert(Page $page) {
  pagetype_page_path_insert($page);

  if (module_exists('pathauto')) {
    pagetype_page_update_alias($page, 'insert');
  }
}

/**
 * Hook_pagetype_update()
 */
function pagetype_pagetype_update(Page $page) {
  pagetype_page_path_update($page);

  if (module_exists('pathauto')) {
    pagetype_page_update_alias($page, 'update');
  }
}

/**
 * Hook_pagetype_delete()
 */
function pagetype_pagetype_delete(Page $page) {
  pagetype_page_path_delete($page);

  if (module_exists('pathauto')) {
    $uri = entity_uri(Page::ENTITY_NAME, $page);

    pathauto_entity_path_delete_all(
        Page::ENTITY_NAME,
        $page,
        $uri['path']
    );
  }
}

/**
 * Generates the page entity machine uri.
 */
function pagetype_uri(Page $page) : array {
  return [
    'path' => "pages/{$page->id}",
  ];
}

/**
 * Dynamically returns a page title.
 */
function pagetype_page_title(Page $page) : string {
  return $page->title;
}

/**
 * Generates a page view.
 */
function pagetype_page_view($page, string $view_mode = 'full') : array {
  if (!($page instanceof Page)) {
    drupal_not_found();
    drupal_exit();

    return [];
  }

  $page->content = [];

  field_attach_prepare_view(Page::ENTITY_NAME, [$page->id => $page], $view_mode);
  entity_prepare_view(Page::ENTITY_NAME, [$page->id => $page]);

  $page->content += field_attach_view(Page::ENTITY_NAME, $page, $view_mode);

  $page->content += [
    '#theme'     => Page::ENTITY_NAME,
    '#element'   => $page,
    '#view_mode' => $view_mode,
    '#language'  => $page->language,
  ];

  return $page->content;
}

/**
 * Page load callback.
 */
function pagetype_load(int $id) : ?Page {
  return Page::findOne($id);
}

/**
 * Page load multiple callback.
 */
function pagetype_load_multiple(array $ids) : array {
  return Page::find($ids);
}

/**
 * Page type load callback.
 */
function pagetype_type_load(string $uri) : string {
  $machineName = str_replace('-', '_', $uri);

  if (is_null($bundle = Bundle::find($machineName))) {
    throw new MissingBundle(Page::ENTITY_NAME, $machineName);
  }

  return $bundle->machineName;
}
