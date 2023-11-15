<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit\Helpers;

use AKlump\Drupal\BatchFramework\Helpers\CreateLoggingChannel;
use AKlump\Drupal\BatchFramework\OperationInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\Helpers\CreateLoggingChannel
 */
class CreateLoggingChannelTest extends TestCase {

  public function dataFortest__invokeProvider() {
    $tests = [];
    $tests[] = [
      'foo: bar',
      'foo',
      'bar',
    ];
    $tests[] = [
      'foo: bar',
      'foo',
      $this->createConfiguredMock(OperationInterface::class, ['getLabel' => 'bar']),
    ];
    $tests[] = [
      '',
      '',
      $this->createConfiguredMock(OperationInterface::class, ['getLabel' => '']),
    ];
    $tests[] = [
      'ipsum',
      '',
      $this->createConfiguredMock(OperationInterface::class, ['getLabel' => 'ipsum']),
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortest__invokeProvider
   */
  public function test__invoke(string $expected, string $base_channel, $operation) {
    $this->assertSame($expected, (new CreateLoggingChannel())($base_channel, $operation));
  }
}
