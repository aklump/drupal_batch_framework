<?php

namespace AKlump\Drupal\BatchFramework\Helpers;

class CreateLabelByClass {

  /**
   * @param object|string $object_or_classname
   *   A classname or class instance.
   *
   * @return string
   * @throws \ReflectionException
   */
  public function __invoke($object_or_classname): string {
    $name = (new \ReflectionClass($object_or_classname))->getShortName();
    $name = trim(preg_replace('/[A-Z]/', ' $0', $name));
    $name = ucfirst(strtolower($name));

    return $name;
  }

}
