<?php

namespace AKlump\Drupal\BatchFramework\Helpers;

use AKlump\Drupal\BatchFramework\Batch\OperationInterface;

class CreateLoggingChannel {

  /**
   * @param string $base_channel
   * @param string|\AKlump\Drupal\BatchFramework\Batch\OperationInterface $operation
   *
   * @return string
   */
  public function __invoke(string $base_channel, $operation) {
    if ($operation instanceof OperationInterface) {
      $operation = $operation->getLabel();
    }

    return implode(': ', array_filter([
      trim($base_channel),
      trim($operation),
    ]));
  }

}
