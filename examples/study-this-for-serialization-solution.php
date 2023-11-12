<?php

namespace AKlump\Drupal\BatchFramework;

class Operator {

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
      catch (\Exception $exception) {
        self::setBatchHasFailed($batch_context, $exception);
        try {
          $op->finish();
        }
        catch (\Exception $exception) {
          self::setBatchHasFailed($batch_context, $exception);
        }
        $batch_context['finished'] = 1;
      }
    }
  }


}
