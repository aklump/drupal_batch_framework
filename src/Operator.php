<?php

namespace AKlump\Drupal\BatchFramework;

use Psr\Log\LoggerInterface;

/**
 * This class is used to run batches as cron jobs or otherwise programmatically.
 * It is not used when running a batch as a result of a form submission.
 */
class Operator {

  /**
   * Handle a single batch operation.
   *
   * @code
   * @endcode
   *
   * @param \AKlump\Drupal\BatchFramework\OperationInterface $op
   * @param array &$batch_context
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
    self::initializeBatchFailedContext($batch_context);
    $batch_context['logger'] = $logger;
    $batch_context['messenger'] = $messenger;
    $op->setBatchContext($batch_context);
    if (self::hasBatchFailed($batch_context) && $op->skipOnBatchFailure()) {
      $batch_context['finished'] = 1;

      return;
    }
    try {
      if (!$op->isInitialized()) {
        $op->initialize();
      }
      $end = time() + $max_execution;
      do {
        // We do not want to process until we've checked our progress ratio
        // first.  This is why we skip this here.
        if (isset($progress)) {
          $batch_context['message'] = $op->process();
        }
        $batch_context['finished'] = $progress = $op->getProgressRatio();
        if (floatval(1) === $progress) {
          $op->finish();
        }
      } while (time() < $end && $batch_context['finished'] < 1);
    }
    catch (BatchFailedException $exception) {
      self::setBatchHasFailed($batch_context, $exception);
      try {
        $op->finish();
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
    $batch_context['results']['batch_failed_exceptions'] = [];
  }

  public static function setBatchHasFailed(array &$batch_context, BatchFailedException $exception) {
    $batch_context['results']['batch_failed'] = TRUE;
    $batch_context['results']['batch_failed_exceptions'][] = $exception;
    watchdog_exception('batch', $exception);
  }

  /**
   * Detect if a batch has failed.
   *
   * @param array $batch_context
   *
   * @return bool
   */
  public static function hasBatchFailed(array $batch_context): bool {
    return isset($batch_context['results']['batch_failed']) && TRUE === $batch_context['results']['batch_failed'];
  }

  public static function getBatchFailedException(array $batch_context): ?BatchFailedException {
    return $batch_context['results']['batch_failed_exceptions'] ?? [];
  }

}
