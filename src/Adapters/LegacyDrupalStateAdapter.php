<?php

namespace AKlump\Drupal\BatchFramework\Adapters;


class LegacyDrupalStateAdapter implements StateInterface {

  public function set($key, $value) {
    variable_set($key, $value);
  }

  public function get($key) {
    return variable_get($key);
  }

}
