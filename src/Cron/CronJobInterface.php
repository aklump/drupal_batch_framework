<?php

namespace AKlump\Drupal\BatchFramework\Cron;

interface CronJobInterface {

  /**
   * @return int
   *   The maximum seconds to spend during a single cron run on this job.
   */
  public function getMaxTime(): int;
}
