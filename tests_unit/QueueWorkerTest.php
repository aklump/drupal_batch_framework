<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit;

use AKlump\Drupal\BatchFramework\OperationInterface;
use AKlump\Drupal\BatchFramework\QueueItemInterface;
use AKlump\Drupal\BatchFramework\QueueWorker;
use AKlump\Drupal\BatchFramework\QueueWorkerTimeoutException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\QueueWorker
 * @uses   \AKlump\Drupal\BatchFramework\DrupalMode
 * @uses   \AKlump\Drupal\BatchFramework\Helpers\CreateLoggingChannel
 * @uses   \AKlump\Drupal\BatchFramework\Helpers\GetMessenger
 * @uses   \AKlump\Drupal\BatchFramework\Operator
 */
class QueueWorkerTest extends TestCase {

  public function testOperationNotFinishedAtTimeoutThrowsTimeoutException() {
    // why: when a queue operation does say it's complete, then the item should remain in the queue.
    $operation = $this->createConfiguredMock(OperationInterface::class, [
      'getLabel' => 'FooOperation',
    ]);
    $operation->expects($this->atLeast(1))
      ->method('getProgressRatio')
      ->willReturn(0.0);
    $item = [QueueItemInterface::OPERATION => $operation];

    $this->expectException(QueueWorkerTimeoutException::class);
    (new QueueWorker())->setTimeout(0)($item);
  }

  public function testBatchContextResultsExceptionsThrows() {
    $operation = $this->createMock(OperationInterface::class);
    $operation->expects($this->exactly(1))
      ->method('isInitialized')
      ->willReturn(TRUE);
    $operation->expects($this->exactly(1))
      ->method('getProgressRatio')
      ->willReturn(0.0);
    $operation->expects($this->exactly(1))
      ->method('process')
      ->willReturnCallback(function () {
        throw new \LogicException('foo', 13);
      });

    $item = [QueueItemInterface::OPERATION => $operation];
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('foo');
    $this->expectExceptionCode(13);
    (new QueueWorker())($item);
  }

  public function testNoOperationThrows() {
    $this->expectException(\InvalidArgumentException::class);
    $item = [];
    (new QueueWorker())($item);
  }

  public function testSetLoggerChannel() {
    $worker = new QueueWorker();
    $this->assertSame($worker, $worker->setLoggerChannel('foo'));
  }
}
