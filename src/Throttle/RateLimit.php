<?php

namespace AKlump\Drupal\BatchFramework\Throttle;

class RateLimit implements RateLimitInterface {

  private int $count;

  private \DateInterval $interval;

  /**
   * @param int $items
   * @param string $duration
   *   Any valid constructor argument to \DateInterval.
   *
   * @throws \Exception
   */
  public function __construct(int $count, string $duration) {
    $this->setItemsPerInterval($count);
    $this->setInterval(new \DateInterval($duration));
  }

  public function getItemsPerInterval(): int {
    return $this->count;
  }

  public function getInterval(): \DateInterval {
    return $this->interval;
  }

  public function setInterval(\DateInterval $interval): RateLimitInterface {
    $this->interval = $interval;

    return $this;
  }

  public function setItemsPerInterval(int $count): RateLimitInterface {
    $this->count = $count;

    return $this;
  }

  public function __toString(): string {
    $hours = $this->interval->format('%h');
    if ($hours > 0) {
      if ($hours == 1) {
        $hours = "hour";
      }
      else {
        $hours = "$hours hours";
      }
    }
    $minutes = $this->interval->format('%i');
    if ($minutes > 0) {
      if ($minutes == 1) {
        $minutes = "minute";
      }
      else {
        $minutes = "$minutes minutes";
      }
    }
    $interval = implode(' ', array_filter([$hours, $minutes]));

    return sprintf('%d every %s', $this->getItemsPerInterval(), $interval);
  }

}
