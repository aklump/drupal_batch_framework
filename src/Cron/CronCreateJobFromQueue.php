<?php

namespace AKlump\Drupal\BatchFramework\Cron;

use AKlump\Drupal\BatchFramework\Helpers\CreateLoggingChannel;
use AKlump\Drupal\BatchFramework\Helpers\GetLogger;
use AKlump\Drupal\BatchFramework\Queue\QueueDefinitionInterface;
use AKlump\Drupal\BatchFramework\Queue\QueueWorkerTimeoutException;
use AKlump\Drupal\BatchFramework\Throttle\RateLimitThresholdException;
use AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;
use DrupalQueue;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class CronCreateJobFromQueue
  implements CronJobInterface {

  use HasDrupalModeTrait;

  private int $time = 30;

  /**
   * @var \AKlump\Drupal\BatchFramework\Queue\QueueDefinitionInterface
   */
  private QueueDefinitionInterface $definition;

  public function __construct(QueueDefinitionInterface $queue_definition) {
    $this->definition = $queue_definition;
  }

  public function do(): void {
    if ($this->time === 0) {
      return;
    }
    $this->getLogger()->info('Job started');
    $name = $this->definition->getName();
    $queue = DrupalQueue::get($name);
    $queue->createQueue();
    if (!$queue->numberOfItems()) {
      return;
    }

    $callback = $this->definition->getWorker();
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
        $this->getLogger()->error($e->getMessage());
      }
    }
  }

  public function setMaxTime(int $time): CronJobInterface {
    $this->time = $time;

    return $this;
  }

  public function getMaxTime(): int {
    return $this->time;
  }

  public function getLogger(): LoggerInterface {
    $channel = (new CreateLoggingChannel())('Cron', $this->definition->getLoggerChannel());

    return (new GetLogger($this->getDrupalMode()))($channel);
  }

}
