<?php

namespace AKlump\Drupal\BatchFramework\BatchDefinitions;

final class FooBatch extends \AKlump\Drupal\BatchFramework\BatchDefinitionBase {

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  private \Drupal\Core\Session\AccountInterface $account;

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   */
  public function __construct(\Drupal\Core\Session\AccountInterface $account) {
    $this->account = $account;
  }

  /**
   * @inheritDoc
   */
  public function getOperations(): array {
    $operations = [
      new \AKlump\Drupal\BatchFramework\Operations\BazOperation(),

      // This operation takes a couple of arguments, unlike the first.  One is
      // calculated at runtime and the other is a property of the batch.
      new \AKlump\Drupal\BatchFramework\Operations\BarOperation(date_create('now'), $this->account),
    ];

    return $operations;
  }

}
