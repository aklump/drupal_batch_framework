<?php

namespace AKlump\Drupal\BatchFramework\Throttle;

use AKlump\Drupal\BatchFramework\Adapters\StateInterface;
use AKlump\Drupal\BatchFramework\DrupalMode;
use AKlump\Drupal\BatchFramework\Helpers\GetState;

class DrupalGate implements GateInterface {

  protected string $id;

  private RateLimitInterface $rateLimit;

  /**
   * @var \AKlump\Drupal\BatchFramework\Adapters\StateInterface
   */
  private StateInterface $state;

  /**
   * @param string $id
   *   A unique ID for this gate, it will be use to track state.
   * @param \AKlump\Drupal\BatchFramework\Throttle\RateLimitInterface $rate_limit
   *   The rules for traffic through the gate.
   * @param \AKlump\Drupal\BatchFramework\Adapters\StateInterface $state
   *   Mechanism used to persist the throughput, limits, is closed, etc.
   */
  public function __construct(string $id, RateLimitInterface $rate_limit, StateInterface $state = NULL) {
    $this->id = "gate_$id";
    $this->setRateLimit($rate_limit);
    $this->state = $state ?? (new GetState(new DrupalMode()))();
  }

  public function setRateLimit(RateLimitInterface $rateLimit): self {
    $this->rateLimit = $rateLimit;

    return $this;
  }

  /**
   * @return bool
   *   True if the gate is shut.
   */
  public function isClosed(): bool {
    if ($this->isStopped()) {
      return TRUE;
    }

    return !$this->isWithinLimits();
  }

  /**
   * @return bool
   *   True if the limit has been met and the interval is being waiting out.
   */
  private function isStopped(): bool {
    $resume = (int) $this->state->get($this->id . ':resume');
    if (!$resume) {
      return FALSE;
    }

    if (time() < $resume) {
      return TRUE;
    }
    $this->setResumeTimestamp(0);

    return FALSE;
  }

  private function isWithinLimits(): bool {
    $rate_progress = $this->getIntervalProgressCount();
    $reached = $rate_progress === $this->rateLimit->getItemsPerInterval();
    if ($reached) {
      $resume = date_create()
        ->add($this->rateLimit->getInterval())
        ->format('U');
      $this->setResumeTimestamp($resume);
      $this->setIntervalProgressCount(0);
    }

    return !$reached;
  }

  /**
   * Call this to indicate an item has passed the gate.
   *
   * This will count against the rate limit.
   *
   * @return void
   */
  public function allowOneThrough(): void {
    $progress = $this->getIntervalProgressCount() + 1;
    $this->setIntervalProgressCount($progress);
  }

  private function setResumeTimestamp(int $timestamp) {
    $this->state->set($this->id . ':resume', $timestamp);
  }

  private function setIntervalProgressCount(int $count) {
    $this->state->set($this->id . ':progress', $count);
  }

  private function getIntervalProgressCount(): int {
    return (int) $this->state->get($this->id . ':progress');
  }

}
