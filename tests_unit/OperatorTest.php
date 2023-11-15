<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit;

use AKlump\Drupal\BatchFramework\OperationInterface;
use AKlump\Drupal\BatchFramework\Operator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\Operator
 */
class OperatorTest extends TestCase {

  public function testMaxExecutionZeroCallsProcessExactlyOneTime() {
    $operation = $this->createMock(OperationInterface::class);
    $operation->expects($this->exactly(1))
      ->method('isInitialized')
      ->willReturn(FALSE);
    $operation->expects($this->exactly(1))->method('initialize');
    $operation->expects($this->exactly(1))
      ->method('getProgressRatio')
      ->willReturn(0.0, 1.0);
    $operation->expects($this->exactly(1))
      ->method('process')
      ->willReturnCallback(function () {
        throw new \LogicException('foo', 13);
      });

    Operator::handleOperation($operation);
  }
}
