<?php

namespace AKlump\Drupal\BatchFramework\Cron;

use AKlump\Drupal\BatchFramework\Batch\OperationInterface;
use AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;

abstract class CronOperationJob implements CronJobInterface {

  use HasDrupalModeTrait;

  public function do(OperationInterface $operation): void {
    // TODO Build out.
  }

}
