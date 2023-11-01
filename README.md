# Drupal Batch Framework (A Drupal Component)

[Read more about Drupal Components.](https://www.drupal.org/docs/core-modules-and-themes/basic-structure-of-drupal#s-drupal-components

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
      ],
       "extra": {
        "installer-paths": {
            "web/components/custom/{$name}": [
                "type:drupal-custom-component"
            ]
        }
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

## Start a Batch (of Operations) by Submitting a Form

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
class FooController extends ControllerBase {

  public function convert(NodeInterface $node) {
    $max_execution_in_seconds = 60;
    Operator::handleOperation(
      new ConvertUserCollectionsOperation([$node->id()]),
      $max_execution_in_seconds,
      \Drupal::logger('conversions'),
      new DrupalMessengerAdapter(),
    );

    return new RedirectResponse($node->toUrl()->toString());
  }
}
```
