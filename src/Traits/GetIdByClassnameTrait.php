<?php

namespace AKlump\Drupal\BatchFramework\Traits;

/**
 * Use to automatically provide the id based on the classname.
 */
trait GetIdByClassnameTrait {

  /**
   * @return string
   *   A string generated from the class name.
   */
  public function getId(): string {
    $name = (new \ReflectionClass($this))->getShortName();
    $name = trim(preg_replace('/[A-Z]/', ' $0', $name));
    $name = ucwords($name);

    return $name;
  }

}
