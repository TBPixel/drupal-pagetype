<?php

namespace TBPixel\PageType\Exceptions;

use Exception;


class MissingBundle extends Exception
{
    public function __construct(string $entity_type, string $machine_name)
    {
        parent::__construct("Entity Type '{$entity_type}' does not have the bundle '{$machine_name}'");
    }
}
