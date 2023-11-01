<?php

namespace AKlump\Drupal\BatchFramework\Helpers;

/**
 * Calculate the correct float based on total and remaining.
 */
class GetProgressRatio {

  public function __invoke(int $total, int $remaining): float {
    $total = max($total, 1);
    $progress = $total ? 1 - $remaining / $total : 1;
    $progress = min($progress, 1);

    return $progress;
  }

}
