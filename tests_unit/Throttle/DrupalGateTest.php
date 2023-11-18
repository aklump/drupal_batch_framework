<?php

namespace AKlump\Drupal\BatchFramework\Tests\Unit\Throttle;

use AKlump\Drupal\BatchFramework\Adapters\StateInterface;
use AKlump\Drupal\BatchFramework\Throttle\DrupalGate;
use AKlump\Drupal\BatchFramework\Throttle\RateLimit;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\Drupal\BatchFramework\Throttle\DrupalGate
 * @uses   \AKlump\Drupal\BatchFramework\Throttle\RateLimit
 */
class DrupalGateTest extends TestCase {

  public function testGateOpensAndClosesBaseOnRateLimit() {
    $data = [];
    $state = $this->createMock(StateInterface::class);
    $state->method('get')
      ->willReturnCallback(function ($key) use (&$data) {
        return $data[$key] ?? NULL;
      });
    $state->method('set')
      ->willReturnCallback(function ($key, $value) use (&$data) {
        $data[$key] = $value;
      });
    $rate_limit = new RateLimit(1, 'PT1S');

    $gate = new DrupalGate('foo', $rate_limit, $state);

    // 1. Gate starts out open
    $this->assertFalse($gate->isClosed());
    $this->assertFalse($gate->isClosed());
    $gate->allowOneThrough();

    // 2. The closes after letting one through.
    $this->assertTrue($gate->isClosed());
    $this->assertTrue($gate->isClosed());

    // 3. If we wait a second it should reopen
    sleep(1);
    $this->assertFalse($gate->isClosed());
    $gate->allowOneThrough();
    $this->assertTrue($gate->isClosed());
  }

}
