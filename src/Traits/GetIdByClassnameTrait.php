<?php

namespace AKlump\Drupal\BatchFramework\Traits;

use AKlump\Drupal\BatchFramework\Helpers\CreateIdByClass;

/**
 * Use to automatically provide the id based on the classname.
 */
trait GetIdByClassnameTrait {

  /**
   * @return string
   *   A string generated from the class name.
   */
  public function getId(): string {
    return (new CreateIdByClass())($this);
  }

}
