<!--
id: queue_cron_jobs
tags: ''
-->

# Working With Cron Queues

1. Create a queue definition by implementing `\AKlump\Drupal\BatchFramework\QueueDefinitionInterface`
3. Do implement `hook_cron`  as shown below with your queue definition class.
4. Fill the queue using operations (see below).
5. Ensure cron is running.
2. Note this strategy does not use `hook_cron_queue_info`.

```php
class FooQueue implements \AKlump\Drupal\BatchFramework\QueueDefinitionInterface {

  use \AKlump\Drupal\BatchFramework\Traits\GetLabelByClassnameTrait;

  public function getName(): string {
    return 'foo_queue';
  }

  public function getWorker(): callable {
    return (new \AKlump\Drupal\BatchFramework\Queue\QueueWorker())->setLoggerChannel($this->getLoggerChannel());
  }

  public function getLoggerChannel(): string {
    return $this->getLabel();
  }
  
}
```

```php
/**
 * Implements hook_cron_queue_info().
 */
function my_module_cron_() {
  (new CronCreateJobFromQueue(new FooQueue()))
    ->setMaxTime(30)
    ->do();
}
```

### Add an Item to the Queue

The most important is to ensure you add the operation instance that will process the item to the queue item as `operation`.

```php
$queue_name = (new FooQueue())->getName();
$queue = \DrupalQueue::get($queue_name);
$queue->createQueue();

$item = [
  \AKlump\Drupal\BatchFramework\QueueItemInterface::OPERATION => new BarOperation(),
  'key' => 'data',
  'key2' => 'data2'
];
if (FALSE === $queue->createItem($item)) {
  $logger_channel = (new FooQueue())->getLoggerChannel();
  $logger = (new \AKlump\Drupal\BatchFramework\Helpers\GetLogger(new \AKlump\Drupal\BatchFramework\DrupalMode()))($logger_channel);
  $logger->error("Failed to queue item");
}

```

### The Operation Class

* If the operation throws any exception the item remains in the queue.
* If the operation times out the item remains in the queue.
* If the operation returns `getProgressRatio()` < 1 on the final pass, the item remains in the queue.
* The queue item is available in `$this->context['results'][QueueWorkerInterface::ITEMS]`; see `CronOperation::process`

```php
<?php

namespace Drupal\ova_user_export\Batch\Operations;

use AKlump\Drupal\BatchFramework\DrupalBatchAPIOperationBase;
use AKlump\Drupal\BatchFramework\Helpers\GetProgressRatio;
use AKlump\Drupal\BatchFramework\QueueWorker;
use Drupal\ova_user_export\Mail\EmailEventObjectId;
use Drupal\ova_user_export\Mail\GetTemplateByMessage;
use Drupal\ovagraph_core\UserActivity\Event;
use Drupal\ovagraph_core\UserActivity\EventStorage;

class CronOperation extends DrupalBatchAPIOperationBase {

  private bool $recordEvents;

  public function __construct(bool $record_events = TRUE) {
    $this->recordEvents = $record_events;
  }

  public function isInitialized(): bool {
    return isset($this->sb['items']);
  }

  public function initialize(): void {
    $this->sb['items'] = $this->context['results'][QueueWorkerInterface::ITEMS];
    $this->sb['total'] = count($this->sb['items']);
  }

  public function getProgressRatio(): float {
    return (new GetProgressRatio())($this->sb['total'], $this->sb['items']);
  }

  public function process(): void {
    $item = array_pop($this->sb['items']);

    // TODO Implement throttle mechanism, e.g no more than 100 emails per hour.

    if (isset($item['uid'])) {
      $user = user_load($item['uid']);
    }
    else {
      $user = user_load_by_mail($item['send_to']);
    }
    if (!$user) {
      $this->getLogger()
        ->error('Email not sent; cannot locate user by @mail', ['@mail' => $item['send_to']]);

      return;
    }

    $template_class = $item['template_class'];
    /** @var \Drupal\ova_user_export\Mail\BulkEmailInterface $template */
    $template = new $template_class();
    $message = drupal_mail('ova_user_export', $template->getDrupalMailKey(), $item['send_to'], LANGUAGE_NONE, [
      GetTemplateByMessage::KEY => $template_class,
      EmailEventObjectId::KEY => $item[EmailEventObjectId::KEY],
    ]);

    if (isset($message['result']) && TRUE === $message['result']) {
      $event_type = $template->getUserEventTypeSent();
      if ($event_type) {
        $identifier = new EmailEventObjectId($message);
        $object_id = $identifier->get();
        if (NULL === $object_id) {
          $object_id = $identifier::createId($message['to'], $message['subject']);
          $identifier->set($object_id);
        }
        if ($this->recordEvents) {
          $event = new Event($event_type, $user->uid, $object_id);
          (new EventStorage())->save($event);
        }
      }
    }

    $this->shared['drupal_mail'][] = $message;
  }

}

```

## Rate Limits on Cron Queue

To limit the speed at which items are processed in the cron queue you should use an instance of `\AKlump\Drupal\BatchFramework\Throttle\GateInterface`.

```php
class BulkMailQueue implements QueueDefinitionInterface {
  public function getWorker(): callable {
  
    // 1. Create a gate that will limit batch flow to 1 per 5 minutes.
    $gate = new \AKlump\Drupal\BatchFramework\Throttle\DrupalGate(
      $this->getName(),
      new \AKlump\Drupal\BatchFramework\Throttle\RateLimit(1, 'PT5M')
    );

    return (new QueueWorker())
      // 2. Pass the gate to the worker.
      ->setRateLimitGate($gate)
      ->setLoggerChannel($this->getLoggerChannel());
  }
}  
```
