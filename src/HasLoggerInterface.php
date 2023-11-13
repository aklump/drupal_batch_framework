<?php

namespace AKlump\Drupal\BatchFramework;

use Psr\Log\LoggerInterface;

interface HasLoggerInterface {

  /**
   * @return \Psr\Log\LoggerInterface
   */
  public function getLogger(): LoggerInterface;

  /**
   * @return string
   *   The channel to be logging to.
   */
  public function getLoggerChannel(): string;
}
