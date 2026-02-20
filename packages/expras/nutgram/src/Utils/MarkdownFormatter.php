<?php

namespace ExprAs\Nutgram\Utils;

use SergiX44\Nutgram\Telegram\Types\Common\MessageEntity;
use SergiX44\Nutgram\Telegram\Types\Message\Message;

/**
 * Utility class to format Telegram message text with Markdown based on MessageEntity objects.
 */
trait MarkdownFormatter
{
    /**
     * Format the message text with Markdown based on the given entities.
     *
     * @param string $text The original message text.
     * @param MessageEntity[] $entities Array of MessageEntity objects.
     * @return string The formatted text with Markdown.
     */
    public static function formatTextToMarkdownByEntities(string $text, array $entities): string
    {
        $formattedText = $text;

        // Sort entities by offset in descending order to avoid overlapping issues
        usort($entities, fn($a, $b) => $b->offset <=> $a->offset);

        foreach ($entities as $entity) {
            $offset = $entity->offset;
            $length = $entity->length;
            $type = $entity->type->value;

            $startTag = '';
            $endTag = '';

            // Convert UTF-16 offsets to UTF-8 byte offsets
            $utf8Offset = self::utf16ToUtf8Offset($formattedText, $offset);
            $utf8Length = self::utf16ToUtf8Offset(mb_substr($formattedText, $utf8Offset), $length);

            switch ($type) {
                case 'bold':
                    $startTag = '**';
                    $endTag = '**';
                    break;
                case 'italic':
                    $startTag = '_';
                    $endTag = '_';
                    break;
                case 'code':
                    $startTag = '`';
                    $endTag = '`';
                    break;
                case 'pre':
                    $startTag = '```' . ($entity->language ?? '') . "\n";
                    $endTag = "\n```";
                    break;
                case 'text_link':
                    $startTag = '[' . mb_substr($formattedText, $utf8Offset, $utf8Length) . '](' . $entity->url . ')';
                    $endTag = '';
                    break;
                case 'text_mention':
                    $startTag = '[' . mb_substr($formattedText, $utf8Offset, $utf8Length) . '](tg://user?id=' . $entity->user->id . ')';
                    $endTag = '';
                    break;
                default:
                    // Skip unsupported entity types
                    continue 2;
            }

            // Apply tags with correct UTF-8 offsets
            $before = mb_substr($formattedText, 0, $utf8Offset);
            $middle = mb_substr($formattedText, $utf8Offset, $utf8Length);
            $after = mb_substr($formattedText, $utf8Offset + $utf8Length);

            $formattedText = $before . $startTag . $middle . $endTag . $after;
        }

        return $formattedText;
    }

    /**
     * Convert UTF-16 code unit offset to UTF-8 byte offset.
     */
    private static function utf16ToUtf8Offset(string $text, int $utf16Offset): int
    {
        $utf8Text = $text;
        $utf16Text = mb_convert_encoding($utf8Text, 'UTF-16', 'UTF-8');
        $utf16Length = mb_strlen($utf16Text, '8bit') / 2; // Each UTF-16 code unit is 2 bytes

        if ($utf16Offset > $utf16Length) {
            return mb_strlen($utf8Text, 'UTF-8');
        }

        $prefix = mb_substr($utf16Text, 0, $utf16Offset * 2, '8bit');
        $prefixUtf8 = mb_convert_encoding($prefix, 'UTF-8', 'UTF-16');
        return mb_strlen($prefixUtf8, 'UTF-8');
    }

    public function getFormattedTextFromMessage(Message $message): string
    {
        $text = $message->text;
        $entities = $message->entities;
        return self::formatTextToMarkdownByEntities($text, $entities);
    }

    /**
     * Escape special characters for legacy Markdown (v1) format
     * 
     * According to Telegram Bot API, only these 4 characters need escaping: _ * ` [
     * 
     * IMPORTANT LIMITATIONS:
     * - Entities cannot be nested (no bold + italic)
     * - No underline, strikethrough, spoiler, blockquote, custom emoji
     * - Cannot escape INSIDE entities - must close entity, escape, then reopen
     *   Example for italic "snake_case": _snake_\__case_ (not _snake\_case_)
     * - This function escapes content for INSERTION, not entire formatted strings
     * 
     * Note: Legacy Markdown is deprecated. Use MarkdownV2 for new bots.
     * 
     * @param string $text Text to escape (user input, not formatted text)
     * @return string Escaped text safe for legacy Markdown
     */
    public static function escapeMarkdownLegacy(string $text): string
    {
        $specialChars = ['_', '*', '`', '['];
        foreach ($specialChars as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }
        return $text;
    }

    /**
     * Escape special characters for MarkdownV2 format
     * 
     * MarkdownV2 requires escaping these characters: _ * [ ] ( ) ~ ` > # + - = | { } . !
     * 
     * @param string $text Text to escape
     * @return string Escaped text safe for MarkdownV2
     */
    public static function escapeMarkdownV2(string $text): string
    {
        $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
        foreach ($specialChars as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }
        return $text;
    }

    /**
     * Format a Telegram user as a MarkdownV2 mention link
     * 
     * Creates a clickable link: [Name](tg://user?id=123)
     * The user's name is automatically escaped for MarkdownV2 safety.
     * 
     * @param int $userId Telegram user ID
     * @param string $displayName Display name for the link
     * @return string Formatted MarkdownV2 user mention link
     */
    public static function formatUserLink(int $userId, string $displayName): string
    {
        $escapedName = self::escapeMarkdownV2(trim($displayName));
        return sprintf('[%s](tg://user?id=%d)', $escapedName, $userId);
    }
}
