<?php

namespace AKlump\Drupal\BatchFramework;

/**
 * When a batch operation fails throw this exception:
 *
 * Batches with multiple operations should make use of the following to control
 * how they act when after a batch failed exception has been thrown.
 *
 * @see \AKlump\Drupal\BatchFramework\OperationInterface::skipOnBatchFailure
 */
class UnmetDependencyException extends \RuntimeException {

  public function __construct(OperationInterface $op, array $unmet_dependencies, $code = 0, $previous = NULL) {
    $message = sprintf("%s cannot be processed before these dependencies: %s", get_class($op), implode(', ', $unmet_dependencies));
    parent::__construct($message, $code, $previous);
  }

}
