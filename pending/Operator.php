<?php

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Psr\Log\LoggerInterface;

class Operator {

  public static function handleOperation(
    string $operation_class,
    LoggerInterface $logger_channel,
    MessengerInterface $messenger,
    int $max_execution,
    array &$batch_context
  ) {
    self::initializeBatchFailedContext($batch_context);

    // By using the classname and not an instance, we avoid issues with
    // serializing the OperationInterface instance.
    $operation = new $operation_class();
    if ($operation instanceof ContainerInjectionInterface) {
      $operation = call_user_func([
        $operation_class,
        'create',
      ], \Drupal::getContainer());
    }

    $operation->setLogger($logger_channel);
    $operation->setMessenger($messenger);
    $operation->setBatchContext($batch_context);
    if (self::hasBatchFailed($batch_context) && $operation->skipOnBatchFailure()) {
      $batch_context['finished'] = 1;

      return;
    }
    try {
      if (!$operation->isInitialized()) {
        $operation->initialize();
      }
      $end = time() + $max_execution;
      do {
        // We do not want to process until we've checked our progress ratio
        // first.  This is why we skip this here.
        if (isset($progress)) {
          $batch_context['message'] = $operation->process();
        }
        $batch_context['finished'] = $progress = $operation->getProgressRatio();
        if (floatval(1) === $progress) {
          $operation->finish();
        }
      } while (time() < $end && $batch_context['finished'] < 1);
    }
    catch (BatchFailedException $exception) {
      self::setBatchHasFailed($batch_context, $exception);
      try {
        $operation->finish();
      }
      catch (BatchFailedException $exception) {
        self::setBatchHasFailed($batch_context, $exception);
      }
      $batch_context['finished'] = 1;
    }
  }

  protected static function initializeBatchFailedContext(array &$batch_context) {
    if (isset($batch_context['results']['batch_failed'])) {
      return;
    }
    $batch_context['results']['batch_failed'] = FALSE;
    $batch_context['results']['batch_failed_exception'] = NULL;
  }

  public static function setBatchHasFailed(array &$batch_context, BatchFailedException $exception) {
    $batch_context['results']['batch_failed'] = TRUE;
    $batch_context['results']['batch_failed_exception'] = $exception;
    watchdog_exception('batch', $exception);
  }

  /**
   * Detect if a batch has failed.
   *
   * @param array $batch_context
   *
   * @return bool
   */
  public static function hasBatchFailed(array $batch_context) {
    return isset($batch_context['results']['batch_failed']) && TRUE === $batch_context['results']['batch_failed'];
  }

  /**
   * Detect if a batch has failed.
   *
   * @param array $batch_context
   *
   * @return bool
   */
  public static function getBatchFailedException(array $batch_context): ?BatchFailedException {
    return $batch_context['results']['batch_failed_exception'] ?? NULL;
  }

}
