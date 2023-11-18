<?php

namespace AKlump\Drupal\BatchFramework;

use AKlump\Drupal\BatchFramework\Adapters\MessengerInterface;

interface BatchDefinitionInterface extends HasLoggerInterface, HasMessengerInterface {

  /**
   * @return string
   *   A unique string to identify this, such as for log entries.
   */
  public function getLabel(): string;

  /**
   * Get the operations that make up this batch.
   *
   * @return \AKlump\Drupal\BatchFramework\OperationInterface[]
   */
  public function getOperations(): array;

  /**
   * Processes the batch.
   *
   * This function is generally not needed in form submit handlers;
   * Form API takes care of batches that were set during form submission.
   *
   * @param string $redirect
   *   (optional) Either a path or Url object to redirect to when the batch has
   *   finished processing. For example, to redirect users to the home page,
   *   use
   *   '<front>'. If you wish to allow standard form API batch handling to
   *   occur
   *   and force the user to be redirected to a custom location after the batch
   *   has finished processing, you do not need to use batch_process() and this
   *   parameter. Instead, make the batch 'finished' callback return an
   *   instance
   *   of \Symfony\Component\HttpFoundation\RedirectResponse, which will be
   *   used
   *   automatically by the standard batch processing pipeline (and which takes
   *   precedence over this parameter). If this parameter is omitted and no
   *   redirect response was returned by the 'finished' callback, the user will
   *   be redirected to the page that started the batch. Any query arguments
   *   will be automatically persisted.
   * @param $redirect_callback
   *   (optional) Specify a function to be called to redirect to the
   *   progressive
   *   processing page.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|NULL
   *   A redirect response if the batch is progressive. No return value
   *   otherwise.
   */
  public function process(string $redirect = NULL, $redirect_callback = NULL);

  /**
   * Called when a batch has failed.
   *
   * This is called even when exceptions are thrown.
   *
   * @param array &$batch_data
   *
   * @return void
   */
  public function handleFailedBatch(array &$batch_data): void;

  /**
   * Called when all operation have reported success and the batch is done.
   *
   * @param array $batch_data
   *
   * @return void
   */
  public function handleSuccessfulBatch(array &$batch_data): void;

  /**
   * Set the title for the progress page.
   *
   * @param $title
   *
   * @return self
   */
  public function setTitle($title): self;

  /**
   * Set the message displayed while batch is starting up.
   *
   * @param string $init_message
   *
   * @return self
   */
  public function setInitMessage($init_message): self;

  /**
   * Set the progress message
   *
   * @param string $progress_message
   *   Message displayed while processing the batch. Available placeholders are
   * @current, @remaining, @total, @percentage, @estimate and @elapsed.
   *   Defaults to t('Completed @current of @total.').
   *
   * @return self
   */
  public function setProgressMessage($progress_message): self;

  /**
   * @param \AKlump\Drupal\BatchFramework\Adapters\MessengerInterface $messenger
   *
   * @return self
   */
  public function setMessenger(MessengerInterface $messenger): self;
}
