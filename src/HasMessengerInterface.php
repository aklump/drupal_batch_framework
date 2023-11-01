<?php

namespace AKlump\Drupal\BatchFramework;

interface HasMessengerInterface {

  /**
   * @return \AKlump\Drupal\BatchFramework\MessengerInterface
   */
  public function getMessenger(): MessengerInterface;
}
