<?php

namespace AKlump\Drupal\BatchFramework\Cron;

use AKlump\Drupal\BatchFramework\Helpers\GetLogger;
use AKlump\Drupal\BatchFramework\Queue\QueueDefinitionInterface;
use AKlump\Drupal\BatchFramework\Queue\QueueWorkerTimeoutException;
use AKlump\Drupal\BatchFramework\Throttle\RateLimitThresholdException;
use AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;
use DrupalQueue;
use Exception;
use InvalidArgumentException;

abstract class CronQueueJob implements CronJobInterface {

  use HasDrupalModeTrait;

  public function do(QueueDefinitionInterface $queue_definition): void {
    $name = $queue_definition->getName();
    $queue = DrupalQueue::get($name);
    $queue->createQueue();
    if (!$queue->numberOfItems()) {
      return;
    }

    $callback = $queue_definition->getWorker();
    $end = time() + $this->getMaxTime();
    while (time() < $end && ($item = $queue->claimItem())) {
      try {
        call_user_func($callback, $item->data);
        $queue->deleteItem($item);
      }
      catch (RateLimitThresholdException $e) {
        $queue->releaseItem($item);
      }
      catch (QueueWorkerTimeoutException $e) {

      }
      catch (InvalidArgumentException $e) {
        $queue->releaseItem($item);
      }
      catch (Exception $e) {
        $channel = $queue_definition->getLoggerChannel();
        (new GetLogger($this->getDrupalMode()))($channel)->error($e->getMessage());
      }
    }
  }

}
