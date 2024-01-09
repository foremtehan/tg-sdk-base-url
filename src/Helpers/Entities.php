<?php

namespace Telegram\Bot\Helpers;

/**
 * Class Entities.
 */
class Entities
{
    /** @var string Message or Caption */
    protected $text;
    /** @var array Entities from Telegram */
    protected $entities;
    /** @var int Formatting Mode: 0:Markdown | 1:HTML */
    protected $mode = 0;

    /**
     * Entities constructor.
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @param string $text
     * @return static
     */
    public static function format(string $text): self
    {
        return new static($text);
    }

    /**
     * @param array $entities
     * @return $this
     */
    public function withEntities(array $entities): self
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * Format it to markdown style.
     * @return string
     */
    public function toMarkdown(): string
    {
        $this->mode = 0;

        return $this->apply();
    }

    /**
     * Format it to HTML syntax.
     * @return string
     */
    public function toHTML(): string
    {
        $this->mode = 1;

        return $this->apply();
    }

    /**
     * Apply format for given text and entities.
     * @return mixed|string
     */
    protected function apply()
    {
        $syntax = $this->syntax();

        foreach (array_reverse($this->entities) as $entity) {
            $value = mb_substr($this->text, $entity['offset'], $entity['length']);
            $type = $entity['type'];
            $replacement = match ($type) {
                'text_link' => sprintf($syntax[$type][$this->mode], $value, $entity['url']),
                'text_mention' => sprintf($syntax[$type][$this->mode], $entity['user']['username']),
                default => sprintf($syntax[$type][$this->mode], $value),
            };

            $this->text = $this->substrReplace($this->text, $replacement, $entity['offset'], $entity['length']);
        }

        return $this->text;
    }

    function substrReplace($string, $replacement, $start, $length = null, $encoding = null)
    {
        $string_length = (is_null($encoding) === true) ? mb_strlen($string) : mb_strlen($string, $encoding);

        if ($start < 0) {
            $start = max(0, $string_length + $start);
        } else if ($start > $string_length) {
            $start = $string_length;
        }

        if ($length < 0) {
            $length = max(0, $string_length - $start + $length);
        } else if ((is_null($length) === true) || ($length > $string_length)) {
            $length = $string_length;
        }

        if (($start + $length) > $string_length) {
            $length = $string_length - $start;
        }

        if (is_null($encoding) === true) {
            return mb_substr($string, 0, $start).$replacement.mb_substr($string, $start + $length, $string_length - $start - $length);
        }

        return mb_substr($string, 0, $start, $encoding).$replacement.mb_substr($string, $start + $length, $string_length - $start - $length, $encoding);
    }

    /**
     * Formatting Syntax.
     * @return array
     */
    protected function syntax(): array
    {
        // No need of any special formatting for these entity types.
        // 'url', 'bot_command', 'hashtag', 'cashtag', 'email', 'phone_number', 'mention'

        return [
            'bold' => ['*%s*', '<strong>%s</strong>'],
            'italic' => ['_%s_', '<i>%s</i>'],
            'code' => ['`%s`', '<code>%s</code>'],
            'pre' => ["```\n%s```", '<pre>%s</pre>'],
            'spoiler' => ['||%s||', '<tg-spoiler>%s</tg-spoiler>'],
            'text_mention' => ['[%1$s](tg://user?id=%1$s)', '<a href="tg://user?id=%1$s">%1$s</a>'],
            'text_link' => ['[%s](%s)', '<a href="%2$s">%1$s</a>'],
            'underline' => ['_%s_', '<u>%s</u>'],
            'strikethrough' => ['~%s~', '<s>%s</s>'],
            'block_quote' => ['>%s', '<blockquote>%s</blockquote>'],
        ];
    }
}
