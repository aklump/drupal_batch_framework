<?php

namespace AKlump\Drupal\BatchFramework;

use AKlump\Drupal\BatchFramework\Traits\GetLabelByClassnameTrait;
use Psr\Log\LoggerInterface;

abstract class OperationBase implements OperationInterface {

  use GetLabelByClassnameTrait;

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
  public function skipOnBatchFailure(): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getBatchFailures(): array {
    return $this->context['results']['batch_failed_exceptions'] ?? [];
  }

  /**
   * @inheritDoc
   */
  public function finish(): void {

  }

  public function setProgressUpdateMessage(string $message, array $context = []): void {
    if (function_exists('t')) {
      $this->context['message'] = t($message, $context);

      return;
    }
    // TODO Support Drupal 8 better.
    $this->context['message'] = sprintf($message, $context);
  }

  public function clearUserMessage(): void {
    $this->context['message'] = '';
  }

}
