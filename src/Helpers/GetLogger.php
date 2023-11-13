<?php

namespace AKlump\Drupal\BatchFramework\Helpers;

use AKlump\Drupal\BatchFramework\DrupalMode;
use \Drupal;
use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalLoggerAdapter;
use Psr\Log\LoggerInterface;

/**
 * Get the correct logger by drupal mode.
 */
class GetLogger {

  public function __construct(DrupalMode $mode) {
    $this->mode = $mode;
  }

  public function __invoke(string $channel): LoggerInterface {
    if ($this->mode->isModern()) {
      return Drupal::service('logger.factory')->get($channel);
    }

    return new LegacyDrupalLoggerAdapter($channel);
  }

}
