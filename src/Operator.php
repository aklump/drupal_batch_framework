<?php

namespace AKlump\Drupal\BatchFramework;

use Psr\Log\LoggerInterface;

final class Operator {

  /**
   * Handle a single batch operation.
   *
   * @code
   * @endcode
   *
   * @param \AKlump\Drupal\BatchFramework\OperationInterface $op
   * @param int $max_execution
   *   The total seconds not to exceed.  The operation will be processed until
   *   $batch_context['finished'] === 1 or the $max_execution has been met.
   *   When this is being used by the Batch API, this becomes the UI refresh
   *   rate, so you may want to set this lower, e.g. 3.  However when using this
   *   with the Cron queue, it can be set much higher.
   * @param \Psr\Log\LoggerInterface|NULL $logger
   *   Used for developer messages to be written to backend logs.  Not for the
   *   public user.
   * @param \AKlump\Drupal\BatchFramework\MessengerInterface|NULL $messenger
   *   Used to pass messages to the public user when the UI allows for it.
   * @param array &$batch_context
   *
   * @return void
   *
   * @throws \AKlump\Drupal\BatchFramework\BatchFailedException
   *   When the operation has not completed successfully.
   */
  public static function handleOperation(
    OperationInterface $op,
    int $max_execution = 30,
    ?LoggerInterface $logger = NULL,
    ?MessengerInterface $messenger = NULL,
    // $batch_context must always remain the last argument.  @see
    // app/web/core/includes/batch.inc:295
    array &$batch_context = []
  ) {
    self::initializeBatchContext($batch_context);
    $batch_context['logger'] = $logger;
    $batch_context['messenger'] = $messenger;
    $op->setBatchContext($batch_context);

    // Handle failures and skipping.
    if ($op->getBatchFailures() && $op->skipOnBatchFailure()) {
      $batch_context['finished'] = 1;

      return;
    }

    try {
      $unmet_dependencies = array_diff($op->getDependencies(), $batch_context['results']['operations_finished']);
      if ($unmet_dependencies) {
        throw new UnmetDependencyException($op, $unmet_dependencies);
      }

      if (!$op->isInitialized()) {
        $op->initialize();
      }
      $end = time() + $max_execution;
      do {
        // We do not want to process until we've checked our progress ratio
        // first.  This is why we skip this here.
        if (isset($progress)) {
          $op->process();
          $batch_context['message'] = $batch_context['results']['current_activity_message'] ?? '';
        }
        $batch_context['finished'] = $progress = $op->getProgressRatio();
        if (floatval(1) === $progress) {
          $op->finish();
          $batch_context['results']['operations_finished'][] = get_class($op);
        }
      } while (time() < $end && $batch_context['finished'] < 1);
    }
    catch (\Exception $exception) {
      self::setBatchHasFailed($op, $batch_context, $exception);
      try {
        $op->finish();
      }
      catch (\Exception $exception) {
        self::setBatchHasFailed($op, $batch_context, $exception);
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

  private static function setBatchHasFailed(OperationInterface $op, array &$batch_context, \Exception $exception) {
    $batch_context['results']['exceptions'][] = [
      'op_class' => get_class($op),
      'op' => $op->getLabel(),
      'message' => $exception->getMessage(),
      'exception_class' => get_class($exception),
      'exception_code' => $exception->getCode(),
      'exception_trace' => $exception->getTraceAsString(),
    ];
  }

}
