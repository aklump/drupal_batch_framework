<?php

namespace AKlump\Drupal\BatchFramework\Cron;

use AKlump\Drupal\BatchFramework\Adapters\DrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\Batch\OperationInterface;
use AKlump\Drupal\BatchFramework\Batch\Operator;
use AKlump\Drupal\BatchFramework\HasLoggerInterface;
use AKlump\Drupal\BatchFramework\Helpers\GetLogger;
use AKlump\Drupal\BatchFramework\Traits\CanHandleBatchResultExceptionsTrait;
use AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;
use Psr\Log\LoggerInterface;

class CronCreateJobFromOperation implements CronJobInterface, HasLoggerInterface {

  use HasDrupalModeTrait;
  use CanHandleBatchResultExceptionsTrait;

  private int $timeout = 30;

  /**
   * @var \AKlump\Drupal\BatchFramework\Batch\OperationInterface
   */
  private OperationInterface $op;

  public function __construct(OperationInterface $operation) {
    $this->op = $operation;
  }

  public function getLabel(): string {
    return 'Cron';
  }

  public function getLogger(): LoggerInterface {
    $channel = $this->getLoggerChannel();

    return (new GetLogger($this->getDrupalMode()))($channel);
  }

  public function setMaxTime(int $time): CronJobInterface {
    $this->timeout = $time;

    return $this;
  }

  public function getMaxTime(): int {
    return $this->timeout;
  }

  public function do(): void {
    $this->getLogger()->info('Job started');
    $batch_context = [];
    Operator::handleOperation(
      $this->op,
      $this->timeout,
      $this->getLoggerChannel(),
      new DrupalMessengerAdapter(),
      $batch_context
    );
    if (!empty($batch_context['results']['exceptions'])) {
      $this->handleBatchResultsExceptions($batch_context['results']);
    }
  }

}
