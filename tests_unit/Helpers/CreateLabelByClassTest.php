<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit\Helpers;

use AKlump\Drupal\BatchFramework\Helpers\CreateLabelByClass;
use AKlump\Drupal\BatchFramework\Helpers\GetProgressRatio;
use AKlump\Drupal\BatchFramework\Tests\Unit\Traits\SomeOddClassName;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\Helpers\CreateLabelByClass
 */
class CreateLabelByClassTest extends TestCase {

  public function dataFortestInvokeReturnsCorrectFloatValueProvider() {
    $tests = [];
    $tests[] = [
      'Std class',
      new \stdClass(),
    ];
    $tests[] = [
      'Runtime exception',
      new \RuntimeException(),
    ];
    $tests[] = [
      'Create label by class',
      new CreateLabelByClass(),
    ];
    $tests[] = [
      'Create label by class',
      CreateLabelByClass::class,
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeReturnsCorrectFloatValueProvider
   */
  public function testInvokeReturnsCorrectFloatValue($expected, $object) {
    $this->assertSame($expected, (new CreateLabelByClass())($object));
  }

}
