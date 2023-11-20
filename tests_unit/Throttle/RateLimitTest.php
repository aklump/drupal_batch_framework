<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit\Throttle;

use AKlump\Drupal\BatchFramework\Throttle\RateLimit;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\Throttle\RateLimit
 */
class RateLimitTest extends TestCase {

  public function testToString() {
    $limit = new RateLimit(1, 'PT1M');
    $this->assertSame('1 every minute', (string) $limit);
    $limit = new RateLimit(1, 'PT5M');
    $this->assertSame('1 every 5 minutes', (string) $limit);

    $limit = new RateLimit(1, 'PT1H');
    $this->assertSame('1 every hour', (string) $limit);
    $limit = new RateLimit(1, 'PT3H');

    $this->assertSame('1 every 3 hours', (string) $limit);
    $limit = new RateLimit(1, 'PT10H15M');
    $this->assertSame('1 every 10 hours 15 minutes', (string) $limit);
  }

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
