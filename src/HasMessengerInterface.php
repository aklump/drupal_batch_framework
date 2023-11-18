<?php

namespace AKlump\Drupal\BatchFramework;

use AKlump\Drupal\BatchFramework\Adapters\MessengerInterface;

interface HasMessengerInterface {

  /**
   * @return \AKlump\Drupal\BatchFramework\Adapters\MessengerInterface
   */
  public function getMessenger(): MessengerInterface;
}
