<?php

namespace AKlump\Drupal\BatchFramework;

use Psr\Log\LoggerInterface;

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
   * Act on when a batch is finished (pass or fail).
   *
   * This will be called if a BatchFailedException is thrown, but all other
   * exceptions will bypass it.
   *
   * @param bool $batch_status
   *   True if all operations have succeeded.
   * @param array &$batch_data
   *
   * @return array
   *   $batch_data with any modifications.
   *
   */
  public function onBatchFinished(bool $batch_status, array &$batch_data): void;

  /**
   * Set the title for the progress page.
   *
   * @param $title
   *
   * @return void
   */
  public function setTitle($title): void;

  /**
   * Set the message displayed while batch is starting up.
   *
   * @param string $init_message
   *
   * @return void
   */
  public function setInitMessage($init_message): void;

  /**
   * Set the progress message
   *
   * @param string $progress_message
   *   Message displayed while processing the batch. Available placeholders are
   * @current, @remaining, @total, @percentage, @estimate and @elapsed.
   *   Defaults to t('Completed @current of @total.').
   *
   * @return void
   */
  public function setProgressMessage($progress_message): void;

  /**
   * @param \Psr\Log\LoggerInterface $logger
   *
   * @return void
   */
  public function setLogger(LoggerInterface $logger): void;

  /**
   * @param \AKlump\Drupal\BatchFramework\MessengerInterface $messenger
   *
   * @return void
   */
  public function setMessenger(MessengerInterface $messenger): void;
}
