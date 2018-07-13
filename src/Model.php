<?php

namespace TBPixel\PageType;

/**
 * Abstract helper class to represent a data model object.
 */
abstract class Model {

  /**
   * PHP Magic getter, allowing read access to private/protected properties.
   */
  public function __get(string $name) {
    if (property_exists($this, $name)) {
      return $this->{$name};
    }
  }

  /**
   * PHP Magic isset checker, allowing read access to private/protected properties.
   */
  public function __isset(string $name) : bool {
    return property_exists($this, $name);
  }

}
