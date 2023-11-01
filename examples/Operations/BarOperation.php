<?php

namespace AKlump\Drupal\BatchFramework\Operations;

class BarOperation implements \AKlump\Drupal\BatchFramework\OperationInterface {

  use \AKlump\Drupal\BatchFramework\Traits\GetIdByClassnameTrait;

  public function __construct(\DateTimeInterface $date, \Drupal\Core\Session\AccountInterface $account) {
    $this->date = $date;
    $this->account = $account;
  }

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
