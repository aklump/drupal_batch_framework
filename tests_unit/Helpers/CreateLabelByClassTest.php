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
      'Std Class',
      new \stdClass(),
    ];
    $tests[] = [
      'Runtime Exception',
      new \RuntimeException(),
    ];
    $tests[] = [
      'Create Label By Class',
      new CreateLabelByClass(),
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
