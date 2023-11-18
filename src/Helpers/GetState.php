<?php

namespace AKlump\Drupal\BatchFramework\Helpers;

use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalStateAdapter;
use AKlump\Drupal\BatchFramework\Adapters\StateInterface;
use AKlump\Drupal\BatchFramework\DrupalMode;

/**
 * Get the correct state handler by drupal mode.
 */
final class GetState {

  private DrupalMode $mode;

  public function __construct(DrupalMode $mode) {
    $this->mode = $mode;
  }

  public function __invoke(): StateInterface {
    if ($this->mode->isModern()) {
      // TODO Flesh out.
      throw new \RuntimeException('Not yet supported');
    }

    return new LegacyDrupalStateAdapter();
  }

}
