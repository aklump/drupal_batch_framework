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
  public function getLabel(): string;

  /**
   * Get operation dependencies
   *
   * If this operation must be run AFTER another, you must declare the other as
   * a dependency.  If this operation runs before any of it's dependencies, a
   * \AKlump\Drupal\BatchFramework\MissingDependencyException will be thrown.
   *
   * @return array
   *   Operation classnames that are required to run before this one.
   *
   * @throws \AKlump\Drupal\BatchFramework\UnmetDependencyException
   *   If run before all dependencies have finished.
   */
  public function getDependencies(): array;

  /**
   * Set the batch context.
   *
   * @param array &$batch_context
   *
   * @return mixed
   */
  public function setBatchContext(array &$batch_context);

  /**
   * Get batch failures.
   *
   * @return array
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
   * Use ::setCurrentActivityMessage() or ::clearCurrentActivityMessage for messaging.
   *
   * @return void.
   * @see \AKlump\Drupal\BatchFramework\OperationInterface::setCurrentActivityMessage();
   * @see \AKlump\Drupal\BatchFramework\OperationInterface::clearCurrentActivityMessage();
   */
  public function process(): void;

  /**
   * Give a message as to the progress or current task of the operation.
   *
   * This should changes frequently to give the user a sense of moving through
   * the batch, so they know what's happening. This is separate from
   * BatchDefinitionInterface::setProgressMessage which is a message indicating
   * the progress of all operations.
   *
   * @param string $message
   * @param array $context
   *   An array of key/valus that will be replaced in $message.
   *
   * @return void
   *
   * @see \AKlump\Drupal\BatchFramework\BatchDefinitionInterface::setProgressMessage
   */
  public function setCurrentActivityMessage(string $message, array $context = []): void;

  /**
   * Clear any existing current activity message.
   *
   * @return void
   */
  public function clearCurrentActivityMessage(): void;

  /**
   * Run quick tasks after processing is completed.
   *
   * Do not do anything in this method that will take longer than a couple of
   * seconds; longer processes should be in the ::process() method or a separate
   * Operation class.
   *
   * This method will be called if the process completes successfully OR if an
   * exception was thrown from this same class.
   */
  public function finish(): void;

}
