<?php

namespace AKlump\Drupal\BatchFramework;

interface QueueWorkerInterface {

  const ITEMS = 'queue_worker_items';

  /**
   * @return void
   *
   * @throws \AKlump\Drupal\BatchFramework\QueueWorkerTimeoutException If the
   * timeout is reached before the operation has reported being finished.
   */
  public function __invoke($queue_item): void;

  /**
   * @param int $timeout
   *
   * @return self
   *
   * @see \AKlump\Drupal\BatchFramework\Operator::handleOperation
   */
  public function setTimeout(int $timeout): self;

  public function setLoggerChannel(string $channel): self;

}
