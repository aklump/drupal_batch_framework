<?php

namespace AKlump\Drupal\BatchFramework\Traits;

use AKlump\Drupal\BatchFramework\Helpers\CreateLabelByClass;

/**
 * Use to automatically provide the id based on the classname.
 */
trait GetLabelByClassnameTrait {

  /**
   * @return string
   *   A string generated from the class name.
   */
  public function getLabel(): string {
    return (new CreateLabelByClass())($this);
  }

}
