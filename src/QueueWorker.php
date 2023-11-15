<?php

namespace AKlump\Drupal\BatchFramework;

use AKlump\Drupal\BatchFramework\Helpers\CreateLoggingChannel;
use AKlump\Drupal\BatchFramework\Helpers\GetMessenger;
use AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;
use InvalidArgumentException;

class QueueWorker {

  use HasDrupalModeTrait;

  protected string $loggerChannel = '';

  /**
   * @see \AKlump\Drupal\BatchFramework\QueueDefinitionInterface::getWorker
   */
  public function __invoke($item): void {
    /** @var \AKlump\Drupal\BatchFramework\OperationInterface $operation */
    $operation = $item['operation'] ?? NULL;
    if (empty($operation)) {
      throw new InvalidArgumentException(sprintf('Cannot process this item due to missing $item[operation], which should be an instance of %s', OperationInterface::class));
    }
    unset($item['operation']);

    $messenger = (new GetMessenger($this->getDrupalMode()))();
    $batch_context = [];
    $batch_context['sandbox']['items'] = [$item];
    $logger_channel = (new CreateLoggingChannel())($this->loggerChannel, $operation);

    Operator::handleOperation(
      $operation,
      0,
      $logger_channel,
      $messenger,
      $batch_context,
    );

    if ($batch_context['results']['exceptions']) {
      $e = reset($batch_context['results']['exceptions']);
      throw new $e['exception_class']($e['message'], $e['exception_code']);
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

}
