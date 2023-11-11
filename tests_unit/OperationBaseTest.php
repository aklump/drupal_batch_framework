<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit;

use AKlump\Drupal\BatchFramework\Adapters\DrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\BatchFailedException;
use AKlump\Drupal\BatchFramework\OperationBase;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \AKlump\Drupal\BatchFramework\OperationBase
 */
class OperationBaseTest extends TestCase {

  public function testGetBatchFailuresPassesContext() {
    $batch_context = [];
    $batch_context['results']['batch_failed_exceptions'] = [
      new BatchFailedException('foo'),
      new BatchFailedException('bar'),
    ];
    $op = new Operation();
    $op->setBatchContext($batch_context);
    $this->assertSame($batch_context['results']['batch_failed_exceptions'], $op->getBatchFailures());
  }

  public function testGetBatchFailuresIsEmptyWhenContextIsEmpty() {
    $batch_context = [];
    //    $batch_context['results']['batch_failed_exceptions'] = [];
    $op = new Operation();
    $op->setBatchContext($batch_context);
    $this->assertSame([], $op->getBatchFailures());
  }

  public function testGetLogger() {
    $batch_context = [];
    $batch_context['logger'] = $this->createMock(LoggerInterface::class);
    $op = new Operation();
    $op->setBatchContext($batch_context);
    $this->assertSame($batch_context['logger'], $op->getLogger());
  }

  public function testSkipOnBatchFailure() {
    $this->assertTrue((new Operation())->skipOnBatchFailure());
  }

  public function testGetDependencies() {
    $this->assertSame([], (new Operation())->getDependencies());

  }

  public function testGetMessenger() {
    $batch_context = [];
    $batch_context['messenger'] = $this->createMock(DrupalMessengerAdapter::class);
    $op = new Operation();
    $op->setBatchContext($batch_context);
    $this->assertSame($batch_context['messenger'], $op->getMessenger());
  }
}

class Operation extends OperationBase {

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
