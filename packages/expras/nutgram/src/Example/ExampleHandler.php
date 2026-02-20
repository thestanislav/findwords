<?php

namespace ExprAs\Nutgram\Example;

use ExprAs\Nutgram\SubscriberInterface;
use SergiX44\Nutgram\Nutgram;

/**
 * Example handler demonstrating how to implement SubscriberInterface
 * This shows how to subscribe to various Nutgram events
 */
class ExampleHandler implements SubscriberInterface
{
        public function subscribeToEvents(Nutgram $bot): void
    {
        // Handle text messages
        $bot->onText('hello', fn(Nutgram $bot) => $this->onHelloText($bot));

        // Handle commands
        $bot->onCommand('start', fn(Nutgram $bot) => $this->onStartCommand($bot));
        $bot->onCommand('help', fn(Nutgram $bot) => $this->onHelpCommand($bot));

        // Handle callback queries
        $bot->onCallbackQuery(fn(Nutgram $bot) => $this->onCallbackQuery($bot));

        // Handle specific callback query data
        $bot->onCallbackQueryData('info', fn(Nutgram $bot) => $this->onInfoCallbackQuery($bot));

        // Handle photo messages
        $bot->onPhoto(fn(Nutgram $bot) => $this->onPhotoMessage($bot));

        // Handle document messages
        $bot->onDocument(fn(Nutgram $bot) => $this->onDocumentMessage($bot));

        // Handle location messages
        $bot->onLocation(fn(Nutgram $bot) => $this->onLocationMessage($bot));

        // Handle contact messages
        $bot->onContact(fn(Nutgram $bot) => $this->onContactMessage($bot));

        // Handle voice messages
        $bot->onVoice(fn(Nutgram $bot) => $this->onVoiceMessage($bot));

        // Handle video messages
        $bot->onVideo(fn(Nutgram $bot) => $this->onVideoMessage($bot));

        // Handle sticker messages
        $bot->onSticker(fn(Nutgram $bot) => $this->onStickerMessage($bot));

        // Handle new chat members
        $bot->onNewChatMembers(fn(Nutgram $bot) => $this->onNewChatMembers($bot));

        // Handle left chat members
        $bot->onLeftChatMember(fn(Nutgram $bot) => $this->onLeftChatMember($bot));

        // Handle inline queries
        $bot->onInlineQuery(fn(Nutgram $bot) => $this->onInlineQuery($bot));

        // Handle chosen inline results
        $bot->onChosenInlineResult(fn(Nutgram $bot) => $this->onChosenInlineResult($bot));

        // Handle shipping queries
        $bot->onShippingQuery(fn(Nutgram $bot) => $this->onShippingQuery($bot));

        // Handle pre-checkout queries
        $bot->onPreCheckoutQuery(fn(Nutgram $bot) => $this->onPreCheckoutQuery($bot));

        // Handle successful payments
        $bot->onSuccessfulPayment(fn(Nutgram $bot) => $this->onSuccessfulPayment($bot));

        // Handle polls
        $bot->onUpdatePoll(fn(Nutgram $bot) => $this->onUpdatePoll($bot));

        // Handle poll answers
        $bot->onPollAnswer(fn(Nutgram $bot) => $this->onPollAnswer($bot));

        // Handle chat member updates
        $bot->onMyChatMember(fn(Nutgram $bot) => $this->onMyChatMember($bot));
        $bot->onChatMember(fn(Nutgram $bot) => $this->onChatMember($bot));

        // Handle chat join requests
        $bot->onChatJoinRequest(fn(Nutgram $bot) => $this->onChatJoinRequest($bot));

        // Handle forum topic events
        $bot->onForumTopicCreated(fn(Nutgram $bot) => $this->onForumTopicCreated($bot));
        $bot->onForumTopicClosed(fn(Nutgram $bot) => $this->onForumTopicClosed($bot));
        $bot->onForumTopicReopened(fn(Nutgram $bot) => $this->onForumTopicReopened($bot));

        // Handle video chat events
        $bot->onVideoChatScheduled(fn(Nutgram $bot) => $this->onVideoChatScheduled($bot));
        $bot->onVideoChatStarted(fn(Nutgram $bot) => $this->onVideoChatStarted($bot));
        $bot->onVideoChatEnded(fn(Nutgram $bot) => $this->onVideoChatEnded($bot));

        // Handle web app data
        $bot->onWebAppData(fn(Nutgram $bot) => $this->onWebAppData($bot));

        // Handle story messages
        $bot->onStory(fn(Nutgram $bot) => $this->onStoryMessage($bot));

        // Handle venue messages
        $bot->onVenue(fn(Nutgram $bot) => $this->onVenueMessage($bot));

        // Handle dice messages
        $bot->onDice(fn(Nutgram $bot) => $this->onDiceMessage($bot));

        // Handle game messages
        $bot->onGame(fn(Nutgram $bot) => $this->onGameMessage($bot));

        // Handle invoice messages
        $bot->onInvoice(fn(Nutgram $bot) => $this->onInvoiceMessage($bot));

        // Handle connected website
        $bot->onConnectedWebsite(fn(Nutgram $bot) => $this->onConnectedWebsite($bot));

        // Handle passport data
        $bot->onPassportData(fn(Nutgram $bot) => $this->onPassportData($bot));

        // Handle proximity alert
        $bot->onProximityAlertTriggered(fn(Nutgram $bot) => $this->onProximityAlertTriggered($bot));

        // Handle video chat participants invited
        $bot->onVideoChatParticipantsInvited(fn(Nutgram $bot) => $this->onVideoChatParticipantsInvited($bot));

        // Handle message auto delete timer changes
        $bot->onMessageAutoDeleteTimerChanged(fn(Nutgram $bot) => $this->onMessageAutoDeleteTimerChanged($bot));

        // Handle pinned messages
        $bot->onPinnedMessage(fn(Nutgram $bot) => $this->onPinnedMessage($bot));

        // Handle migrate events
        $bot->onMigrateToChatId(fn(Nutgram $bot) => $this->onMigrateToChatId($bot));
        $bot->onMigrateFromChatId(fn(Nutgram $bot) => $this->onMigrateFromChatId($bot));

        // Handle chat creation events
        $bot->onGroupChatCreated(fn(Nutgram $bot) => $this->onGroupChatCreated($bot));
        $bot->onSupergroupChatCreated(fn(Nutgram $bot) => $this->onSupergroupChatCreated($bot));
        $bot->onChannelChatCreated(fn(Nutgram $bot) => $this->onChannelChatCreated($bot));

        // Handle chat photo changes
        $bot->onNewChatPhoto(fn(Nutgram $bot) => $this->onNewChatPhoto($bot));
        $bot->onDeleteChatPhoto(fn(Nutgram $bot) => $this->onDeleteChatPhoto($bot));

        // Handle chat title changes
        $bot->onNewChatTitle(fn(Nutgram $bot) => $this->onNewChatTitle($bot));

        // Handle edited messages
        $bot->onEditedMessage(fn(Nutgram $bot) => $this->onEditedMessage($bot));

        // Handle channel posts
        $bot->onChannelPost(fn(Nutgram $bot) => $this->onChannelPost($bot));

        // Handle edited channel posts
        $bot->onEditedChannelPost(fn(Nutgram $bot) => $this->onEditedChannelPost($bot));

        // Handle any update (fallback)
        $bot->fallback(fn(Nutgram $bot) => $this->onFallback($bot));

        // Handle exceptions
        $bot->onException(fn(Nutgram $bot, \Throwable $e) => $this->onException($bot, $e));

        // Handle API errors
        $bot->onApiError(fn(Nutgram $bot, \Throwable $e) => $this->onApiError($bot, $e));
    }

    // Text message handlers
    private function onHelloText(Nutgram $bot): void
    {
        $bot->sendMessage('Hello there!');
    }

    // Command handlers
    private function onStartCommand(Nutgram $bot): void
    {
        $bot->sendMessage('Welcome! Use /help for available commands.');
    }

    private function onHelpCommand(Nutgram $bot): void
    {
        $bot->sendMessage('Available commands: /start, /help, /info');
    }

    // Callback query handlers
    private function onCallbackQuery(Nutgram $bot): void
    {
        $bot->answerCallbackQuery('Button clicked!');
    }

    private function onInfoCallbackQuery(Nutgram $bot): void
    {
        $bot->sendMessage('This is an info message from callback query.');
    }

    // Media message handlers
    private function onPhotoMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Nice photo!');
    }

    private function onDocumentMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Document received!');
    }

    private function onLocationMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Location received!');
    }

    private function onContactMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Contact information received!');
    }

    private function onVoiceMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Voice message received!');
    }

    private function onVideoMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Video received!');
    }

    private function onStickerMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Sticker received!');
    }

    // Chat member handlers
    private function onNewChatMembers(Nutgram $bot): void
    {
        $bot->sendMessage('Welcome new members!');
    }

    private function onLeftChatMember(Nutgram $bot): void
    {
        $bot->sendMessage('Member left the chat.');
    }

    // Inline query handlers
    private function onInlineQuery(Nutgram $bot): void
    {
        $bot->answerInlineQuery([
            [
                'type' => 'article',
                'id' => '1',
                'title' => 'Example Result',
                'input_message_content' => [
                    'message_text' => 'This is an example inline result'
                ]
            ]
        ]);
    }

    private function onChosenInlineResult(Nutgram $bot): void
    {
        // Handle chosen inline result
    }

    // Payment handlers
    private function onShippingQuery(Nutgram $bot): void
    {
        $bot->answerShippingQuery(true);
    }

    private function onPreCheckoutQuery(Nutgram $bot): void
    {
        $bot->answerPreCheckoutQuery(true);
    }

    private function onSuccessfulPayment(Nutgram $bot): void
    {
        $bot->sendMessage('Payment successful! Thank you.');
    }

    // Poll handlers
    private function onUpdatePoll(Nutgram $bot): void
    {
        // Handle poll updates
    }

    private function onPollAnswer(Nutgram $bot): void
    {
        // Handle poll answers
    }

    // Chat member update handlers
    private function onMyChatMember(Nutgram $bot): void
    {
        // Handle bot's chat member status changes
    }

    private function onChatMember(Nutgram $bot): void
    {
        // Handle other chat member status changes
    }

    // Chat join request handler
    private function onChatJoinRequest(Nutgram $bot): void
    {
        $bot->sendMessage('Join request received!');
    }

    // Forum topic handlers
    private function onForumTopicCreated(Nutgram $bot): void
    {
        $bot->sendMessage('New forum topic created!');
    }

    private function onForumTopicClosed(Nutgram $bot): void
    {
        $bot->sendMessage('Forum topic closed!');
    }

    private function onForumTopicReopened(Nutgram $bot): void
    {
        $bot->sendMessage('Forum topic reopened!');
    }

    // Video chat handlers
    private function onVideoChatScheduled(Nutgram $bot): void
    {
        $bot->sendMessage('Video chat scheduled!');
    }

    private function onVideoChatStarted(Nutgram $bot): void
    {
        $bot->sendMessage('Video chat started!');
    }

    private function onVideoChatEnded(Nutgram $bot): void
    {
        $bot->sendMessage('Video chat ended!');
    }

    // Web app data handler
    private function onWebAppData(Nutgram $bot): void
    {
        $bot->sendMessage('Web app data received!');
    }

    // Story message handler
    private function onStoryMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Story received!');
    }

    // Venue message handler
    private function onVenueMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Venue information received!');
    }

    // Dice message handler
    private function onDiceMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Dice rolled!');
    }

    // Game message handler
    private function onGameMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Game message received!');
    }

    // Invoice message handler
    private function onInvoiceMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Invoice received!');
    }

    // Connected website handler
    private function onConnectedWebsite(Nutgram $bot): void
    {
        $bot->sendMessage('Website connected!');
    }

    // Passport data handler
    private function onPassportData(Nutgram $bot): void
    {
        $bot->sendMessage('Passport data received!');
    }

    // Proximity alert handler
    private function onProximityAlertTriggered(Nutgram $bot): void
    {
        $bot->sendMessage('Proximity alert triggered!');
    }

    // Video chat participants invited handler
    private function onVideoChatParticipantsInvited(Nutgram $bot): void
    {
        $bot->sendMessage('Participants invited to video chat!');
    }

    // Message auto delete timer changed handler
    private function onMessageAutoDeleteTimerChanged(Nutgram $bot): void
    {
        $bot->sendMessage('Message auto delete timer changed!');
    }

    // Pinned message handler
    private function onPinnedMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Message pinned!');
    }

    // Migrate event handlers
    private function onMigrateToChatId(Nutgram $bot): void
    {
        $bot->sendMessage('Chat migrated to new ID!');
    }

    private function onMigrateFromChatId(Nutgram $bot): void
    {
        $bot->sendMessage('Chat migrated from old ID!');
    }

    // Chat creation handlers
    private function onGroupChatCreated(Nutgram $bot): void
    {
        $bot->sendMessage('Group chat created!');
    }

    private function onSupergroupChatCreated(Nutgram $bot): void
    {
        $bot->sendMessage('Supergroup chat created!');
    }

    private function onChannelChatCreated(Nutgram $bot): void
    {
        $bot->sendMessage('Channel chat created!');
    }

    // Chat photo change handlers
    private function onNewChatPhoto(Nutgram $bot): void
    {
        $bot->sendMessage('New chat photo set!');
    }

    private function onDeleteChatPhoto(Nutgram $bot): void
    {
        $bot->sendMessage('Chat photo deleted!');
    }

    // Chat title change handler
    private function onNewChatTitle(Nutgram $bot): void
    {
        $bot->sendMessage('Chat title changed!');
    }

    // Edited message handler
    private function onEditedMessage(Nutgram $bot): void
    {
        $bot->sendMessage('Message was edited!');
    }

    // Channel post handlers
    private function onChannelPost(Nutgram $bot): void
    {
        $bot->sendMessage('Channel post received!');
    }

    private function onEditedChannelPost(Nutgram $bot): void
    {
        $bot->sendMessage('Channel post was edited!');
    }

    // Fallback handler
    private function onFallback(Nutgram $bot): void
    {
        $bot->sendMessage('Unhandled update received.');
    }

    // Exception handlers
    private function onException(Nutgram $bot, \Throwable $e): void
    {
        $bot->sendMessage('An error occurred: ' . $e->getMessage());
    }

    private function onApiError(Nutgram $bot, \Throwable $e): void
    {
        $bot->sendMessage('API error occurred: ' . $e->getMessage());
    }
}
