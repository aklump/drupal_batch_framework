<?php

namespace AKlump\Drupal\BatchFramework\Throttle;

interface RateLimitInterface {

  public function getItemsPerInterval(): int;

  public function getInterval(): \DateInterval;

  public function setInterval(\DateInterval $interval): self;

  public function setItemsPerInterval(int $count): self;

}
