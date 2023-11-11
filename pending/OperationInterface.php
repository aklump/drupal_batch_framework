<?php

use Drupal\Core\Messenger\MessengerInterface;
use Psr\Log\LoggerInterface;

/**
 * Batch operation classes may use this interface.
 *
 * Avoid constructor arguments for these classes, or at least be careful with
 * them, as they should not be services due to the serialization problem of the
 * Batch API with services that reference the database container.  Instead you
 * should implement the ContainerInjectionInterface on the class and use the
 * ::create method to set services as class properties.  However, use the newer
 * style where you add properties in ::create and not in ::__construct.
 * Otherwise you're back to square one.  See code example.
 *
 * @code
 * final class FooOperation implements OperationInterface, \Drupal\Core\DependencyInjection\ContainerInjectionInterface {
 *
 *   private $nodeStorage;
 *
 *   public static function create(ContainerInterface $container) {
 *     $obj = new self();
 *
 *     // Notice that we set this as private class property!  You could also use
 *     a setter if you feel better doing that.
 *     $obj->nodeStorage = $container->get('entity_type.manager')
 *       ->getStorage('node');
 *
 *     return $obj;
 *   }
 * }
 * @endcode
 *
 * @see \Drupal\Core\Database\Connection::__sleep
 */
interface OperationInterface {

  /**
   * Gets the unique identifier of the operation.
   *
   * @return string
   *   The unique identifier of the operation.
   */
  public function id();

  /**
   * Sets the messenger.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   *
   * @see \Drupal\Core\Messenger\MessengerTrait
   */
  public function setMessenger(MessengerInterface $messenger);

  /**
   * Gets the messenger.
   *
   * @return
   *   The messenger.
   *
   * @see \Drupal\Core\Messenger\MessengerTrait
   */
  public function messenger();

  /**
   * Set the batch context.
   *
   * @param array &$batch_context
   *
   * @return mixed
   */
  public function setBatchContext(array &$batch_context);

  /**
   * Set the logger channel to be used by the operation.
   *
   * All messages for a given operation should be logged to the same channel.
   * Convention says that channel should be "$batch_id.$operation_id".
   *
   * @param \Psr\Log\LoggerInterface $logger
   *
   * @return mixed
   */
  public function setLogger(LoggerInterface $logger);

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
   * @throws \Drupal\gop_2101\Batch\BatchFailedException
   *   For any reason that indicates initialization failed.
   *
   * @see \Drupal\gop3_core\Batch\OperationInterface::onFail()
   */
  public function initialize(): void;

  /**
   * Indicate how much processing remains.
   *
   * @return float
   *   A number from 0 to 1 indicating how close the process is to completion.
   *   1 means the process is complete.
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
   * @throws \Drupal\gop3_core\Batch\BatchFailedException
   *   To indicate the operation failed and no more operations should run.
   *   ::finish on the active operation will NOT be called.
   * @throws \Drupal\gop3_core\Batch\BatchFailedException
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
   * This method will be called if the process completes successfully OR if a
   * BatchFailedException exception was thrown from this same class.  In
   * the case of the latter, the exception will be available in the context; see
   * \Drupal\gop3_core\Batch\BatchFailedException for variable names.
   *
   * @throws \Drupal\gop3_core\Batch\BatchFailedException
   *   To indicate the operation failed and no more operations should run.
   *   ::finish on the active operation will NOT be called.
   */
  public function finish(): void;

}
