<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit;

use AKlump\Drupal\BatchFramework\OperationInterface;
use AKlump\Drupal\BatchFramework\Operator;
use AKlump\Drupal\BatchFramework\UnmetDependencyException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\Operator
 * @uses   \AKlump\Drupal\BatchFramework\UnmetDependencyException
 */
class OperatorTest extends TestCase {

  public function testOperationsFinishedGrowsEachTimeOpFinishes() {
    $batch_context = [];

    $operation = $this->createConfiguredMock(OperationInterface::class, [
      'getProgressRatio' => 1.0,
    ]);
    Operator::handleOperation($operation, 30, NULL, NULL, $batch_context);

    $operation = $this->createConfiguredMock(OperationInterface::class, [
      'getProgressRatio' => 1.0,
    ]);
    Operator::handleOperation($operation, 30, NULL, NULL, $batch_context);
    $this->assertCount(2, $batch_context['results']['operations_finished']);
  }

  public function testTwoExceptionsCaughtWhenSecondFinishThrows() {
    $operation = $this->createConfiguredMock(OperationInterface::class, [
      'getProgressRatio' => 1.0,
    ]);
    $operation->method('isInitialized')
      ->willReturnCallback(function () {
        throw new \RuntimeException();
      });
    $operation
      ->expects($this->once())
      ->method('finish')
      ->willReturnCallback(function () {
        throw new \RuntimeException();
      });
    $batch_context = [];
    Operator::handleOperation($operation, 30, NULL, NULL, $batch_context);
    $this->assertSame(1, $batch_context['finished']);
    $this->assertCount(2, $batch_context["results"]["exceptions"]);
  }

  public function testOperationMethodsNotCalledIfBatchFailes() {
    $operation = $this->createMock(OperationInterface::class);
    $operation->expects($this->once())
      ->method('getBatchFailures')
      ->willReturn([1, 2, 3]);
    $operation->expects($this->never())->method('isInitialized');
    $operation->expects($this->never())->method('initialize');
    $operation->expects($this->never())->method('getProgressRatio');
    $operation->expects($this->never())->method('process');
    $operation->expects($this->never())->method('finish');
    $batch_context = [];
    Operator::handleOperation($operation, 30, NULL, NULL, $batch_context);
    $this->assertSame(1, $batch_context['finished']);
  }

  public function testUnmetDependenciesThrows() {
    $batch_context = [];
    //    $batch_context['results']['operations_finished'] = [];
    $operation = $this->createConfiguredMock(OperationInterface::class, [
      'getDependencies' => ['\Foo'],
    ]);
    Operator::handleOperation($operation, 30, NULL, NULL, $batch_context);
    $exception = $batch_context["results"]["exceptions"][0];
    $this->assertSame(UnmetDependencyException::class, $exception['exception_class']);
  }

  public function testExceptionCaughtIfFinishThrows() {
    $operation = $this->createConfiguredMock(OperationInterface::class, [
      'getProgressRatio' => 1.0,
    ]);
    $operation->expects($this->exactly(1))
      ->method('finish')
      ->willReturnCallback(function () {
        throw new \RuntimeException();
      });
    Operator::handleOperation($operation, 3);
  }

  public function dataFortestFinishIsCalledAfterMethodThrowsProvider() {
    $tests = [];
    $tests[] = ['isInitialized'];
    $tests[] = ['initialize'];
    $tests[] = ['getProgressRatio'];
    $tests[] = ['process'];

    return $tests;
  }

  /**
   * @dataProvider dataFortestFinishIsCalledAfterMethodThrowsProvider
   */
  public function testFinishIsCalledAfterMethodThrows(string $method) {
    $operation = $this->createMock(OperationInterface::class);
    $operation->expects($this->exactly(1))
      ->method($method)
      ->willReturnCallback(function () {
        throw new \RuntimeException();
      });
    $operation->expects($this->once())->method('finish');
    Operator::handleOperation($operation, 3);
  }

  public function testInitializeIsNotCalledWhenIsInitializedIsTrue() {
    $operation = $this->createMock(OperationInterface::class);
    $operation->expects($this->exactly(1))
      ->method('isInitialized')
      ->willReturn(TRUE);
    $operation->expects($this->never())->method('initialize');
    $operation->method('getProgressRatio')->willReturn(1.0);
    Operator::handleOperation($operation, 3);
  }

  public function testOperatorStopsAfterMaxExecutionIsReachedEvenIfNotFinished() {
    $max_execution = 1;
    $start = time();
    $operation = $this->createMock(OperationInterface::class);
    $operation->method('getProgressRatio')->willReturn(0.0);

    Operator::handleOperation($operation, $max_execution);
    $this->assertSame(time(), $start + $max_execution);
  }

  public function testMaxExecutionZeroCallsProcessExactlyOneTime() {
    $operation = $this->createMock(OperationInterface::class);
    $operation->expects($this->exactly(1))
      ->method('isInitialized')
      ->willReturn(TRUE);
    $operation->expects($this->exactly(2))
      ->method('getProgressRatio')
      ->willReturn(0.0);
    $operation->expects($this->exactly(1))
      ->method('process');

    Operator::handleOperation($operation, 0);
  }
}
