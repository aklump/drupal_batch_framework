<?php

namespace AKlump\Drupal\BatchFramework;

use AKlump\Drupal\BatchFramework\Helpers\LegacyDrupalLoggerAdapter;
use AKlump\Drupal\BatchFramework\Helpers\LegacyDrupalMessengerAdapter;
use Drupal;
use Drupal\Core\Messenger\Messenger;
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
      if (class_exists(Messenger::class)) {
        $this->messenger = new DrupalMessengerAdapter();
      }
      elseif (function_exists('drupal_set_message')) {
        $this->messenger = new LegacyDrupalMessengerAdapter();
      }
      else {
        throw new \RuntimeException(sprintf('Cannot find a suitable class implementing %s.', MessengerInterface::class));
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

    $this->batch['finished'] = [$this, 'onBatchFinished'];
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
        $channel .= '.' . $this->op->getLabel();
      }
      if (class_exists(Drupal::class)) {
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
   * {@inheritdoc}
   */
  public function onBatchFinished(bool $batch_status, array $batch_data): void {
    $elapsed = time() - $batch_data['start'];
    $elapsed = "$elapsed seconds";

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
    }
  }

}
