<?php

namespace AKlump\Drupal\BatchFramework\Batch;

use AKlump\Drupal\BatchFramework\Adapters\MessengerInterface;
use AKlump\Drupal\BatchFramework\Helpers\CreateLabelByClass;
use AKlump\Drupal\BatchFramework\Helpers\GetLogger;
use AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;
use Drupal;
use function t;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Psr\Log\LoggerInterface;

abstract class DrupalBatchAPIOperationBase implements OperationInterface {

  use HasDrupalModeTrait;

  protected array $context = [];

  protected array $sb = [];

  /**
   * @var array This is shared across all operations.
   */
  protected array $shared = [];

  /**
   * @return string
   *   A string generated from the class name.
   */
  public function getLabel(): string {
    return (new CreateLabelByClass())($this);
  }

  public function getDependencies(): array {
    return [];
  }

  public function getLoggerChannel(): string {
    return $this->context['logger_channel'] ?? $this->getLabel();
  }

  /**
   * @inheritDoc
   */
  public function getLogger(): LoggerInterface {
    $mode = $this->getDrupalMode();
    $channel = $this->getLoggerChannel();

    return (new GetLogger($mode))($channel);
  }

  /**
   * @inheritDoc
   */
  public function getMessenger(): MessengerInterface {
    return $this->context['messenger'];
  }

  /**
   * @inheritDoc
   */
  public function setBatchContext(array &$batch_context): self {
    $this->context = &$batch_context;
    $batch_context += ['sandbox' => [], 'results' => []];
    $batch_context['results'] += ['shared' => []];
    $this->sb = &$batch_context['sandbox'];
    $this->shared = &$batch_context['results']['shared'];

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBatchFailures(): array {
    return $this->context['results']['exceptions'] ?? [];
  }

  /**
   * @inheritDoc
   */
  public function initialize(): void {

  }

  /**
   * @inheritDoc
   */
  public function finish(): void {

  }

  public function setCurrentActivityMessage(string $message, array $context = []): void {
    if ($this->getDrupalMode()->isModern()) {
      $message = (string) (new TranslatableMarkup($message, $context));
    }
    else {
      $message = t($message, $context);
    }

    $this->context['results']['current_activity_message'] = $message;
  }

  public function clearCurrentActivityMessage(): void {
    $this->context['results']['current_activity_message'] = '';
  }

}
