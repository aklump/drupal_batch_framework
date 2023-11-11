<?php

namespace AKlump\Drupal\BatchFramework\Traits;

use AKlump\Drupal\BatchFramework\DrupalMode;

trait HasDrupalModeTrait {

  protected DrupalMode $drupalMode;

  public function getDrupalMode(): DrupalMode {
    if (!isset($this->drupalMode)) {
      $this->drupalMode = new DrupalMode();
    }

    return $this->drupalMode;
  }

  public function setDrupalMode(DrupalMode $mode): void {
    $this->drupalMode = $mode;
  }

}
