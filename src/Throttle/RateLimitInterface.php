<?php

namespace AKlump\Drupal\BatchFramework\Throttle;

interface RateLimitInterface {

  public function getItemsPerInterval(): int;

  public function getInterval(): \DateInterval;

  public function setInterval(\DateInterval $interval): self;

  public function setItemsPerInterval(int $count): self;

  /**
   * @return string
   *   A human readable string representing the rate, e.g. "1 every 5 minutes".
   */
  public function __toString(): string;

}
