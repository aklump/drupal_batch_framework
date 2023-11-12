<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit;

use AKlump\Drupal\BatchFramework\OperationInterface;
use Exception;

class GetExceptionData {

  public function __invoke(OperationInterface $op, Exception $exception): array {
    return [
      'op_class' => get_class($op),
      'op' => $op->getLabel(),
      'message' => $exception->getMessage(),
      'exception_class' => get_class($exception),
      'exception_code' => $exception->getCode(),
      'exception_trace' => $exception->getTraceAsString(),
    ];
  }
}
