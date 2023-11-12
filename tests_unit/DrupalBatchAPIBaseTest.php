<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit;

use AKlump\Drupal\BatchFramework\Adapters\DrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalLoggerAdapter;
use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\DrupalBatchAPIBase;
use AKlump\Drupal\BatchFramework\DrupalMode;
use AKlump\Drupal\BatchFramework\MessengerInterface;
use AKlump\Drupal\BatchFramework\OperationInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \AKlump\Drupal\BatchFramework\DrupalBatchAPIBase
 * @uses   \AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalLoggerAdapter
 * @uses   \AKlump\Drupal\BatchFramework\DrupalMode::get()
 * @uses   \AKlump\Drupal\BatchFramework\DrupalMode::set()
 * @uses   \AKlump\Drupal\BatchFramework\DrupalMode::isModern()
 */
class DrupalBatchAPIBaseTest extends TestCase {

  public function testFinishWithFailCallsLoggerForBatchAndForOpErrors() {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->exactly(2))->method('error');

    $batch = $this->createPartialMock(Batch_ModernDrupal::class, [
      'getLogger',
    ]);
    $batch->method('getLogger')->willReturn($logger);

    $batch_data = [
      'exceptions' => [
        (new GetExceptionData())(
          $this->createMock(OperationInterface::class),
          new Exception()
        ),
      ],
    ];
    $batch->finish(FALSE, $batch_data, []);
  }

  public function testFinishWithSuccessCallsLoggerInfo() {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->exactly(1))->method('info');

    $batch = $this->createPartialMock(Batch_ModernDrupal::class, ['getLogger']);
    $batch->method('getLogger')->willReturn($logger);

    $batch_data = [];
    $batch->finish(TRUE, $batch_data, []);
  }

  public function dataForTestOnBatchFinishedReceivesCorrectStatusProvider() {
    $tests = [];
    $tests[] = [
      TRUE,
      [],
    ];
    $tests[] = [
      FALSE,
      [
        [
          $this->createMock(OperationInterface::class),
          new Exception(),
        ],
      ],
    ];

    return $tests;
  }

  /**
   * @dataProvider dataForTestOnBatchFinishedReceivesCorrectStatusProvider
   */
  public function testCorrectBatchStopMethodIsCalled(bool $expected, array $exception_data) {
    $batch_data = [
      'exceptions' => $exception_data,
    ];

    $logger = $this->createMock(LoggerInterface::class);

    if ($expected) {
      $batch = $this->createPartialMock(Batch_ModernDrupal::class, [
        'getLogger',
        'handleSuccessfulBatch',
      ]);
      $batch->method('getLogger')->willReturn($logger);
      $batch
        ->expects($this->exactly(1))
        ->method('handleSuccessfulBatch')
        ->willReturnCallback(function () use (&$batch_data) {
          $batch_data = func_get_args();
        });
    }
    else {
      $batch = $this->createPartialMock(Batch_ModernDrupal::class, [
        'getLogger',
        'handleFailedBatch',
      ]);
      $batch->method('getLogger')->willReturn($logger);
      $batch
        ->expects($this->exactly(1))
        ->method('handleFailedBatch')
        ->willReturnCallback(function () use (&$batch_data) {
          $batch_data = func_get_args();
        });
    }

    $batch->finish(TRUE, $batch_data, []);
  }

  public function testHandleSuccessfulBatchReceivesElapsedTime() {
    $expected_min_duration = 120;

    $batch_data = [
      'start' => time() - $expected_min_duration,
    ];

    $logger = $this->createMock(LoggerInterface::class);
    $batch = $this->createPartialMock(Batch_ModernDrupal::class, [
      'getLogger',
      'handleSuccessfulBatch',
    ]);
    $batch->method('getLogger')->willReturn($logger);
    $batch->method('handleSuccessfulBatch')
      ->willReturnCallback(function () use (&$elapsed) {
        $elapsed = func_get_args()[0]['elapsed'];
      });
    $batch->finish(TRUE, $batch_data, []);

    $this->assertGreaterThanOrEqual($expected_min_duration, $elapsed);
  }

  public function testHandleFailedBatchReceivesElapsedTime() {
    $expected_min_duration = 120;

    $batch_data = [
      'start' => time() - $expected_min_duration,
      'exceptions' => [
        (new GetExceptionData())(
          $this->createMock(OperationInterface::class),
          new Exception()
        ),
      ],
    ];

    $logger = $this->createMock(LoggerInterface::class);
    $batch = $this->createPartialMock(Batch_ModernDrupal::class, [
      'getLogger',
      'handleFailedBatch',
    ]);
    $batch->method('getLogger')->willReturn($logger);
    $batch->method('handleFailedBatch')
      ->willReturnCallback(function () use (&$elapsed) {
        $elapsed = func_get_args()[0]['elapsed'];
      });
    $batch->finish(TRUE, $batch_data, []);

    $this->assertGreaterThanOrEqual($expected_min_duration, $elapsed);
  }

  public function testAutoDetectCanSniffLegacyDrupal() {
    $this->assertSame(DrupalMode::LEGACY, (new Batch_AutoDetectDrupal())->getDrupalMode()
      ->get());
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

  public function getDrupalMode(): DrupalMode {
    return (new DrupalMode())->set(DrupalMode::MODERN);
  }

  public function getLabel(): string {
    return get_class($this);
  }

  public function getOperations(): array {
    return [];
  }

}

class Batch_LegacyDrupal extends DrupalBatchAPIBase {

  public function getDrupalMode(): DrupalMode {
    return (new DrupalMode())->set(DrupalMode::LEGACY);
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


