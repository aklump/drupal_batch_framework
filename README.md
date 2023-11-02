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
