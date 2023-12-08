<?php

namespace AKlump\Drupal\BatchFramework\Traits;


use AKlump\Drupal\BatchFramework\Helpers\CreateLoggingChannel;

trait CanHandleBatchResultExceptionsTrait {

  protected string $loggerChannelOpLabel = '';

  /**
   * {@inheritdoc}
   */
  public function getLoggerChannel(): string {
    $op_label = $this->op ?? '';
    if ($this->loggerChannelOpLabel) {
      $op_label = $this->loggerChannelOpLabel;
    }

    return (new CreateLoggingChannel())($this->getLabel(), $op_label);
  }

  private function handleBatchResultsExceptions(array $batch_results): void {
    if (empty($batch_results['exceptions'])) {
      return;
    }
    foreach ($batch_results['exceptions'] as $data) {
      $message = trim(($data['message'] ?? '') . "\n" . ($data['exception_trace'] ?? ''));
      if (!$message) {
        continue;
      }
      $this->loggerChannelOpLabel = $data['op'] ?? '';
      $this->getLogger()->error($message);
    }
    $this->loggerChannelOpLabel = '';
  }

}
