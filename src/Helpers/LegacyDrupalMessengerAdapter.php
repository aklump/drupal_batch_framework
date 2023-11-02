<?php

namespace AKlump\Drupal\BatchFramework\Helpers;

use AKlump\Drupal\BatchFramework\MessengerInterface;

/**
 * Adapts drupal_set_message to implement \AKlump\Drupal\BatchFramework\MessengerInterface
 */
class LegacyDrupalMessengerAdapter implements MessengerInterface {

  public function addMessage(string $message, $type = self::TYPE_STATUS, $repeat = FALSE) {
    return drupal_set_message($message, $type, $repeat);
  }

}
