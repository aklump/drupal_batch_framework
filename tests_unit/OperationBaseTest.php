<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit;

use AKlump\Drupal\BatchFramework\Adapters\DrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalLoggerAdapter;
use AKlump\Drupal\BatchFramework\DrupalBatchAPIOperationBase;
use AKlump\Drupal\BatchFramework\DrupalMode;
use AKlump\Drupal\BatchFramework\OperationInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \AKlump\Drupal\BatchFramework\DrupalBatchAPIOperationBase
 * @uses   \AKlump\Drupal\BatchFramework\DrupalMode
 * @uses   \AKlump\Drupal\BatchFramework\Helpers\CreateLabelByClass
 * @uses   \AKlump\Drupal\BatchFramework\Helpers\GetLogger
 * @uses   \AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalLoggerAdapter
 */
class OperationBaseTest extends TestCase {

  public function testGetBatchFailuresPassesContext() {
    $batch_context = [];
    $batch_context['results']['exceptions'] = [
      (new GetExceptionData())($this->createMock(OperationInterface::class), new \RuntimeException('foo')),
      (new GetExceptionData())($this->createMock(OperationInterface::class), new \RuntimeException('bar')),
    ];
    $op = new Operation();
    $op->setBatchContext($batch_context);
    $this->assertSame($batch_context['results']['exceptions'], $op->getBatchFailures());
  }

  public function testGetBatchFailuresIsEmptyWhenContextIsEmpty() {
    $batch_context = [];
    $op = new Operation();
    $op->setBatchContext($batch_context);
    $this->assertSame([], $op->getBatchFailures());
  }

  public function testGetLoggerReturnsCorrectBasedOnLegacyMode() {
    $op = new Operation();
    $op->setDrupalMode(new DrupalMode(DrupalMode::LEGACY));
    $this->assertInstanceOf(LegacyDrupalLoggerAdapter::class, $op->getLogger());
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

class Operation extends DrupalBatchAPIOperationBase {

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
