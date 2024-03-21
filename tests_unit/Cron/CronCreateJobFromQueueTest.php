<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit\Cron;

use AKlump\Drupal\BatchFramework\Batch\OperationInterface;
use AKlump\Drupal\BatchFramework\Cron\CronCreateJobFromOperation;
use AKlump\Drupal\BatchFramework\Cron\CronCreateJobFromQueue;
use AKlump\Drupal\BatchFramework\Queue\QueueDefinitionInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\Cron\CronCreateJobFromQueue
 */
final class CronCreateJobFromQueueTest extends TestCase {

  public function testMaxTimeZeroNeverStartsOperation() {
    $queue = $this->createMock(QueueDefinitionInterface::class);
    $queue->expects($this->never())->method('getName');
    $job = new CronCreateJobFromQueue($queue);
    $job->setMaxTime(0)->do();
  }

}
