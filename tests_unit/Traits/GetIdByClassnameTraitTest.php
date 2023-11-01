<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit\Traits;

use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\Traits\GetIdByClassnameTrait
 */
class GetIdByClassnameTraitTest extends TestCase {

  public function testFoo() {
    $subject = new SomeOddClassName();
    $this->assertSame('Some Odd Class Name', $subject->getId());
  }
}
