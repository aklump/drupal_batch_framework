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

}
