<?php

namespace AKlump\Drupal\BatchFramework;

use Drupal;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

final class Operator {

  /**
   * Handle a single batch operation.
   *
   * @code
   * @endcode
   *
   * @param \AKlump\Drupal\BatchFramework\OperationInterface $op
   * @param int $timeout
   *   The total seconds not to exceed.  The operation will be processed until
   *   $batch_context['finished'] === 1 or the $timeout has been met.
   *   When this is being used by the Batch API, this becomes the UI refresh
   *   rate, so you may want to set this lower, e.g. 3.  Set this to zero and
   *   \AKlump\Drupal\BatchFramework\OperationInterface::process will only be
   *   called once.  For Queue operations you should set it higher, e.g. 60 to
   *   prevent items getting stuck in the queue simply because the operation
   *   didn't have enough time to finish.
   * @param \Psr\Log\LoggerInterface|NULL $logger
   *   Used for developer messages to be written to backend logs.  Not for the
   *   public user.
   * @param \AKlump\Drupal\BatchFramework\MessengerInterface|NULL $messenger
   *   Used to pass messages to the public user when the UI allows for it.
   * @param array &$batch_context
   *
   * @return void
   */
  public static function handleOperation(
    OperationInterface $operation,
    int $timeout,
    string $logger_channel = NULL,
    ?MessengerInterface $messenger = NULL,
    // $batch_context must always remain the last argument.  @see
    // app/web/core/includes/batch.inc:295
    array &$batch_context = []
  ) {
    self::initializeBatchContext($batch_context);
    $batch_context['logger_channel'] = $logger_channel;
    $batch_context['messenger'] = $messenger;
    $operation->setBatchContext($batch_context);

    if ($operation->getBatchFailures()) {
      $batch_context['finished'] = 1;

      return;
    }
    $finish_called = FALSE;
    try {
      $unmet_dependencies = array_diff($operation->getDependencies(), $batch_context['results']['operations_finished']);
      if ($unmet_dependencies) {
        throw new UnmetDependencyException($operation, $unmet_dependencies);
      }

      if (!$operation->isInitialized()) {
        $operation->initialize();
      }
      $end = time() + $timeout;
      $processed = FALSE;
      do {
        // We do not want to process until we've checked our progress ratio
        // first.  This is why we skip this here.
        if (isset($progress)) {
          $processed = TRUE;
          $operation->process();
          $batch_context['message'] = $batch_context['results']['current_activity_message'] ?? '';
        }
        $progress = $operation->getProgressRatio();
        $batch_context['finished'] = $progress;
        if (floatval(1) === $progress) {
          $finish_called = TRUE;
          $operation->finish();
          $batch_context['results']['operations_finished'][] = get_class($operation);
        }
        $times_up = time() >= $end;
      } while ($progress < 1 && (!$processed || ($processed && !$times_up)));
    }
    catch (\Exception $exception) {
      self::setBatchHasFailed($operation, $batch_context, $exception);
      try {
        if (!$finish_called) {
          $operation->finish();
        }
      }
      catch (\Exception $exception) {
        self::setBatchHasFailed($operation, $batch_context, $exception);
      }
      $batch_context['finished'] = 1;
    }
  }

  private static function initializeBatchContext(array &$batch_context) {
    if (isset($batch_context['results']['start'])) {
      return;
    }
    $batch_context['results']['start'] = time();
    $batch_context['results']['operations_finished'] = [];
    $batch_context['results']['exceptions'] = [];
  }

  private static function setBatchHasFailed(OperationInterface $operation, array &$batch_context, \Exception $exception) {
    $batch_context['results']['exceptions'][] = [
      'op_class' => get_class($operation),
      'op' => $operation->getLabel(),
      'message' => $exception->getMessage(),
      'exception_class' => get_class($exception),
      'exception_code' => $exception->getCode(),
      'exception_trace' => $exception->getTraceAsString(),
    ];
  }

}
