<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit\Throttle;

use AKlump\Drupal\BatchFramework\Throttle\RateLimit;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\Throttle\RateLimit
 */
class RateLimitTest extends TestCase {


  public function testSetThenGetItemsPerIntervalWorks() {
    $limit = new RateLimit(1, 'P1D');
    $this->assertSame(100, $limit->setItemsPerInterval(100)
      ->getItemsPerInterval());
  }

  public function testSetThenGetIntervalReturnsSameInstance() {
    $limit = new RateLimit(1, 'PT5M');
    $interval = new \DateInterval('P1D');
    $limit->setInterval($interval);
    $this->assertSame($interval, $limit->getInterval());
  }

  public function testConstructorSetsItems() {
    $this->assertSame(100, (new RateLimit(100, 'PT5M'))->getItemsPerInterval());
  }

  public function testConstructorCreatesDateIntervalFromDurationString() {
    $duration = 'PT5M';
    $this->assertEquals(new \DateInterval($duration), (new RateLimit(1, $duration))->getInterval());
  }
}
