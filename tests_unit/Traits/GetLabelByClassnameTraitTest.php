<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit\Traits;

use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\Traits\GetLabelByClassnameTrait
 * @uses   \AKlump\Drupal\BatchFramework\Helpers\CreateLabelByClass::__invoke
 */
class GetLabelByClassnameTraitTest extends TestCase {

  public function testFoo() {
    $subject = new SomeOddClassName();
    $this->assertSame('Some odd class name', $subject->getLabel());
  }
}
