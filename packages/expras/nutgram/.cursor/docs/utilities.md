# ExprAs Nutgram Utilities

This document covers utility classes and helper functions available in the ExprAs Nutgram package.

---

## MarkdownFormatter

**Location:** `ExprAs\Nutgram\Utils\MarkdownFormatter`

Utility class for formatting Telegram messages with Markdown. Provides both parsing (from Telegram entities) and creating (for sending messages) capabilities.

### Parsing Telegram Message Entities

#### `formatTextToMarkdownByEntities()`

Converts Telegram message text with MessageEntity objects to Markdown format.

```php
use ExprAs\Nutgram\Utils\MarkdownFormatter;

$text = "Hello World";
$entities = $message->entities; // Array of MessageEntity objects

$formatted = MarkdownFormatter::formatTextToMarkdownByEntities($text, $entities);
// Returns: "**Hello** _World_" (if entities mark bold and italic)
```

**Supported Entity Types:**
- `bold` → `**text**`
- `italic` → `_text_`
- `code` → `` `text` ``
- `pre` → ` ```language\ntext\n``` `
- `text_link` → `[text](url)`
- `text_mention` → `[text](tg://user?id=123)`

---

### Creating Markdown Messages

#### `escapeMarkdownLegacy()`

Escapes special characters for safe use in legacy Markdown (v1) format messages.

**Legacy Markdown Special Characters (only 4):** `_ * ` [ `

```php
use ExprAs\Nutgram\Utils\MarkdownFormatter;

// Escape user input for legacy Markdown
$userInput = "Price: $19.99 (50% off!)";
$safe = MarkdownFormatter::escapeMarkdownLegacy($userInput);
// Returns: "Price: $19.99 \(50% off!\)"

// Use in message with legacy Markdown
$bot->sendMessage(
    text: "User said: *{$safe}*",
    parse_mode: 'Markdown'
);
```

**⚠️ Important Legacy Markdown Limitations:**

1. **Entities cannot be nested** - You cannot combine formatting (e.g., bold + italic)
2. **Limited features** - No support for:
   - Underline
   - Strikethrough
   - Spoiler
   - Blockquote / Expandable blockquote
   - Custom emoji
3. **Escaping rules:**
   - Only escape `_ * ` [ ` **outside** entities by prepending `\` (per Telegram API docs)
   - **Inside entities, escaping is not allowed** - close and reopen instead:
     - ❌ Wrong: `_snake\_case_` (escaping inside italic entity)
     - ✅ Correct: `_snake_\__case_` (close italic, escape, reopen italic)
     - ❌ Wrong: `*2\*2=4*` (escaping inside bold entity)
     - ✅ Correct: `*2*\**2=4*` (close bold, escape, reopen bold)

**Note:** Legacy Markdown is deprecated by Telegram. **Use MarkdownV2 for new bots** to avoid these limitations.

#### `escapeMarkdownV2()`

Escapes special characters for safe use in MarkdownV2 format messages.

**MarkdownV2 Special Characters:** `_ * [ ] ( ) ~ ` > # + - = | { } . !`

```php
use ExprAs\Nutgram\Utils\MarkdownFormatter;

// Escape user input for safe MarkdownV2 usage
$userInput = "Price: $19.99 (50% off!)";
$safe = MarkdownFormatter::escapeMarkdownV2($userInput);
// Returns: "Price: $19\.99 \(50% off\!\)"

// Use in message
$bot->sendMessage(
    text: "User said: *{$safe}*",
    parse_mode: 'MarkdownV2'
);
```

**When to Use:**
- ✅ Escaping user-generated content
- ✅ Escaping dynamic data (prices, dates, names)
- ✅ Any text that may contain special characters
- ❌ Don't escape your own Markdown syntax (bold, italic, etc.)

#### `formatUserLink()`

Creates a clickable Telegram user mention link in MarkdownV2 format. Automatically escapes the display name.

```php
use ExprAs\Nutgram\Utils\MarkdownFormatter;

// Create user mention link
$userId = 123456789;
$displayName = "John Doe (Admin)";

$userLink = MarkdownFormatter::formatUserLink($userId, $displayName);
// Returns: "[John Doe \(Admin\)](tg://user?id=123456789)"

// Use in message
$bot->sendMessage(
    text: "New message from {$userLink}",
    parse_mode: 'MarkdownV2'
);
```

**Features:**
- Auto-escapes display name for MarkdownV2 safety
- Creates clickable link to user profile
- Works even if user hasn't started the bot
- Display name can be different from actual Telegram name

---

## Usage Examples

### Example 1: Sending Notification with User Data

```php
use ExprAs\Nutgram\Utils\MarkdownFormatter;

$bot->sendMessage(
    text: sprintf(
        "📝 *New Task Submission*\n\n" .
        "*Task:* %s\n" .
        "*From:* %s\n" .
        "*Comment:* %s",
        MarkdownFormatter::escapeMarkdownV2($task->getName()),
        MarkdownFormatter::formatUserLink($user->getId(), $user->getFullName()),
        MarkdownFormatter::escapeMarkdownV2($submission->getComment())
    ),
    chat_id: $moderatorChatId,
    parse_mode: 'MarkdownV2'
);
```

### Example 2: Building Complex Messages

```php
use ExprAs\Nutgram\Utils\MarkdownFormatter;

// Prepare data
$productName = "T-Shirt (Size: M)";
$price = 1500;
$buyerName = "Alice Smith";
$buyerId = 987654321;

// Build message with proper escaping
$message = sprintf(
    "🛒 *New Order*\n\n" .
    "*Product:* %s\n" .
    "*Price:* %d 💡\n" .
    "*Buyer:* %s\n" .
    "*Status:* %s",
    MarkdownFormatter::escapeMarkdownV2($productName),
    $price, // Numbers don't need escaping
    MarkdownFormatter::formatUserLink($buyerId, $buyerName),
    MarkdownFormatter::escapeMarkdownV2("Pending")
);

$bot->sendMessage(
    text: $message,
    chat_id: $adminChatId,
    parse_mode: 'MarkdownV2'
);
```

### Example 3: Handling Dynamic Lists

```php
use ExprAs\Nutgram\Utils\MarkdownFormatter;

$tasks = [
    ['name' => 'Task #1: Review PR', 'user' => ['id' => 111, 'name' => 'Bob']],
    ['name' => 'Task #2: Fix bug', 'user' => ['id' => 222, 'name' => 'Alice']],
    ['name' => 'Task #3: Deploy', 'user' => ['id' => 333, 'name' => 'Charlie']],
];

$lines = ["📋 *Pending Tasks*\n"];
foreach ($tasks as $i => $task) {
    $lines[] = sprintf(
        "%d\. %s \- %s",
        $i + 1,
        MarkdownFormatter::escapeMarkdownV2($task['name']),
        MarkdownFormatter::formatUserLink($task['user']['id'], $task['user']['name'])
    );
}

$bot->sendMessage(
    text: implode("\n", $lines),
    parse_mode: 'MarkdownV2'
);
```

### Example 4: Service Integration

```php
<?php

namespace App\Service;

use ExprAs\Nutgram\Utils\MarkdownFormatter;
use SergiX44\Nutgram\Nutgram;

class NotificationService
{
    private Nutgram $bot;
    private int $adminChatId;

    public function notifyAdmin(string $title, array $data): void
    {
        $message = sprintf(
            "*%s*\n\n%s",
            MarkdownFormatter::escapeMarkdownV2($title),
            $this->formatData($data)
        );

        $this->bot->sendMessage(
            text: $message,
            chat_id: $this->adminChatId,
            parse_mode: 'MarkdownV2'
        );
    }

    private function formatData(array $data): string
    {
        $lines = [];
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value['user_id'], $value['display_name'])) {
                // Format as user link
                $formatted = MarkdownFormatter::formatUserLink(
                    $value['user_id'],
                    $value['display_name']
                );
            } else {
                // Escape regular text
                $formatted = MarkdownFormatter::escapeMarkdownV2((string) $value);
            }
            
            $lines[] = sprintf(
                "*%s:* %s",
                MarkdownFormatter::escapeMarkdownV2(ucfirst($key)),
                $formatted
            );
        }
        return implode("\n", $lines);
    }
}
```

---

## Best Practices

### ✅ DO

1. **Always escape user input:**
   ```php
   $safe = MarkdownFormatter::escapeMarkdownV2($userInput);
   ```

2. **Use formatUserLink for user mentions:**
   ```php
   $link = MarkdownFormatter::formatUserLink($userId, $displayName);
   ```

3. **Escape dynamic database content:**
   ```php
   MarkdownFormatter::escapeMarkdownV2($product->getName());
   ```

4. **Keep your Markdown syntax unescaped:**
   ```php
   $text = "*Bold* " . MarkdownFormatter::escapeMarkdownV2($content);
   ```

### ❌ DON'T

1. **Don't escape Markdown syntax:**
   ```php
   // ❌ Wrong - escapes the asterisks
   $text = MarkdownFormatter::escapeMarkdownV2("*Bold text*");
   
   // ✅ Correct - keep syntax separate
   $text = "*" . MarkdownFormatter::escapeMarkdownV2("Bold text") . "*";
   ```

2. **Don't forget to escape in concatenation:**
   ```php
   // ❌ Wrong - dynamic content not escaped
   $text = "Price: " . $price . " руб.";
   
   // ✅ Correct - escape the entire string
   $text = MarkdownFormatter::escapeMarkdownV2("Price: {$price} руб.");
   ```

3. **Don't manually escape user links:**
   ```php
   // ❌ Wrong - manual escaping is error-prone
   $name = str_replace('.', '\\.', $user->getName());
   $link = "[{$name}](tg://user?id={$userId})";
   
   // ✅ Correct - use the helper
   $link = MarkdownFormatter::formatUserLink($userId, $user->getName());
   ```

---

## Common Pitfalls

### 1. Forgetting to Escape Periods

```php
// ❌ Wrong - period breaks MarkdownV2
$text = "Price: 19.99 руб.";

// ✅ Correct - escape special chars
$text = MarkdownFormatter::escapeMarkdownV2("Price: 19.99 руб.");
// Returns: "Price: 19\.99 руб\."
```

### 2. Double Escaping

```php
$userName = "John.Doe";

// ❌ Wrong - double escaping
$escaped = MarkdownFormatter::escapeMarkdownV2($userName);
$link = MarkdownFormatter::formatUserLink($userId, $escaped); // Already escaped!

// ✅ Correct - formatUserLink handles escaping
$link = MarkdownFormatter::formatUserLink($userId, $userName);
```

### 3. Mixing Parse Modes

```php
// ❌ Wrong - using MarkdownV2 escaping with Markdown mode
$text = MarkdownFormatter::escapeMarkdownV2("Text");
$bot->sendMessage($text, parse_mode: 'Markdown'); // Won't work correctly

// ✅ Correct - match escape method with parse mode
$text = MarkdownFormatter::escapeMarkdownV2("Text");
$bot->sendMessage($text, parse_mode: 'MarkdownV2');

// ✅ For legacy Markdown
$text = MarkdownFormatter::escapeMarkdownLegacy("Text");
$bot->sendMessage($text, parse_mode: 'Markdown');
```

### 4. Legacy Markdown Entity Escaping

```php
// ❌ Wrong - escaping inside entity
$text = "_file\_name.txt_"; // Breaks in legacy Markdown

// ✅ Correct - close, escape, reopen
$text = "_file_\__name.txt_"; // Works correctly

// ❌ Wrong - trying to bold "C++"
$text = "*C\+\+*"; // Breaks in legacy Markdown

// ✅ Correct - MarkdownV2 handles this properly
$text = "*C\+\+*"; // Use MarkdownV2 parse mode instead
```

---

## Reference

### Method Signatures

```php
namespace ExprAs\Nutgram\Utils;

trait MarkdownFormatter
{
    // Parse Telegram entities to Markdown
    public static function formatTextToMarkdownByEntities(
        string $text,
        array $entities
    ): string;

    // Escape special characters for legacy Markdown (v1)
    public static function escapeMarkdownLegacy(string $text): string;

    // Escape special characters for MarkdownV2
    public static function escapeMarkdownV2(string $text): string;

    // Format user mention link (auto-escaped for MarkdownV2)
    public static function formatUserLink(
        int $userId,
        string $displayName
    ): string;
    
    // Convert UTF-16 offset to UTF-8 (internal use)
    private static function utf16ToUtf8Offset(
        string $text,
        int $utf16Offset
    ): int;
    
    // Get formatted text from Message object
    public function getFormattedTextFromMessage(Message $message): string;
}
```

### Special Characters Reference

#### Legacy Markdown (v1) Special Characters

According to Telegram Bot API, only **4 characters** need escaping:

| Character | Escaped | Usage Context |
|-----------|---------|---------------|
| `_` | `\_` | Italic formatting |
| `*` | `\*` | Bold formatting |
| `` ` `` | `` \` `` | Code formatting |
| `[` | `\[` | Link text start |

**⚠️ Legacy Markdown Limitations:**
- **No nested entities** (can't combine bold + italic)
- **No underline, strikethrough, spoiler, blockquote, custom emoji**
- **Can't escape inside entities** - must close entity, escape, then reopen
- **Deprecated by Telegram** - use MarkdownV2 for new bots

**Example of entity reopening:**
```php
// For italic "snake_case":
$text = "_snake_\__case_";  // Close italic, escape _, reopen italic

// For bold "2*2=4":
$text = "*2*\**2=4*";  // Close bold, escape *, reopen bold
```

#### MarkdownV2 Special Characters

| Character | Escaped | Usage Context |
|-----------|---------|---------------|
| `_` | `\_` | Italic formatting |
| `*` | `\*` | Bold formatting |
| `[` | `\[` | Link text start |
| `]` | `\]` | Link text end |
| `(` | `\(` | Link URL start |
| `)` | `\)` | Link URL end |
| `~` | `\~` | Strikethrough |
| `` ` `` | `` \` `` | Code formatting |
| `>` | `\>` | Blockquote |
| `#` | `\#` | Headers |
| `+` | `\+` | Lists |
| `-` | `\-` | Lists/minus |
| `=` | `\=` | Headers |
| `\|` | `\|` | Tables |
| `{` | `\{` | Text formatting |
| `}` | `\}` | Text formatting |
| `.` | `\.` | Numeric lists |
| `!` | `\!` | Emphasis |

---

## Related Documentation

- [Handlers Guide](./handlers-guide.md) - Creating and handling bot commands
- [Telegram MarkdownV2 Official Docs](https://core.telegram.org/bots/api#markdownv2-style)

