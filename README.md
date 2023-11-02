# Drupal Batch Framework (A Drupal Component)

[Read more about Drupal Components.](https://www.drupal.org/docs/core-modules-and-themes/basic-structure-of-drupal#s-drupal-components)

It works with all Drupal versions >= 6

[Drupal 7 Batch API](https://www.drupal.org/docs/7/api/batch-api)

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
   extending `\AKlump\Drupal\BatchFramework\BatchDefinitionBase` or
   implementing `\AKlump\Drupal\BatchFramework\BatchDefinitionInterface`.
1. Create one or more operations by
   implementing `\AKlump\Drupal\BatchFramework\OperationInterface`.
1. Add the operation(s) to your batch class; see
   _examples/BatchDefinitions/FooBatch.php_

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
```

## Batch Definition Example

You may or many not need to pass anything to the class, the constructor is
optional, yet this example shows how it can be done.

```php
<?php

namespace Drupal\my_module\Batch\BatchDefinitions;

final class FooBatch extends \AKlump\Drupal\BatchFramework\DrupalBatchAPIBase {

  use \AKlump\Drupal\BatchFramework\Traits\GetIdByClassnameTrait;

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

See _examples/Operations/BarOperation_

## Start the Batch by Submitting a Form

```php
function some_form_submit_handler(array &$form, FormStateInterface $form_state) {
  // Grab data from the form inputs.
  $account = $form_state->get('account');

  // Identify and configure the batch you want to run.
  $batch = new FooBatch($account);
  $batch->setTitle($this->t('Lorem title'));
  $batch->setInitMessage($this->t('Start your engines...'));

  // Deteremine where the user will be redirected after the batch stops.
  $post_batch_redirect = Url::fromRoute('<front>')->toString();
  $response = $batch->process($post_batch_redirect);
  $form_state->setResponse($response);
}
```

## Execute a Single Operation from a Controller Class

`Operator::handleOperation` is an easy way to leverage your batch operation
outside of a batch. It allows you to trigger a single operation that will run
for a set duration. The second two arguments may be omitted if unnecessary.

```php
class BarController extends ControllerBase {

  public function process(AccountInterface $account) {
    $max_execution_in_seconds = 60;
    Operator::handleOperation(
      new BarOperation(date_create(), $account),
      $max_execution_in_seconds,
      \Drupal::logger('conversions'),
      new DrupalMessengerAdapter(),
    );

    return new RedirectResponse($node->toUrl()->toString());
  }
}
```

## Handling Errors

### Batch Failures

* Each operation may elect to be skipped or not, when that batch has failed by implementing `\OperationInterface::skipOnBatchFailure`.
* If an operation wants to mark the batch failed it should throw `\AKlump\Drupal\BatchFramework\BatchFailedException`
* Any other uncaught exceptions will make their way to Drupal, so be careful about that. It's usually not pretty for the user.
* When an operation throws such exception, it's `\AKlump\Drupal\BatchFramework\OperationInterface::finish` method will be called. See the method for more info.
* The `\AKlump\Drupal\BatchFramework\OperationInterface::finish` may also throw such exception.
* See `\AKlump\Drupal\BatchFramework\Operator::handleOperation` which handles the exception for more info.

### Other Failures

* Operations having errors that do not constitute a batch failure should log them using `::getLogger` and handle the situation.

### UX Best Practices

You should have a final operation in your batch that will handle a batch failure by providing user feedback. Here's an example. Be sure to return `FALSE` for `\OperationInterface::skipOnBatchFailure` for that final operation so it will run.

```php
<?php
class HandleFailure extends \AKlump\Drupal\BatchFramework\OperationBase {

  public function skipOnBatchFailure(): bool {
    return FALSE;
  }

  public function initialize(): void {
    $this->sb['failures'] = $this->getBatchFailures();
    $this->sb['total'] = count($this->sb['failures'] ?? []);
  }

  public function isInitialized(): bool {
    return isset($this->sb['failures']);
  }

  public function getProgressRatio(): float {
    return (new \AKlump\Drupal\BatchFramework\Helpers\GetProgressRatio())($this->sb['total'], count($this->sb['failures']));
  }

  public function process(): void {
    foreach ($this->sb['failures'] as $failure) {
    
      // In reality you would probably not pass the exception message to the user, but clean it up in some way.
      $this->getMessenger()
        ->addMessage($failure->getMessage(), \AKlump\Drupal\BatchFramework\MessengerInterface::TYPE_ERROR);
    }
    unset($this->sb['failures']);
  }
}
```
