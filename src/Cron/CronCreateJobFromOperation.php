<?php

namespace AKlump\Drupal\BatchFramework\Cron;

use AKlump\Drupal\BatchFramework\Queue\QueueDefinitionInterface;
use AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;

class CronCreateJobFromOperation implements CronJobInterface {

  use HasDrupalModeTrait;

  private int $time = 30;

  /**
   * @var \AKlump\Drupal\BatchFramework\Queue\QueueDefinitionInterface
   */
  private QueueDefinitionInterface $definition;

  public function __construct(QueueDefinitionInterface $queue_definition) {
    $this->definition = $queue_definition;
  }

  public function setMaxTime(int $time): CronJobInterface {
    $this->time = $time;

    return $this;
  }

  public function getMaxTime(): int {
    return $this->time;
  }

  public function do(): void {
    // TODO: Implement do() method.
  }

}
