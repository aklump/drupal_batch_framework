<?php

namespace AKlump\Drupal\BatchFramework\Helpers;

use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalLoggerAdapter;
use AKlump\Drupal\BatchFramework\DrupalMode;
use Drupal;
use Psr\Log\LoggerInterface;

/**
 * Get the correct logger by drupal mode.
 */
final class GetLogger {

  private DrupalMode $mode;

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
