<?php

namespace AKlump\Drupal\BatchFramework;

class Operator {

  /**
   * Handle a single batch operation.
   *
   * @code
   * @endcode
   *
   * @param callable $callback
   * @param array $callback_args
   * @param array &$batch_context
   * @param int $max_execution
   *   The total seconds not to exceed.  The operation will be processed until
   *   $batch_context['finished'] === 1 or the $max_execution has been met.
   *   When this is being used by the Batch API, this because the UI refresh
   *   rate, so you may want to set this lower, e.g. 3.  However when using this
   *   with the Cron queue, it can be set much higher.
   *
   * @return void
   *
   * @throws \Drupal\ovagraph_core\Batch\BatchFailedException
   *   When the operation has not completed successfully.
   */
  public static function handleOperation($callback, array $callback_args, array &$batch_context, int $max_execution = 3) {
    self::initializeBatchFailedContext($batch_context);
    $op = call_user_func_array($callback, array_merge($callback_args, array(&$batch_context)));
    if ($op instanceof \Drupal\ovagraph_core\Batch\OperationInterface) {
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
      catch (\Drupal\ovagraph_core\Batch\BatchFailedException $exception) {
        self::setBatchHasFailed($batch_context, $exception);
        try {
          $op->finish();
        }
        catch (\Drupal\ovagraph_core\Batch\BatchFailedException $exception) {
          self::setBatchHasFailed($batch_context, $exception);
        }
        $batch_context['finished'] = 1;
      }
    }
  }

  protected static function initializeBatchFailedContext(array &$batch_context) {
    if (isset($batch_context['results']['batch_failed'])) {
      return;
    }
    $batch_context['results']['batch_failed'] = FALSE;
    $batch_context['results']['batch_failed_exception'] = NULL;
  }

  public static function setBatchHasFailed(array &$batch_context, \Drupal\ovagraph_core\Batch\BatchFailedException $exception) {
    $batch_context['results']['batch_failed'] = TRUE;
    $batch_context['results']['batch_failed_exception'] = $exception;
    \Drupal\ovagraph_core\Batch\watchdog_exception('batch', $exception);
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
  public static function getBatchFailedException(array $batch_context): ?\Drupal\ovagraph_core\Batch\BatchFailedException {
    return $batch_context['results']['batch_failed_exception'] ?? NULL;
  }

}
