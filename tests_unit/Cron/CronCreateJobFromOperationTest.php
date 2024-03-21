<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit\Cron;

use AKlump\Drupal\BatchFramework\Batch\OperationInterface;
use AKlump\Drupal\BatchFramework\Cron\CronCreateJobFromOperation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\Cron\CronCreateJobFromOperation
 */
final class CronCreateJobFromOperationTest extends TestCase {

  public function testMaxTimeZeroNeverStartsOperation() {
    $operation = $this->createMock(OperationInterface::class);
    $operation->expects($this->never())->method('isInitialized');
    $job = new CronCreateJobFromOperation($operation);
    $job->setMaxTime(0)->do();
  }

}
