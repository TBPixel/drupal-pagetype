<?php

namespace TBPixel\PageType\Exceptions;

use Exception;

/**
 * Special exception defined for when an Entity is missing a bundle.
 */
class MissingBundle extends Exception {

  /**
   * Constructor.
   */
  public function __construct(string $entity_type, string $machine_name) {
    parent::__construct("Entity Type '{$entity_type}' does not have the bundle '{$machine_name}'");
  }

}
