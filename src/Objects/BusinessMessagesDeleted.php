<?php

namespace Telegram\Bot\Objects;

/**
 * Class BusinessMessagesDeleted.
 *
 * This object is received when messages are deleted from a connected business account.
 *
 * @property string     $businessConnectionId  Unique identifier of the business connection.
 * @property Chat       $chat                  Information about a chat in the business account.
 * @property int[]      $messageIds            The list of identifiers of deleted messages in the chat of the business account.
 */
class BusinessMessagesDeleted extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'chat' => Chat::class,
        ];
    }
}
