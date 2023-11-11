<?php

namespace AKlump\Drupal\BatchFramework;

use \Drupal;

class DrupalMode {

  const LEGACY = 'legacy';

  /**
   * Modern drupal starts at version 8.
   */
  const MODERN = 'modern';

  protected string $mode;

  /**
   * Determine if you're in modern drupal.
   *
   * @return bool
   *   TRUE if version >=8
   */
  public function isModern(): bool {
    return $this->get() === self::MODERN;
  }


  /**
   * @param string $mode
   *
   * @return \AKlump\Drupal\BatchFramework\DrupalMode
   *
   * @see self::LEGACY
   * @see self::MODERN
   */
  public function set(string $mode): self {
    if (!in_array($mode, [
      self::LEGACY,
      self::MODERN,
    ])) {
      throw new \InvalidArgumentException(sprintf('Invalid mode: %s', $mode));
    }
    $this->mode = $mode;

    return $this;
  }

  public function get(): string {
    if (!isset($this->mode)) {
      $drupal_version = 7;
      if (class_exists(Drupal::class)) {
        $drupal_version++;
      }
      if (version_compare($drupal_version, '8') >= 0) {
        $this->mode = self::MODERN;
      }
      else {
        $this->mode = self::LEGACY;
      }
    }

    return $this->mode;
  }

}
