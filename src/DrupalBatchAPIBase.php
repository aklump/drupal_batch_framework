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
use ReflectionFunction;
use function batch_process;
use function batch_set;

/**
 * An abstract base class for batch definitions.
 *
 * @see \batch_process()
 * @see \batch_set()
 */
abstract class DrupalBatchAPIBase implements BatchDefinitionInterface {

  use HasDrupalModeTrait;

  protected array $batch = [];

  private ?LoggerInterface $logger = NULL;

  private ?MessengerInterface $messenger = NULL;

  protected ?string $batchProcessingPageUrl = NULL;

  private ?OperationInterface $op = NULL;

  private string $opInLoggerChannel = '';

  /**
   * {@inheritdoc}
   */
  public function setMessenger(MessengerInterface $messenger): self {
    $this->messenger = $messenger;

    return $this;
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
   * @return self
   *
   * @see \batch_process
   */
  public function setBatchProcessingPage($url): self {
    if ($url instanceof Url) {
      $url = $url->toString();
    }
    $this->batchProcessingPageUrl = $url;

    return $this;
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
    $reflect = new ReflectionFunction('\batch_process');
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
  public function setLogger(LoggerInterface $logger): self {
    $this->logger = $logger;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLogger(): LoggerInterface {
    if (!$this->logger) {
      $channel = $this->getLabel();

      $op_label = '';
      if ($this->opInLoggerChannel) {
        $op_label = $this->opInLoggerChannel;
      }
      elseif ($this->op) {
        $op_label = $this->op->getLabel();
      }
      if ($op_label) {
        $channel .= ': ' . $op_label;
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
  public function setTitle($title): self {
    $this->batch['title'] = (string) $title;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setInitMessage($init_message): self {
    $this->batch['init_message'] = (string) $init_message;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setProgressMessage($progress_message): self {
    $this->batch['progress_message'] = (string) $progress_message;

    return $this;
  }

  /**
   * A wrapper for our API batch stop methods.
   *
   * This mutates the results to match our API.  DO NOT CALL THIS METHOD NOR
   * OVERRIDE IT.  Use onBatchFinish() to instead, it's cleaner.
   *
   * @param $success
   * @param $batch_data
   * @param $operations
   *
   * @return void
   *
   * @see callback_batch_finished()
   * @see \AKlump\Drupal\BatchFramework\DrupalBatchAPIBase::handleSuccessfulBatch
   * @see \AKlump\Drupal\BatchFramework\DrupalBatchAPIBase::handleFailedBatch()
   */
  public function finish($success, $batch_data, $operations) {
    // I have found that $success comes as TRUE when it shouldn't, and I don't
    // know why, so I've handled batch success detection on my own further down.

    // Calculate the batch elapsed time.
    $elapsed = '(missing)';
    if (isset($batch_data['start'])) {
      $batch_data['elapsed'] = time() - $batch_data['start'];
      $elapsed = $batch_data['elapsed'] . ' seconds';
    }

    // Base batch status on if any exception was thrown.
    $batch_status = $success && empty($batch_data['exceptions']);

    // This will remove the operation from the logger channel.
    $this->logger = NULL;
    $this->op = NULL;
    $this->opInLoggerChannel = '';

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

      foreach ($batch_data['exceptions'] as $data) {
        // Setting NULL is so that the logger label resets each time, with the
        // current op.
        $this->logger = NULL;
        $this->opInLoggerChannel = $data['op'] ?? '';
        $message = trim(($data['message'] ?? '') . "\n" . ($data['exception_trace'] ?? ''));
        if ($message) {
          $this->getLogger()->error($message);
        }
      }

      $this->logger = NULL;
      $this->opInLoggerChannel = '';
    }
    if (FALSE === $batch_status) {
      $this->handleFailedBatch($batch_data);
    }
    else {
      $this->handleSuccessfulBatch($batch_data);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function handleFailedBatch(array &$batch_data): void {
  }

  /**
   * {@inheritdoc}
   */
  public function handleSuccessfulBatch(array &$batch_data): void {
  }

}
