# Developers

A batch contains one or more operations.

* Create a batch class by extending `\AKlump\Drupal\BatchFramework\BatchDefinitionBase` or implementing `\AKlump\Drupal\BatchFramework\BatchDefinitionInterface`.
* Create one or more operations by implementing `\AKlump\Drupal\BatchFramework\OperationInterface`.
* Add the operation(s) to your batch class; see _examples/BatchDefinitions/FooBatch.php_

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

`Operator::handleOperation` is an easy way to leverage your batch operation outside of a batch. It allows you to trigger a single operation that will run for a set duration. The second two arguments may be omitted if unnecessary.

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
