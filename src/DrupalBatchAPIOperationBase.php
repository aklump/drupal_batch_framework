<?php

namespace AKlump\Drupal\BatchFramework;

use AKlump\Drupal\BatchFramework\Traits\GetLabelByClassnameTrait;
use AKlump\Drupal\BatchFramework\Traits\HasDrupalModeTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Psr\Log\LoggerInterface;

abstract class DrupalBatchAPIOperationBase implements OperationInterface {

  use GetLabelByClassnameTrait;
  use HasDrupalModeTrait;

  protected array $context = [];

  protected array $sb = [];

  /**
   * @var array This is shared across all operations.
   */
  protected array $shared = [];

  public function getDependencies(): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getLogger(): LoggerInterface {
    return $this->context['logger'];
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
  public function setBatchContext(array &$batch_context) {
    $this->context = &$batch_context;
    $batch_context += ['sandbox' => [], 'results' => []];
    $batch_context['results'] += ['shared' => []];
    $this->sb = &$batch_context['sandbox'];
    $this->shared = &$batch_context['results']['shared'];
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
