<?php

namespace Telegram\Bot\Exceptions;

use Telegram\Bot\TelegramResponse;

/**
 * Class TelegramResponseException.
 */
class TelegramResponseException extends TelegramSDKException
{
    /** @var TelegramResponse The response that threw the exception. */
    protected $response;

    /** @var array Decoded response. */
    protected $responseData;

    /**
     * Creates a TelegramResponseException.
     *
     * @param TelegramResponse $response The response that threw the exception.
     * @param TelegramSDKException $previousException The more detailed exception.
     */
    public function __construct(TelegramResponse $response, TelegramSDKException $previousException = null)
    {
        $this->response = $response;
        $this->responseData = $response->getDecodedBody();

        $errorMessage = $this->get('description', 'Unknown error from API Response.');
        $errorCode = $this->get('error_code', -1);

        parent::__construct($errorMessage, $errorCode, $previousException);
    }

    /**
     * Checks isset and returns that or a default value.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function get($key, $default = null)
    {
        return $this->responseData[$key] ?? $default;
    }

    /**
     * A factory for creating the appropriate exception based on the response from Telegram.
     *
     * @param TelegramResponse $response The response that threw the exception.
     *
     * @return TelegramResponseException
     */
    public static function create(TelegramResponse $response)
    {
        $data = $response->getDecodedBody();

        $message = null;
        if (isset($data['ok'], $data['error_code']) && $data['ok'] === false) {
            $message = $data['description'] ?? 'Unknown error from API.';
        }

        $has = fn(string $words) => str_contains($message, $words);

        $exception = match (true) {
            $has('message to copy not found') => MessageToCopyNotFoundException::class,
            $has('message to edit not found') => MessageToEditNotFoundException::class,
            $has('blocked by the user') || $has('USER_IS_BLOCKED') => BotBlockedByUserException::class,
            $has('user is deactivated') => UserDeactivatedException::class,
            $has('message is too long') || $has('caption is too long') || $has('_TOO_LONG') => TextTooLongException::class,
            $has('Too Many Requests') => TooManyRequestException::class,
            $has('user not found') => UserNotFoundException::class,
            $has('chat not found') || $has('PEER_ID_INVALID') => ChatNotFoundException::class,
            $has('message to delete not found') || $has('deleted for everyone') => MessageToDeleteNotFoundException::class,
            $has('message to forward not found') => MessageToForwardNotFoundException::class,
            $has('query is too old') => QueryOldException::class,
            $has('reply markup are exactly the same') => MessageMarkupIdenticalException::class,
            $has('message to reply not found') || $has("to be replied not found") => MessageToReplyNotFoundException::class,
            $has('message to react not found') => MessageToReactNotFoundException::class,
            $has("can't be forwarded") => MessageCantBeForwardedException::class,
            $has("can't be copied") => MessageCantBeCopiedException::class,
            $has("VOICE_MESSAGES_FORBIDDEN") => VoiceMessageForbiddenException::class,
            $has('no write access') => NoWriteAccessException::class,
            $has('kicked from the supergroup') || $has('kicked from the group') => BotKickedFromGroupException::class,
            $has('kicked from the channel') => BotKickedFromChannelException::class,
            $has('not a member of') => BotNotMemberOfChatException::class,
            $has('QUOTE_TEXT_INVALID') => QuoteInvalidException::class,
            $has('initiate') => BotCantInitiateConversationException::class,
            $has('thread not found') || $has('TOPIC_ID_INVALID') => ThreadNotFoundException::class,
            $has('PARTICIPANT_ID_INVALID') => ParticipantInvalidException::class,
            $has('PEER_ID_INVALID') => PeerInvalidException::class,
            $has('member not found') => MemberNotFoundException::class,
            $has('CHANNEL_FORUM_MISSING') || $has('not a forum') => ForumMissingException::class,
            $has('not enough rights') || $has('member list is inaccessible') || $has('rights in the') => NotEnoughRightsException::class,
            $message == 'Unauthorized' || $has('SESSION_REVOKED') || $has('USER_DEACTIVATED') => UnauthorizedException::class,
            ($data['error_code'] ?? null) >= 500 => TelegramServerErrorException::class,
            default => static::class
        };

        return new $exception($response);
    }

    /**
     * Returns the HTTP status code.
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->response->getHttpStatusCode();
    }

    /**
     * Returns the error type.
     *
     * @return string
     */
    public function getErrorType(): string
    {
        return $this->get('type', '');
    }

    /**
     * Returns the raw response used to create the exception.
     *
     * @return string
     */
    public function getRawResponse(): string
    {
        return $this->response->getBody();
    }

    /**
     * Returns the decoded response used to create the exception.
     *
     * @return array
     */
    public function getResponseData(): array
    {
        return $this->responseData;
    }

    /**
     * Returns the response entity used to create the exception.
     *
     * @return TelegramResponse
     */
    public function getResponse(): TelegramResponse
    {
        return $this->response;
    }
}
