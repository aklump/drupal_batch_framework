<?php

namespace AKlump\Drupal\BatchFramework;

/**
 * Batch operation classes may use this interface.
 */
interface OperationInterface extends HasLoggerInterface, HasMessengerInterface {

  /**
   * @return string
   *   A unique string to identify this, such as for log entries.
   */
  public function getId(): string;

  /**
   * Set the batch context.
   *
   * @param array &$batch_context
   *
   * @return mixed
   */
  public function setBatchContext(array &$batch_context);

  /**
   * @return bool
   *   If this operation should not run when the batch has failed, return true.
   */
  public function skipOnBatchFailure(): bool;

  /**
   * Get batch failures.
   *
   * @return \AKlump\Drupal\BatchFramework\BatchFailedException[]
   *   Contains any errors to this point across all operations in the batch.
   */
  public function getBatchFailures(): array;

  /**
   * Has the operation been initialized yet.
   *
   * @return bool
   */
  public function isInitialized(): bool;

  /**
   * Initialize the operation.
   *
   * @return void
   *
   * @throws \AKlump\Drupal\BatchFramework\BatchFailedException
   *   For any reason that indicates initialization failed.
   */
  public function initialize(): void;

  /**
   * Indicate how much processing remains.
   *
   * @return float
   *   From 0 to 1 indicating how close the process is to completion, where 1 is
   *   100% complete.
   * @see \AKlump\Drupal\BatchFramework\Helpers\GetProgressRatio
   */
  public function getProgressRatio(): float;

  /**
   * Do the process.
   *
   * This should perform the smallest chunk as possible, such as a single item
   * so that the managing process can control the elapsed time limitations.
   *
   * For error conditions pick an appropriate exception as listed below.  All
   * exceptions that are thrown will be added to to the watchdog table
   * automatically.
   *
   * Use ::setProgressUpdateMessage() or ::clearUserMessage for messaging.
   *
   * @return void.
   * @throws \AKlump\Drupal\BatchFramework\BatchFailedException
   *   To indicate the operation failed and no more operations should run.
   *   ::finish on the active operation will NOT be called.
   * @throws \AKlump\Drupal\BatchFramework\BatchFailedException
   *   To indicate the operation failed, but the batch should continue.
   *   ::finish() on the active operation will still be called.
   * @see \AKlump\Drupal\BatchFramework\OperationInterface::setProgressUpdateMessage();
   * @see \AKlump\Drupal\BatchFramework\OperationInterface::clearUserMessage();
   */
  public function process(): void;

  public function setProgressUpdateMessage(string $message, array $context = []): void;

  public function clearUserMessage(): void;

  /**
   * Run quick tasks after processing is completed.
   *
   * Do not do anything in this method that will take longer than a couple of
   * seconds; longer processes should be in the ::process() method or a separate
   * Operation class.
   *
   * This method will be called if the process completes successfully OR if an
   * BatchFailedException exception was thrown from this same class..  In
   * the case of the latter, the exception will be available in the context; see
   * \AKlump\Drupal\BatchFramework\BatchFailedException for variable names.
   *
   * When handling errors in this method, you should not throw an
   * BatchFailedException as it will not be handled correctly; that is it
   * will not call ::finish().  You may throw a BatchFailedException.
   *
   * @throws \AKlump\Drupal\BatchFramework\BatchFailedException
   *   To indicate the operation failed and no more operations should run.
   *   ::finish on the active operation will NOT be called.
   */
  public function finish(): void;

}
