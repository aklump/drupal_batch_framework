<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit;

use AKlump\Drupal\BatchFramework\DrupalMode;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \AKlump\Drupal\BatchFramework\DrupalMode
 * @covers  \AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait
 */
class DrupalModeTest extends TestCase {

  public function testSetAndGetDrupalModeOnTraitClassWorks() {
    $mode = new DrupalMode();
    $foo = new Foo();
    $foo->setDrupalMode($mode);
    $this->assertSame($mode, $foo->getDrupalMode());
  }

  public function testSetThenToString() {
    $this->assertSame(DrupalMode::LEGACY, (string) (new DrupalMode(DrupalMode::LEGACY)));
    $this->assertSame(DrupalMode::MODERN, (string) (new DrupalMode(DrupalMode::MODERN)));
  }

  public function testIsModern() {
    $this->assertTrue((new DrupalMode(DrupalMode::MODERN))->isModern());
  }

  public function testAutoDetectsAsLegacy() {
    $mode = (new DrupalMode());
    $this->assertSame(DrupalMode::LEGACY, (string) $mode);
  }
}

class Foo {

  use \AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;
}
