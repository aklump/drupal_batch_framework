<?php

namespace AKlump\Drupal\BatchFramework\Helpers;

use AKlump\Drupal\BatchFramework\Adapters\DrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\Adapters\MessengerInterface;
use AKlump\Drupal\BatchFramework\DrupalMode;
use Drupal;

/**
 * Get the correct logger by drupal mode.
 */
final class GetMessenger {

  private DrupalMode $mode;

  public function __construct(DrupalMode $mode) {
    $this->mode = $mode;
  }

  public function __invoke(): MessengerInterface {
    if ($this->mode->isModern()) {
      return new DrupalMessengerAdapter();
    }

    return new LegacyDrupalMessengerAdapter();
  }

}
