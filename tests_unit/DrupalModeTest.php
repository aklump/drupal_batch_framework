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

  public function testSetThenGet() {
    $this->assertSame(DrupalMode::LEGACY, (new DrupalMode())->set(DrupalMode::LEGACY)
      ->get());
    $this->assertSame(DrupalMode::MODERN, (new DrupalMode())->set(DrupalMode::MODERN)
      ->get());
  }

  public function testIsModern() {
    $this->assertTrue((new DrupalMode())->set(DrupalMode::MODERN)->isModern());
  }

  public function testGetReturnsLegacy() {
    $mode = (new DrupalMode())->get();
    $this->assertSame(DrupalMode::LEGACY, $mode);
  }
}

class Foo {

  use \AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;
}
