<?php

namespace AKlump\Drupal\BatchFramework\Cron;

use AKlump\Drupal\BatchFramework\Batch\OperationInterface;
use AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;

abstract class CronOperationJob implements CronJobInterface {

  use HasDrupalModeTrait;

  private int $time = 30;

  public function do(OperationInterface $operation): void {
    // TODO Build out.
  }

  public function setMaxTime(int $time): CronJobInterface {
    $this->time = $time;

    return $this;
  }

  public function getMaxTime(): int {
    return $this->time;
  }

}
