<?php

namespace AKlump\Drupal\BatchFramework\Throttle;

interface GateInterface {

  public function setRateLimit(RateLimitInterface $limit): self;

  public function isClosed(): bool;

  public function allowOneThrough(): void;

}
