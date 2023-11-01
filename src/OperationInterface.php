<?php

namespace AKlump\Drupal\BatchFramework;

/**
 * Batch operation classes may use this interface.
 *
 * @see \Drupal\ova_storage_migrate\Migrate\MigrateUserOperation
 */
interface OperationInterface {

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
   *
   * @see \Drupal\ova_storage_migrate\Batch\OperationInterface::onFail()
   */
  public function initialize(): void;

  /**
   * Indicate how much processing remains.
   *
   * @return float
   *   A number from 0 to 1 indicating how close the process is to completion.
   *   1 means the process is complete.
   *
   * If the operation should be skipped due to previous batch failure, you
   * may include this code in the start of this method of your class.
   * @code
   * if (\AKlump\Drupal\BatchFramework\Operator::hasBatchFailed($batch_context)) {
   *   return 1;
   * }
   * @endcode
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
   * For user messages to the UI, use a MessengerInterface instance.
   *
   * @return string
   *   A message to echo to the UI or ''.
   *
   * @throws \AKlump\Drupal\BatchFramework\BatchFailedException
   *   To indicate the operation failed and no more operations should run.
   *   ::finish on the active operation will NOT be called.
   * @throws \AKlump\Drupal\BatchFramework\BatchFailedException
   *   To indicate the operation failed, but the batch should continue.
   *   ::finish() on the active operation will still be called.
   */
  public function process(): string;

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
