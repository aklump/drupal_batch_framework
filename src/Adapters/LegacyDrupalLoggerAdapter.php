<?php

namespace AKlump\Drupal\BatchFramework\Adapters;

use Psr\Log\LoggerInterface;
use function watchdog;

class LegacyDrupalLoggerAdapter implements LoggerInterface {

  protected string $channel;

  /**
   * @param string $channel
   */
  public function __construct(string $channel) {
    $this->channel = $channel;
  }

  public function emergency($message, array $context = array()) {
    watchdog($this->channel, t($message, $context), $context, WATCHDOG_EMERGENCY);
  }

  public function alert($message, array $context = array()) {
    watchdog($this->channel, t($message, $context), $context, WATCHDOG_ALERT);
  }

  public function critical($message, array $context = array()) {
    watchdog($this->channel, t($message, $context), $context, WATCHDOG_CRITICAL);
  }

  public function error($message, array $context = array()) {
    watchdog($this->channel, t($message, $context), $context, WATCHDOG_ERROR);
  }

  public function warning($message, array $context = array()) {
    watchdog($this->channel, t($message, $context), $context, WATCHDOG_WARNING);
  }

  public function notice($message, array $context = array()) {
    watchdog($this->channel, t($message, $context), $context, WATCHDOG_NOTICE);
  }

  public function info($message, array $context = array()) {
    watchdog($this->channel, t($message, $context), $context, WATCHDOG_INFO);
  }

  public function debug($message, array $context = array()) {
    watchdog($this->channel, t($message, $context), $context, WATCHDOG_DEBUG);
  }

  public function log($level, $message, array $context = array()) {
    watchdog($this->channel, t($message, $context), $context);
  }

}
