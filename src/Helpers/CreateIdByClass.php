<?php

namespace AKlump\Drupal\BatchFramework\Helpers;

class CreateIdByClass {

  public function __invoke(object $object): string {
    $name = (new \ReflectionClass($object))->getShortName();
    $name = trim(preg_replace('/[A-Z]/', ' $0', $name));
    $name = ucwords($name);

    return $name;
  }

}
