<?php

namespace AKlump\Drupal\BatchFramework;

use AKlump\Drupal\BatchFramework\Helpers\CreateLoggingChannel;
use AKlump\Drupal\BatchFramework\Helpers\GetMessenger;
use AKlump\Drupal\BatchFramework\Throttle\GateInterface;
use AKlump\Drupal\BatchFramework\Throttle\RateLimitThresholdException;
use AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;
use InvalidArgumentException;

class QueueWorker implements QueueWorkerInterface {

  use HasDrupalModeTrait;

  /**
   * @var int Time in seconds before the queue worker will timeout.  If this is
   * reached and the operation has not finished, the item remains in the queue.
   * It is up to the operation to track such an event.
   */
  protected int $timeout = 60;

  /**
   * @var \AKlump\Drupal\BatchFramework\Throttle\GateInterface
   */
  protected GateInterface $gate;

  public function setTimeout(int $timeout): self {
    $this->timeout = $timeout;

    return $this;
  }

  protected string $loggerChannel = '';

  /**
   * @see \AKlump\Drupal\BatchFramework\QueueDefinitionInterface::getWorker
   */
  public function __invoke($queue_item): void {
    if (isset($this->gate) && $this->gate->isClosed()) {
      throw new RateLimitThresholdException();
    }

    /** @var \AKlump\Drupal\BatchFramework\OperationInterface $operation */
    $operation = $queue_item[QueueItemInterface::OPERATION] ?? NULL;
    if (empty($operation)) {
      throw new InvalidArgumentException(sprintf('Cannot process this item due to missing $item["%s"], which should be an instance of %s.', QueueItemInterface::OPERATION, OperationInterface::class));
    }
    unset($queue_item[QueueItemInterface::OPERATION]);

    $messenger = (new GetMessenger($this->getDrupalMode()))();
    $batch_context = [];
    $batch_context['results'][QueueWorkerInterface::ITEMS] = [$queue_item];
    $logger_channel = (new CreateLoggingChannel())($this->loggerChannel, $operation);

    Operator::handleOperation(
      $operation,
      $this->timeout,
      $logger_channel,
      $messenger,
      $batch_context,
    );

    // When a queue operation fails to report it has finished, then the item
    // should logically remain in the queue.  This should be due to when the
    // timeout is reached.
    if ($batch_context['finished'] < 1) {
      $message = sprintf('%s did not finished before timeout reached; item should remain in queue.', $operation->getLabel());
      throw new QueueWorkerTimeoutException($message);
    }

    if ($batch_context['results']['exceptions']) {
      $e = reset($batch_context['results']['exceptions']);
      throw new $e['exception_class']($e['message'], $e['exception_code']);
    }

    if (isset($this->gate)) {
      $this->gate->allowOneThrough();
    }
  }

  /**
   * @param string $channel
   *   The channel to use when logging for this worker and the operations it
   *   works on.  The operation label will automatically be appended to the
   *   value passed here and therefore should not be a part of $channel.
   *
   * @return $this
   *
   * @see \AKlump\Drupal\BatchFramework\QueueDefinitionInterface::getLoggerChannel()
   */
  public function setLoggerChannel(string $channel): self {
    $this->loggerChannel = $channel;

    return $this;
  }

  public function setRateLimitGate(GateInterface $gate): self {
    $this->gate = $gate;

    return $this;
  }

}
