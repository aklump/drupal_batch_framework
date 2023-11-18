<?php

namespace AKlump\Drupal\BatchFramework;

interface QueueDefinitionInterface {

  /**
   * @return string
   *   The name to be used by the Drupal Queue API.
   */
  public function getName(): string;

  /**
   * @return string
   *   The channel to use when logging related to this queue.
   */
  public function getLoggerChannel(): string;

  /**
   * @return callable
   *   Receives a queue item for processing
   * @throws \Exception If the item should remain in the queue.
   *
   * @see \AKlump\Drupal\BatchFramework\QueueWorker
   */
  public function getWorker(): callable;

  /**
   * @return string
   *   If this queue has an management URL return it here otherwise return ''.
   */
  public function getAdminUrl(): string;

}
