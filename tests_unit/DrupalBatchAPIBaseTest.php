<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit;

use AKlump\Drupal\BatchFramework\Adapters\DrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalLoggerAdapter;
use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\DrupalBatchAPIBase;
use AKlump\Drupal\BatchFramework\MessengerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \AKlump\Drupal\BatchFramework\DrupalBatchAPIBase
 * @uses   \AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalLoggerAdapter
 */
class DrupalBatchAPIBaseTest extends TestCase {

  public function testOnBatchFinishedWithFailCallsLoggerError() {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->exactly(1))->method('error');

    $batch = $this->createPartialMock(Batch_ModernDrupal::class, ['getLogger']);
    $batch->method('getLogger')->willReturn($logger);

    $batch_data = [];
    $batch->onBatchFinished(FALSE, $batch_data);
  }

  public function testOnBatchFinishedWithSuccessCallsLoggerInfo() {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->exactly(1))->method('info');

    $batch = $this->createPartialMock(Batch_ModernDrupal::class, ['getLogger']);
    $batch->method('getLogger')->willReturn($logger);

    $batch_data = [];
    $batch->onBatchFinished(TRUE, $batch_data);
  }

  public function testOnBatchFinishedCalculatesElapsedTime() {
    $expected_min_duration = 120;

    $batch_data = [
      'start' => time() - $expected_min_duration,
    ];

    $logger = $this->createMock(LoggerInterface::class);
    $batch = $this->createPartialMock(Batch_ModernDrupal::class, ['getLogger']);
    $batch->method('getLogger')->willReturn($logger);

    $batch->onBatchFinished(TRUE, $batch_data);
    $this->assertGreaterThanOrEqual($expected_min_duration, $batch_data['elapsed']);
  }

  public function testAutoDetectCanSniffLegacyDrupal() {
    $this->assertSame(DrupalBatchAPIBase::LEGACY, (new Batch_AutoDetectDrupal())->getMode());
  }

  public function testSetTitle() {
    $this->expectNotToPerformAssertions();
    (new Batch_ModernDrupal())->setTitle('Lorem Ipsum');
  }

  public function testSetBatchProcessingPageWorksWithDrupalCoreUrl() {
    $this->expectNotToPerformAssertions();
    (new Batch_ModernDrupal())->setBatchProcessingPage(new Url());
  }

  public function testSetBatchProcessingPageWorksWithString() {
    $this->expectNotToPerformAssertions();
    (new Batch_ModernDrupal())->setBatchProcessingPage('batch.php');
  }

  public function testGetMessengerReturnsLegacyDrupalMessengerAdapter() {
    $this->assertInstanceOf(LegacyDrupalMessengerAdapter::class, (new Batch_LegacyDrupal())->getMessenger());
  }

  public function testGetMessengerReturnsDrupalMessengerAdapter() {
    $this->assertInstanceOf(DrupalMessengerAdapter::class, (new Batch_ModernDrupal())->getMessenger());
  }

  public function testSetThenGetMessenger() {
    $messenger = $this->createMock(MessengerInterface::class);
    $batch = new Batch_ModernDrupal();
    $batch->setMessenger($messenger);

    $this->assertSame($messenger, $batch->getMessenger());
  }

  public function testGetLoggerReturnsLegacyDrupalLoggerAdapter() {
    $logger = (new Batch_LegacyDrupal())->getLogger();
    $this->assertInstanceOf(LegacyDrupalLoggerAdapter::class, $logger);
  }

  public function testSetThenGetLogger() {
    $logger = $this->createMock(LoggerInterface::class);
    $batch = new Batch_ModernDrupal();
    $batch->setLogger($logger);
    $this->assertSame($logger, $batch->getLogger());
  }

  public function testSetInitMessage() {
    $this->expectNotToPerformAssertions();
    (new Batch_ModernDrupal())->setInitMessage('Getting starting...');
  }

  public function testSetProgressMessage() {
    $this->expectNotToPerformAssertions();
    (new Batch_ModernDrupal())->setProgressMessage('Things are moving...');
  }

}

class Url {

  public function toString(): string {
    return '/foo';
  }

}

class_alias(Url::class, 'Drupal\Core\Url');


class Batch_ModernDrupal extends DrupalBatchAPIBase {

  public function getMode(): string {
    return static::MODERN;
  }

  public function getLabel(): string {
    return get_class($this);
  }

  public function getOperations(): array {
    return [];
  }

}

class Batch_LegacyDrupal extends DrupalBatchAPIBase {

  public function getMode(): string {
    return static::LEGACY;
  }

  public function getLabel(): string {
    return get_class($this);
  }

  public function getOperations(): array {
    return [];
  }

}

class Batch_AutoDetectDrupal extends DrupalBatchAPIBase {

  public function getLabel(): string {
    return get_class($this);
  }

  public function getOperations(): array {
    return [];
  }

}

