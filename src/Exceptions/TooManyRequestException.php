<?php

namespace Telegram\Bot\Exceptions;

use Telegram\Bot\Exceptions\TelegramResponseException;

class TooManyRequestException extends TelegramResponseException
{
    public function getTtl(): int
    {
        return $this->getResponseData()['parameters']['retry_after'];
    }
}
