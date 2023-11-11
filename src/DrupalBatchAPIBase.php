<?php

namespace AKlump\Drupal\BatchFramework;

use AKlump\Drupal\BatchFramework\Adapters\DrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalLoggerAdapter;
use AKlump\Drupal\BatchFramework\Adapters\LegacyDrupalMessengerAdapter;
use AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;
use Drupal;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;

/**
 * An abstract base class for batch definitions.
 *
 * @see \batch_process()
 * @see \batch_set()
 */
abstract class DrupalBatchAPIBase implements BatchDefinitionInterface {

  use HasDrupalModeTrait;

  protected array $batch = [];

  protected ?LoggerInterface $logger = NULL;

  protected ?MessengerInterface $messenger = NULL;

  protected ?string $batchProcessingPageUrl = NULL;

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
      if ($this->getDrupalMode()->isModern()) {
        $this->messenger = new DrupalMessengerAdapter();
      }
      else {
        $this->messenger = new LegacyDrupalMessengerAdapter();
      }
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
    if ($url instanceof Url) {
      $url = $url->toString();
    }
    $this->batchProcessingPageUrl = $url;
  }

  /**
   * {@inheritdoc}
   */
  public function process(string $redirect = NULL, $redirect_callback = NULL) {
    $this->batch['operations'] = array_map(function (OperationInterface $op) {
      if (method_exists($op, 'setDrupalMode')) {
        $op->setDrupalMode($this->getDrupalMode());
      }
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

    $this->batch['finished'] = [$this, 'finish'];
    batch_set($this->batch);

    if (function_exists('drupal_goto')) {
      $this->handleLegacyDrupalBatchProcessArgs($redirect, $redirect_callback);
    }

    return batch_process($redirect, $this->batchProcessingPageUrl, $redirect_callback);
  }

  private function handleLegacyDrupalBatchProcessArgs(&$redirect, &$redirect_callback) {
    $reflect = new \ReflectionFunction('\batch_process');
    foreach ($reflect->getParameters() as $key => $param) {
      if (0 === $key) {
        $redirect = $redirect ?? $param->getDefaultValue();
      }
      if (1 === $key) {
        $this->batchProcessingPageUrl = $this->batchProcessingPageUrl ?? $param->getDefaultValue();
      }
      if (2 === $key) {
        $redirect_callback = $redirect_callback ?? $param->getDefaultValue();
      }
    }
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
      $channel = $this->getLabel();
      if ($this->op) {
        $channel .= ': ' . $this->op->getLabel();
      }
      if ($this->getDrupalMode()->isModern()) {
        $this->logger = Drupal::service('logger.factory')->get($channel);
      }
      else {
        $this->logger = new LegacyDrupalLoggerAdapter($channel);
      }
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

  /**
   * A wrapper for our API method onBatchFinished.
   *
   * This mutates the results to match our API.  DO NOT CALL THIS METHOD NOR
   * OVERRIDE IT.  Use onBatchFinish() to instead, it's cleaner.
   *
   * @param $success
   * @param $results
   * @param $operations
   *
   * @return void
   *
   * @see callback_batch_finished()
   */
  public function finish($success, $batch_data, $operations) {
    // I have found that $success comes as TRUE when it shouldn't, and I don't
    // know why, so I've handled batch success detection on my own further down.
    //  I think it's because of how I architected the BatchFailedException bit.

    // Calculate the batch elapsed time.
    $elapsed = '(missing)';
    if (isset($batch_data['start'])) {
      $batch_data['elapsed'] = time() - $batch_data['start'];
      $elapsed = $batch_data['elapsed'] . ' seconds';
    }

    // Base batch status on if any exception was thrown.
    $batch_status = empty($batch_data["batch_failed_exceptions"]);

    // This will remove the operation from the logger channel.
    $this->logger = NULL;
    $this->op = NULL;

    if ($batch_status) {
      $this->getLogger()->info("All batch operations completed in @time.", [
        '@batch' => $this->getLabel(),
        '@time' => $elapsed,
      ]);
    }
    else {
      $this->getLogger()->error("Batch failed (@time elapsed).", [
        '@batch' => $this->getLabel(),
        '@time' => $elapsed,
      ]);

      foreach ($batch_data["batch_failed_exceptions"] as $data) {
        // Setting NULL is so that the logger label resets each time, with the
        // current op.
        $this->logger = NULL;
        list($this->op, $exception) = $data;
        $this->getLogger()->error($exception->getMessage());
      }
    }

    $this->onBatchFinished($batch_status, $batch_data);
  }

  /**
   * {@inheritdoc}
   */
  public function onBatchFinished(bool $batch_status, array &$batch_data): void {
  }

}
