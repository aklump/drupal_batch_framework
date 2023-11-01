<?php

namespace AKlump\Drupal\BatchFramework;

use Psr\Log\LoggerInterface;

interface HasLoggerInterface {

  /**
   * @return \Psr\Log\LoggerInterface
   */
  public function getLogger(): LoggerInterface;
}
