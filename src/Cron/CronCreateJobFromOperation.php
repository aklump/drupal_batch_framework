<?php

namespace AKlump\Drupal\BatchFramework\Cron;

use AKlump\Drupal\BatchFramework\Adapters\DrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\Batch\OperationInterface;
use AKlump\Drupal\BatchFramework\Batch\Operator;
use AKlump\Drupal\BatchFramework\HasLoggerInterface;
use AKlump\Drupal\BatchFramework\Helpers\CreateLoggingChannel;
use AKlump\Drupal\BatchFramework\Helpers\GetLogger;
use AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;
use Psr\Log\LoggerInterface;

class CronCreateJobFromOperation implements CronJobInterface, HasLoggerInterface {

  use HasDrupalModeTrait;

  private int $timeout = 30;

  /**
   * @var \AKlump\Drupal\BatchFramework\Batch\OperationInterface
   */
  private OperationInterface $operation;

  private string $loggerChannelOp = '';

  public function __construct(OperationInterface $operation) {
    $this->operation = $operation;
  }

  public function getLogger(): LoggerInterface {
    $channel = $this->getLoggerChannel();

    return (new GetLogger($this->getDrupalMode()))($channel);
  }

  public function getLoggerChannel(): string {
    $op = $this->operation;
    if ($this->loggerChannelOp) {
      $op = $this->loggerChannelOp;
    }

    return (new CreateLoggingChannel())('Cron', $op);
  }

  public function setMaxTime(int $time): CronJobInterface {
    $this->timeout = $time;

    return $this;
  }

  public function getMaxTime(): int {
    return $this->timeout;
  }

  public function do(): void {
    $batch_context = [];
    Operator::handleOperation(
      $this->operation,
      $this->timeout,
      $this->getLoggerChannel(),
      new DrupalMessengerAdapter(),
      $batch_context
    );
    if (!empty($batch_context['results']['exceptions'])) {
      $this->handleBatchContextExceptions($batch_context);
    }
  }

  private function handleBatchContextExceptions(array $batch_context): void {
    foreach ($batch_context['results']['exceptions'] as $data) {
      $message = trim(($data['message'] ?? '') . "\n" . ($data['exception_trace'] ?? ''));
      if (!$message) {
        continue;
      }
      $this->loggerChannelOp = $data['op'];
      $this->getLogger()->error($message);
      $this->loggerChannelOp = '';
    }
  }

}
