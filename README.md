# Drupal Batch Framework (A Drupal Component)

* [What are Drupal Components?](https://www.drupal.org/docs/core-modules-and-themes/basic-structure-of-drupal#s-drupal-components)
* This framework works with and uses a common interface for all Drupal versions.
* [Drupal.org Batch API Docs](https://www.drupal.org/docs/7/api/batch-api)

## Installation

1. Add this _composer.json_:

    ```json
    {
      "repositories": [
        {
          "type": "github",
          "url": "https://github.com/aklump/drupal_batch_framework"
        }
      ]
    }
    ```

1. `composer require aklump/drupal-batch-framework:^0`

## Usage

You will use this framework to create batches of operations. A batch contains
one or more operations.

1. Create a batch class by
   extending `\AKlump\Drupal\BatchFramework\DrupalBatchAPIBase` or
   implementing `\AKlump\Drupal\BatchFramework\BatchDefinitionInterface`.
2. Create one or more operations by extending `\AKlump\Drupal\BatchFramework\DrupalBatchAPIOperationBase` or
   implementing `\AKlump\Drupal\BatchFramework\OperationInterface`.
3. Add the operation(s) to your batch class; see below.
4. Create a form to trigger the batch.

## File Structure

Suggested class structure within _my_module/_

```php
.
└── src
    └── Batch
        ├── BatchDefinitions
        │   └── FooBatch.php
        └── Operations
            ├── BarOperation.php
            └── BazOperation.php
        ├── QueueDefinitions
            └── FooQueue.php
```

## Batch Definition Example

You may or many not need to pass anything to the class, the constructor is
optional, yet this example shows how it can be done.

```php
<?php

namespace Drupal\my_module\Batch\BatchDefinitions;

final class FooBatch extends \AKlump\Drupal\BatchFramework\DrupalBatchAPIBase {

  use \AKlump\Drupal\BatchFramework\Traits\GetLabelByClassnameTrait;

  private \Drupal\Core\Session\AccountInterface $account;

  public function __construct(\Drupal\Core\Session\AccountInterface $account) {
    $this->account = $account;
  }

  /**
   * @inheritDoc
   */
  public function getOperations(): array {
    $operations = [
      // This operation takes a couple of arguments, unlike the first.  One is
      // calculated at runtime and the other is a property of the batch.
      new \AKlump\Drupal\BatchFramework\Operations\BarOperation(date_create('now'), $this->account),
      // Another operation to be processed by this batch; it's unlimited.
      new \AKlump\Drupal\BatchFramework\Operations\BazOperation(),
    ];

    return $operations;
  }
}
```

## Operation Example

```php
<?php

namespace AKlump\Drupal\BatchFramework\Operations;

class BarOperation extends \AKlump\Drupal\BatchFramework\DrupalBatchAPIOperationBase {

  use \AKlump\Drupal\BatchFramework\Traits\GetLabelByClassnameTrait;

  public function __construct(\DateTimeInterface $date, \Drupal\Core\Session\AccountInterface $account) {
    $this->date = $date;
    $this->account = $account;
  }

  /**
   * @inheritDoc
   */
  public function isInitialized(): bool {
    return isset($this->sb['items']);
  }

  /**
   * @inheritDoc
   */
  public function initialize(): void {
    $this->sb['items'] = [10, 20, 30];
    $this->sb['total'] = count($this->sb['items']);
  }

  /**
   * @inheritDoc
   */
  public function getProgressRatio(): float {
    return (new \AKlump\Drupal\BatchFramework\Helpers\GetProgressRatio())($this->sb['total'], $this->sb['items']);
  }

  /**
   * @inheritDoc
   */
  public function process(): void {
    $item = array_shift($this->sb['items']);
    
    // TODO Do something with the item.
    
    $this->getLogger()->info("The item value is @value", ['@value' => $item]);
  }

}
```

## Start the Batch (< Drupal 8)

### Using a Form

```php
function some_form_submit_handler(array &$form, array $form_state) {
  // Grab data from the form inputs.
  $account = $form_state['values']['account'];

  // Identify and configure the batch you want to run.
  $batch = (new FooBatch($account))
    ->setTitle(t('Creating Archive File'))
    ->setInitMessage(t('Getting things rolling...'))
    ->setProgressMessage(t("Building your archive file; about @estimate until we're done."));

  $on_finish_goto = url(current_path());

  return $batch->process($on_finish_goto);
}
```

## From a Controller

@todo

## Start the Batch (Drupal 8+)

### Using a Form

```php
function some_form_submit_handler(array &$form, FormStateInterface $form_state) {
  // Grab data from the form inputs.
  $account = $form_state->get('account');

  // Identify and configure the batch you want to run.
  $batch = new FooBatch($account)
    ->setTitle($this->t('Lorem title'))
    ->setInitMessage($this->t('Start your engines...'))
    ->setProgressMessage(t("Building your archive file; about @estimate until we're done."));    

  // Deteremine where the user will be redirected after the batch stops.
  $on_finish_goto = Url::fromRoute('<front>')->toString();
  $response = $batch->process($on_finish_goto);
  $form_state->setResponse($response);
}
```

## From a Controller

`Operator::handleOperation` is an easy way to leverage your batch operation
outside of a batch. It allows you to trigger a single operation that will run
for a set duration. The second two arguments may be omitted if unnecessary.

```php
class BarController extends ControllerBase {

  public function process(AccountInterface $account) {
    $max_execution_in_seconds = 60;
    Operator::handleOperation(
      new BarOperation(date_create(), $account),
      $timeout_in_seconds,
      \Drupal::logger('conversions'),
      new DrupalMessengerAdapter(),
    );

    return new RedirectResponse($node->toUrl()->toString());
  }
}
```

## How to Handle Errors

### Batch Failures

* All exceptions thrown during a batch will be caught and cause the batch to be marked as failed.
* `BatchDefinitionInterface::handleFailedBatch` is always called after an exception is caught.
* Take appropriate action in `BatchDefinitionInterface::handleFailedBatch` such as using `getMessenger` to alert the user.
* See `\AKlump\Drupal\BatchFramework\Operator::handleOperation` which handles the exception for more info.

### Other Failures

* Operations having errors that do not constitute a batch failure should log them using `::getLogger` and handle the situation.  Here is an example of logging an exception during a process run.

```php
class FooOperation {
  public function process(): void {
    try {
      $uid = array_shift($this->sb['items']);
      $account = user_load($uid);
      // Do something to throw an exception
    }
    catch (\Exception $exception) {
      $this->getLogger()->error(sprintf('Failed user %d', $account->uid));
      $this->getLogger()
        ->error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
    }
  }
}
```

## How to Share Data Between Operations

`$this->shared` should be used to shared data. See `\AKlump\Drupal\BatchFramework\DrupalBatchAPIOperationBase::setBatchContext` for more info.

### Operation A

Pass a value by setting the value in your first operation.

```php
public function process(): void {
  $this->shared['path'] = '/foo/bar/baz.html'
}
```

### Operation B

Pull the value into the operation sandbox from the shared array.

```php
public function initialize(): void {
  $this->sb['path'] = $this->shared['path'];
}
```

## You Should Declare Operation Dependencies

You can ensure that operation A is run before operation B by implementing `\AKlump\Drupal\BatchFramework\OperationInterface::getDependencies`. This is generally necessary if you are sharing data across operations.

## UX Best Practices

You should handle a batch failure by providing user feedback in `\AKlump\Drupal\BatchFramework\BatchDefinitionInterface::handleFailedBatch`

```php
public function handleFailedBatch(array &$batch_data): void {
  
  // Delete the incomplete files created in the batch.
  $service = new FooBarExportService();
  $service->deleteExistingExportFiles($this->account);

  // Tell the user what happened.
  $m = $this->getMessenger();
  $m->addMessage(t('The process has failed, unfortunately.'), MessengerInterface::TYPE_ERROR);
  $m->addMessage(t("We've been notified.  Kindly give us a day or two to work it out."), MessengerInterface::TYPE_STATUS);
  $m->addMessage(t('Thank you for your patience.'), MessengerInterface::TYPE_STATUS);
}
```

## Batches of Batches

Let's say you create a batch of operations that operate on a single user. Call this `UserReviewBatch`. Then you decide you want to be able to process multiple users along the same lines. Let's call this new batch `MultipleUserReviewBatch`. The following shows how to leverage this API to do just that.

* Make sure `UserReviewBatch` properties are protected not private.
* **Be careful with `$this->shared`**.  You will most likely want to empty this array before every new user is processed. That is to say, in the very first operation in `UserReviewBatch`.  `$this->context` is now going to be shared across all operations and so you either need to reset `$this->context['results']['shared']` (what `$this->shared` references) or key/scope that very carefully. See `DrupalBatchAPIOperationBase::setBatchContext` for more info.
* Do not use `\AKlump\Drupal\BatchFramework\Traits\GetLabelByClassnameTrait` in `UserReviewBatch` but instead do this:

   ```php
   public function getLabel(): string {
       return (new CreateLabelByClass())(self::class);
   }
   ```

Here is `MultipleUserReviewBatch`:

```php
class MultipleUserReviewBatch extends UserReviewBatch {

  /**
   * @var int[]
   */
  private array $uids = [];

  public function __construct(array $uids) {
    $this->uids = $uids;
  }

  /**
   * @inheritDoc
   */
  public function getOperations(): array {
    $operations = [];
    $accounts = user_load_multiple($this->uids);
    foreach ($accounts as $account) {
      // Push operations for this account onto the others.
      $this->account = $account;
      $account_operations = parent::getOperations();
      $operations = array_merge($operations, $account_operations);
    }
    return $operations;
  } 

}
```
