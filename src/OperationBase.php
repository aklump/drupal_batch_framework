<?php

namespace AKlump\Drupal\BatchFramework;

use AKlump\Drupal\BatchFramework\Traits\GetIdByClassnameTrait;
use Psr\Log\LoggerInterface;

abstract class OperationBase implements OperationInterface {

  use GetIdByClassnameTrait;

  protected array $context = [];

  protected array $sb = [];

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
    $batch_context += ['sandbox' => []];
    $this->sb = &$batch_context['sandbox'];
  }

  /**
   * @inheritDoc
   */
  public function finish(): void {

  }

  public function setUserMessage(string $message): void {
    $this->context['message'] = $message;
  }

  public function clearUserMessage(): void {
    $this->context['message'] = '';
  }

}
