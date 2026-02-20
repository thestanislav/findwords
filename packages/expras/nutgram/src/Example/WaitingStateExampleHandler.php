<?php

namespace ExprAs\Nutgram\Example;

use ExprAs\Nutgram\SubscriberInterface;
use SergiX44\Nutgram\Nutgram;
use ExprAs\Nutgram\Entity\User;

/**
 * Example handler demonstrating how to use the waiting state functionality
 * for surveys, forms, and multi-step conversations
 */
class WaitingStateExampleHandler implements SubscriberInterface
{
    public function subscribeToEvents(Nutgram $bot): void
    {
        // Start survey command
        $bot->onCommand('survey', fn(Nutgram $bot) => $this->startSurvey($bot));

        // Start registration command
        $bot->onCommand('register', fn(Nutgram $bot) => $this->startRegistration($bot));

        // Cancel waiting state
        $bot->onCommand('cancel', fn(Nutgram $bot) => $this->cancelWaitingState($bot));
    }

    /**
     * Start a simple survey
     */
    public function startSurvey(Nutgram $bot): void
    {
        $user = $this->getCurrentUser($bot);
        if (!$user) {
            $bot->sendMessage('User not found. Please try again.');
            return;
        }

        $bot->sendMessage('Let\'s start the survey! 🎉');
        $bot->sendMessage('What\'s your name?');

        // Set waiting state
        $user->setWaitingForHandler(WaitingStateExampleHandler::class . '::handleSurveyName');
        $user->setWaitingContext(['step' => 'name', 'type' => 'survey']);
        $user->setWaitingSince(new \DateTime());

        $this->saveUser($user);
    }

    /**
     * Handle survey name input
     */
    public function handleSurveyName(Nutgram $bot, User $user, array $context): void
    {
        $name = $bot->message()->text;

        // Validate input
        if (strlen((string) $name) < 2) {
            $bot->sendMessage('Name must be at least 2 characters long. Please try again.');
            return; // Keep waiting for valid input
        }

        // Save name to context
        $context['name'] = $name;
        $user->setWaitingContext($context);

        $bot->sendMessage("Nice to meet you, $name! What's your age?");

        // Move to next step
        $user->setWaitingForHandler(WaitingStateExampleHandler::class . '::handleSurveyAge');
        $user->setWaitingContext($context);

        $this->saveUser($user);
    }

    /**
     * Handle survey age input
     */
    public function handleSurveyAge(Nutgram $bot, User $user, array $context): void
    {
        $age = $bot->message()->text;

        // Validate input
        if (!is_numeric($age) || $age < 13 || $age > 120) {
            $bot->sendMessage('Please enter a valid age between 13 and 120.');
            return; // Keep waiting for valid input
        }

        $context['age'] = (int)$age;

        $bot->sendMessage("What's your favorite programming language?");

        // Move to next step
        $user->setWaitingForHandler(WaitingStateExampleHandler::class . '::handleSurveyLanguage');
        $user->setWaitingContext($context);

        $this->saveUser($user);
    }

    /**
     * Handle survey language input
     */
    public function handleSurveyLanguage(Nutgram $bot, User $user, array $context): void
    {
        $language = $bot->message()->text;
        $context['language'] = $language;

        // Complete survey
        $bot->sendMessage("Thank you for completing the survey! 🎉");
        $bot->sendMessage("Summary:\n" . $this->formatSurveySummary($context));

        // Clear waiting state
        $user->clearWaitingState();
        $this->saveUser($user);

        // Optionally save survey data to separate table
        $this->saveSurveyData($user, $context);
    }

    /**
     * Start user registration
     */
    public function startRegistration(Nutgram $bot): void
    {
        $user = $this->getCurrentUser($bot);
        if (!$user) {
            $bot->sendMessage('User not found. Please try again.');
            return;
        }

        $bot->sendMessage('Welcome to user registration! 📝');
        $bot->sendMessage('Please enter your email address:');

        // Set waiting state
        $user->setWaitingForHandler(WaitingStateExampleHandler::class . '::handleRegistrationEmail');
        $user->setWaitingContext(['step' => 'email', 'type' => 'registration']);
        $user->setWaitingSince(new \DateTime());

        $this->saveUser($user);
    }

    /**
     * Handle registration email input
     */
    public function handleRegistrationEmail(Nutgram $bot, User $user, array $context): void
    {
        $email = $bot->message()->text;

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $bot->sendMessage('Please enter a valid email address.');
            return; // Keep waiting for valid input
        }

        $context['email'] = $email;
        $user->setWaitingContext($context);

        $bot->sendMessage("Great! Now please enter your phone number:");

        // Move to next step
        $user->setWaitingForHandler(WaitingStateExampleHandler::class . '::handleRegistrationPhone');
        $user->setWaitingContext($context);

        $this->saveUser($user);
    }

    /**
     * Handle registration phone input
     */
    public function handleRegistrationPhone(Nutgram $bot, User $user, array $context): void
    {
        $phone = $bot->message()->text;

        // Basic phone validation (you might want more sophisticated validation)
        if (strlen((string) $phone) < 10) {
            $bot->sendMessage('Please enter a valid phone number (at least 10 digits).');
            return; // Keep waiting for valid input
        }

        $context['phone'] = $phone;

        // Complete registration
        $bot->sendMessage("Registration completed successfully! ✅");
        $bot->sendMessage("Your details:\n" . $this->formatRegistrationSummary($context));

        // Clear waiting state
        $user->clearWaitingState();
        $this->saveUser($user);

        // Save registration data
        $this->saveRegistrationData($user, $context);
    }

    /**
     * Cancel waiting state
     */
    public function cancelWaitingState(Nutgram $bot): void
    {
        $user = $this->getCurrentUser($bot);
        if (!$user || !$user->isWaitingForInput()) {
            $bot->sendMessage('No active conversation to cancel.');
            return;
        }

        $context = $user->getWaitingContext();
        $type = $context['type'] ?? 'conversation';

        $bot->sendMessage("$type cancelled. You can start over anytime!");

        // Clear waiting state
        $user->clearWaitingState();
        $this->saveUser($user);
    }

    /**
     * Get current user from bot context
     */
    private function getCurrentUser(Nutgram $bot): ?User
    {
        // Try to get user from bot context first
        if (method_exists($bot, 'get') && $bot->get('expras.nutgram.user')) {
            return $bot->get('expras.nutgram.user');
        }

        // Fallback: try to get from message
        if ($bot->message() && $bot->message()->from) {
            // In a real implementation, you'd fetch from database
            // For this example, we'll return null
            return null;
        }

        return null;
    }

    /**
     * Save user to database
     */
    private function saveUser(User $user): void
    {
        // In a real implementation, you'd save to database
        // For this example, we'll do nothing
    }

    /**
     * Format survey summary
     */
    private function formatSurveySummary(array $context): string
    {
        return "Name: {$context['name']}\n" .
               "Age: {$context['age']}\n" .
               "Favorite Language: {$context['language']}";
    }

    /**
     * Format registration summary
     */
    private function formatRegistrationSummary(array $context): string
    {
        return "Email: {$context['email']}\n" .
               "Phone: {$context['phone']}";
    }

    /**
     * Save survey data
     */
    private function saveSurveyData(User $user, array $context): void
    {
        // In a real implementation, you'd save to a survey table
        // For this example, we'll do nothing
    }

    /**
     * Save registration data
     */
    private function saveRegistrationData(User $user, array $context): void
    {
        // In a real implementation, you'd save to a registration table
        // For this example, we'll do nothing
    }
}
