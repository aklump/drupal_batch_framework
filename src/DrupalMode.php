<?php

namespace AKlump\Drupal\BatchFramework;

use Drupal\Core\Composer\Composer;

class DrupalMode {

  const LEGACY = 'legacy';

  /**
   * Modern drupal starts at version 8.
   */
  const MODERN = 'modern';

  protected string $mode;

  /**
   * @param string $mode
   *   Omit to auto-detect.
   *
   * @see self::MODERN
   * @see self::LEGACY
   */
  public function __construct(string $mode = NULL) {
    if ($mode) {
      $this->set($mode);
    }
  }

  /**
   * Determine if you're in modern drupal.
   *
   * @return bool
   *   TRUE if version >=8
   */
  public function isModern(): bool {
    return (string) $this === self::MODERN;
  }


  /**
   * @param string $mode
   *
   * @return \AKlump\Drupal\BatchFramework\DrupalMode
   *
   * @see self::LEGACY
   * @see self::MODERN
   */
  private function set(string $mode): self {
    if (!in_array($mode, [
      self::LEGACY,
      self::MODERN,
    ])) {
      throw new \InvalidArgumentException(sprintf('Invalid mode: %s', $mode));
    }
    $this->mode = $mode;

    return $this;
  }

  public function __toString(): string {
    if (!isset($this->mode)) {
      $drupal_version = 7;
      // This is a pretty obscure class buried in drupal core, so we'll detect
      // on it, rather than say \Drupal, since I use that in some polyfills in
      // Drupal 7.
      if (class_exists(Composer::class)) {
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
