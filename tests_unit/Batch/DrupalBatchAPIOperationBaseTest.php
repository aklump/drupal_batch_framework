<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit\Batch;

use AKlump\Drupal\BatchFramework\Adapters\MessengerInterface;
use AKlump\Drupal\BatchFramework\Batch\DrupalBatchAPIOperationBase;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \AKlump\Drupal\BatchFramework\Batch\DrupalBatchAPIOperationBase
 * @uses   \AKlump\Drupal\BatchFramework\Helpers\CreateLabelByClass
 * @uses   \AKlump\Drupal\BatchFramework\Helpers\GetLogger
 * @uses   \AKlump\Drupal\BatchFramework\DrupalMode
 * @uses   \AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalLoggerAdapter
 *
 */
class DrupalBatchAPIOperationBaseTest extends TestCase {

  public function testGetLogger() {
    $this->assertInstanceOf(LoggerInterface::class, (new TestableOperation())->getLogger());
  }

  public function testGetLoggerChannel() {
    $foo = new TestableOperation();
    $this->assertSame('Testable operation', $foo->getLoggerChannel());
    $foo = new TestableOperation();
    $batch_context = ['logger_channel' => 'lorem'];
    $foo->setBatchContext($batch_context);
    $this->assertSame('lorem', $foo->getLoggerChannel());
  }

  public function testGetMessenger() {
    $foo = new TestableOperation();
    $messenger = $this->createMock(MessengerInterface::class);
    $batch_context = ['messenger' => $messenger];
    $foo->setBatchContext($batch_context);
    $this->assertSame($messenger, $foo->getMessenger());
  }

  public function testGetLabel() {
    $this->assertSame('Testable operation', (new TestableOperation())->getLabel());
  }

  public function testGetRemainingTimeReturnsExpected() {
    $start = time();
    $max_execution = 10;
    $foo = new TestableOperation();
    $context = [
      'results' => ['start' => $start],
      'max_execution_seconds' => $max_execution,
    ];
    $foo->setBatchContext($context);
    $remaining = $foo->getRemainingTime();
    $this->assertLessThanOrEqual($max_execution, $remaining);
    $this->assertGreaterThanOrEqual($max_execution - 1, $remaining);
  }

  public function testGetRemainingUsesDefaultTimeoutIfNoContext() {
    $foo = new TestableOperation();
    $batch_context = [
      'results' => ['start' => time()],
    ];
    $foo->setBatchContext($batch_context);
    $remaining = $foo->getRemainingTime();
    $timeout = DrupalBatchAPIOperationBase::DEFAULT_MAX_EXECUTION;
    $this->assertLessThanOrEqual($timeout, $remaining);
    $this->assertGreaterThanOrEqual($timeout - 1, $remaining);
  }

  public function testGetRemainingTimeThrowsWithMissingStart() {
    $foo = new TestableOperation();
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessageMatches('#results.start#');
    $foo->getRemainingTime();
  }

  public function testBatchContextResultsStartIsSetIfMissing() {
    $foo = new TestableOperation();
    $start = time();
    $batch_context = [];
    $foo->setBatchContext($batch_context);
    $this->assertGreaterThanOrEqual($start, $batch_context['results']['start']);
  }

  public function testBatchContextResultsStartIsUntouchedWhenAlreadySet() {
    $foo = new TestableOperation();
    $batch_context = ['results' => ['start' => 1711056007]];
    $foo->setBatchContext($batch_context);
    $this->assertEquals(1711056007, $batch_context['results']['start']);
  }
}

class TestableOperation extends DrupalBatchAPIOperationBase {

  public function isInitialized(): bool {
    // TODO: Implement isInitialized() method.
  }

  public function getProgressRatio(): float {
    // TODO: Implement getProgressRatio() method.
  }

  public function process(): void {
    // TODO: Implement process() method.
  }
}
