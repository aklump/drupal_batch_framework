<?php

namespace AKlump\Drupal\BatchFramework\Queue;

use AKlump\Drupal\BatchFramework\Throttle\GateInterface;

interface QueueWorkerInterface {

  const ITEMS = 'queue_worker_items';

  /**
   * @return void
   *
   * @throws \AKlump\Drupal\BatchFramework\Queue\QueueWorkerTimeoutException If the
   * timeout is reached before the operation has reported being finished.
   */
  public function __invoke($queue_item): void;

  /**
   * @param int $timeout
   *
   * @return self
   *
   * @see \AKlump\Drupal\BatchFramework\Batch\Operator::handleOperation
   */
  public function setTimeout(int $timeout): self;

  public function setLoggerChannel(string $channel): self;

  /**
   * Do not allow this worker to operate any faster than the rate limit.
   *
   * @param \AKlump\Drupal\BatchFramework\Throttle\GateInterface
   *
   * @return self
   */
  public function setRateLimitGate(GateInterface $gate): self;

}
