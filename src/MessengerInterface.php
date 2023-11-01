<?php

namespace AKlump\Drupal\BatchFramework;

/**
 * Add messages that will be shown to the user.
 */
interface MessengerInterface {

  /**
   * A status message.
   */
  const TYPE_STATUS = 'status';

  /**
   * A warning.
   */
  const TYPE_WARNING = 'warning';

  /**
   * An error.
   */
  const TYPE_ERROR = 'error';

  /**
   * Adds a new message to the queue.
   *
   * The messages will be displayed in the order they got added later.
   *
   * @param string $message
   *   (optional) The translated message to be displayed to the user. For
   *   consistency with other messages, it should begin with a capital letter
   *   and end with a period.
   * @param string $type
   *   (optional) The message's type. Either self::TYPE_STATUS,
   *   self::TYPE_WARNING, or self::TYPE_ERROR.
   * @param bool $repeat
   *   (optional) If this is FALSE and the message is already set, then the
   *   message won't be repeated. Defaults to FALSE.
   *
   * @return $this
   */
  public function addMessage(string $message, $type = self::TYPE_STATUS, $repeat = FALSE);

}
