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
class BatchFailedException extends \RuntimeException {

}
