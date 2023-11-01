<?php

namespace AKlump\Drupal\BatchFramework\Helpers;

use AKlump\Drupal\BatchFramework\MessengerInterface;

/**
 * Adapts \Drupal::messenger() to implement \AKlump\Drupal\BatchFramework\MessengerInterface
 */
class DrupalMessengerAdapter implements MessengerInterface {

  public function addMessage(string $message, $type = self::TYPE_STATUS, $repeat = FALSE) {
    if (empty($this->drupalMessenger)) {
      $this->drupalMessenger = \Drupal::messenger();
    }
    $args = func_get_args();

    return call_user_func([$this->drupalMessenger, 'addMessage'], ...$args);
  }

}
