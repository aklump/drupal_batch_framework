<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit\Helpers;

use AKlump\Drupal\BatchFramework\Helpers\GetProgressRatio;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\Helpers\GetProgressRatio
 */
class GetProgressRatioTest extends TestCase {

  public function dataFortestInvokeReturnsCorrectFloatValueProvider() {
    $tests = [];
    $tests[] = [
      0.7,
      10,
      ['do', 're', 'mi']
    ];
    $tests[] = [
      1,
      10,
      -1
    ];
    $tests[] = [
      0,
      -1,
      1
    ];
    $tests[] = [
      1,
      0,
      0,
    ];
    $tests[] = [
      1,
      1,
      0,
    ];
    $tests[] = [
      0.5,
      10,
      5,
    ];
    $tests[] = [
      0,
      10,
      10,
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeReturnsCorrectFloatValueProvider
   */
  public function testInvokeReturnsCorrectFloatValue(float $expected, int $total, $remain) {
    $this->assertSame($expected, (new GetProgressRatio())($total, $remain));
  }

}
