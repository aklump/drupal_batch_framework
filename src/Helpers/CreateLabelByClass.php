<?php

namespace AKlump\Drupal\BatchFramework\Helpers;

class CreateLabelByClass {

  public function __invoke(object $object): string {
    $name = (new \ReflectionClass($object))->getShortName();
    $name = trim(preg_replace('/[A-Z]/', ' $0', $name));
    $name = ucfirst(strtolower($name));

    return $name;
  }

}
