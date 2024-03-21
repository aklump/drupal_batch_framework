<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit\Batch;

use AKlump\Drupal\BatchFramework\Adapters\DrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalLoggerAdapter;
use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\Adapters\MessengerInterface;
use AKlump\Drupal\BatchFramework\Batch\DrupalBatchAPIBase;
use AKlump\Drupal\BatchFramework\Batch\OperationInterface;
use AKlump\Drupal\BatchFramework\DrupalMode;
use AKlump\Drupal\BatchFramework\Tests\Unit\GetExceptionData;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \AKlump\Drupal\BatchFramework\Batch\DrupalBatchAPIBase
 * @uses   \AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalLoggerAdapter
 * @uses   \AKlump\Drupal\BatchFramework\DrupalMode
 * @uses   \AKlump\Drupal\BatchFramework\Helpers\GetLogger
 * @uses   \AKlump\Drupal\BatchFramework\Helpers\GetMessenger
 * @uses   \AKlump\Drupal\BatchFramework\Helpers\CreateLoggingChannel
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

  public function testHandleFailedBatchIsOnlyCalledIfBatchFails() {
    $logger = $this->createMock(LoggerInterface::class);
    $batch = new Batch_ModernDrupal($logger);

    global $handle_failed_batch_args;

    $batch_data = [];

    $handle_failed_batch_args = NULL;
    $batch->finish(TRUE, $batch_data, []);
    $this->assertNull($handle_failed_batch_args);

    $batch_data = [];
    $handle_failed_batch_args = NULL;
    $batch->finish(FALSE, $batch_data, []);
    $this->assertIsArray($handle_failed_batch_args);
    $this->assertNotEmpty($handle_failed_batch_args);
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
    $batch = new Batch_ModernDrupal($logger);

    global $handle_failed_batch_args;

    $handle_failed_batch_args = NULL;
    $batch->finish(FALSE, $batch_data, []);

    $this->assertGreaterThanOrEqual($batch_data['start'], $handle_failed_batch_args[0]["start"]);
    $this->assertGreaterThanOrEqual($expected_min_duration, $handle_failed_batch_args[0]["elapsed"]);
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

  /**
   * @var \Psr\Log\LoggerInterface|null
   */
  private ?LoggerInterface $logger;

  public function __construct(LoggerInterface $logger = NULL) {
    $this->logger = $logger;
  }

  public function getDrupalMode(): DrupalMode {
    return new DrupalMode(DrupalMode::MODERN);
  }

  public function getLogger(): LoggerInterface {
    return $this->logger;
  }

  public function getLabel(): string {
    return get_class($this);
  }

  public function getOperations(): array {
    return [];
  }

  public static function handleFailedBatch(array $batch_results, array $exceptions, MessengerInterface $messenger, LoggerInterface $logger, string $logger_channel): void {
    global $handle_failed_batch_args;
    $handle_failed_batch_args = func_get_args();
  }

}

class Batch_LegacyDrupal extends DrupalBatchAPIBase {

  public function getDrupalMode(): DrupalMode {
    return new DrupalMode(DrupalMode::LEGACY);
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


