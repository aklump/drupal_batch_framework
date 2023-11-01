<?php

namespace AKlump\Drupal\BatchFramework;

use Drupal;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;
use AKlump\Drupal\BatchFramework\Helpers\DrupalMessengerAdapter;

/**
 * An abstract base class for batch definitions.
 *
 * @see \batch_process()
 * @see \batch_set()
 */
abstract class DrupalBatchAPIBase implements BatchDefinitionInterface {

  protected array $batch = [];

  protected ?LoggerInterface $logger = NULL;

  protected ?MessengerInterface $messenger = NULL;

  protected ?Url $batchProcessingPageUrl = NULL;

  private ?OperationInterface $op = NULL;

  /**
   * {@inheritdoc}
   */
  public function setMessenger(MessengerInterface $messenger): void {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public function getMessenger(): MessengerInterface {
    if (!isset($this->messenger)) {
      $this->messenger = new DrupalMessengerAdapter();
    }

    return $this->messenger;
  }


  /**
   * Set optional URL of the batch processing page.
   *
   * This is specific to Drupal and is not a part of
   * \AKlump\Drupal\BatchFramework\BatchDefinitionInterface; however it may be
   * needed for some Drupal use cases.
   *
   * @param \Drupal\Core\Url|string $url
   * (optional) URL of the batch processing page. Should only be used for
   * separate scripts like update.php.
   *
   * @return void
   *
   * @see \batch_process
   */
  public function setBatchProcessingPage($url): void {
    $this->batchProcessingPageUrl = $url;
  }

  /**
   * {@inheritdoc}
   */
  public function process(string $redirect = NULL, $redirect_callback = NULL) {
    $this->batch['operations'] = array_map(function (OperationInterface $op) {
      $this->op = $op;

      return [
        [Operator::class, 'handleOperation'],
        [
          // TODO It's possible we should be sending classname, not instance to avoid serialization issues.  Needs more testing.
          $op,
          3,
          $this->getLogger(),
          $this->getMessenger(),
        ],
      ];
    }, $this->getOperations());
    batch_set($this->batch);

    return batch_process($redirect, $this->batchProcessingPageUrl, $redirect_callback);
  }

  /**
   * {@inheritdoc}
   */
  public function setLogger(LoggerInterface $logger): void {
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function getLogger(): LoggerInterface {
    if (!$this->logger) {
      $channel = $this->getId();
      if ($this->op) {
        $channel .= '.' . $this->op->getId();
      }
      $this->logger = Drupal::service('logger.factory')->get($channel);
    }

    return $this->logger;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title): void {
    $this->batch['title'] = $title;
  }

  /**
   * {@inheritdoc}
   */
  public function setInitMessage($init_message): void {
    $this->batch['init_message'] = $init_message;
  }

  /**
   * {@inheritdoc}
   */
  public function setProgressMessage($progress_message): void {
    $this->batch['progress_message'] = $progress_message;
  }

}
