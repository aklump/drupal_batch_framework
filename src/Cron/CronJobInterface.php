<?php

namespace AKlump\Drupal\BatchFramework\Cron;

interface CronJobInterface {

  /**
   * @param int $time
   *
   * @return self
   */
  public function setMaxTime(int $time): self;

  /**
   * @return int
   *   The maximum seconds to spend during a single cron run on this job.
   */

  public function getMaxTime(): int;

  public function do(): void;

}
