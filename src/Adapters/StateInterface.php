<?php

namespace AKlump\Drupal\BatchFramework\Adapters;

interface StateInterface {

  public function set($key, $value);

  public function get($key);

}
