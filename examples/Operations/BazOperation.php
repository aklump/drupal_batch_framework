<?php

namespace AKlump\Drupal\BatchFramework\Operations;

class BazOperation implements \AKlump\Drupal\BatchFramework\OperationInterface {


  /**
   * @inheritDoc
   */
  public function setBatchContext(array &$batch_context) {
    // TODO: Implement setBatchContext() method.
  }

  /**
   * @inheritDoc
   */
  public function skipOnBatchFailure(): bool {
    // TODO: Implement skipOnBatchFailure() method.
  }

  /**
   * @inheritDoc
   */
  public function isInitialized(): bool {
    // TODO: Implement isInitialized() method.
  }

  /**
   * @inheritDoc
   */
  public function initialize(): void {
    // TODO: Implement initialize() method.
  }

  /**
   * @inheritDoc
   */
  public function getProgressRatio(): float {
    // TODO: Implement getProgressRatio() method.
  }

  /**
   * @inheritDoc
   */
  public function process(): string {
    // TODO: Implement process() method.
  }

  /**
   * @inheritDoc
   */
  public function finish(): void {
    // TODO: Implement finish() method.
  }
}
