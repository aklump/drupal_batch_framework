<?php

namespace AKlump\Drupal\BatchFramework\Helpers;

/**
 * Calculate the correct float based on total and remaining.
 */
class GetProgressRatio {

  /**
   * @param int $total
   * @param int|\Countable $remaining
   *
   * @return float
   */
  public function __invoke(int $total, $remaining): float {
    if (is_countable($remaining)) {
      $remaining = count($remaining);
    }
    $total = max($total, 1);
    $progress = $total ? 1 - $remaining / $total : 1;
    $progress = min($progress, 1);

    return $progress;
  }

}
