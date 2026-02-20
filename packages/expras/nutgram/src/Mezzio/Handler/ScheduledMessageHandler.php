<?php

namespace ExprAs\Nutgram\Mezzio\Handler;

use ExprAs\Admin\Handler\JsonServerRestApiHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ExprAs\Core\Handler\RequestParamsTrait;
use DateTime;
use Doctrine\ORM\EntityManager;
use ExprAs\Nutgram\Entity\ScheduledMessage;
use ExprAs\Nutgram\Entity\ScheduledMessageSentStatus;
use Laminas\Diactoros\Response\JsonResponse;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Internal\InputFile;

class ScheduledMessageHandler extends JsonServerRestApiHandler
{
    use RequestParamsTrait;

    public function sendTestMessageAction(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $em = $this->getContainer()->get(EntityManager::class);
        if (!($message = $em->find(ScheduledMessage::class, $this->params($request)->fromParsedBody('message')))) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Message not found'
            ]);
        }

        $botUserEntity = $this->getContainer()->get('config')['nutgram']['userEntity'];

        if (!($botUser = $em->find($botUserEntity, $this->params($request)->fromParsedBody('user')))) {
            return new JsonResponse([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        $status = new ScheduledMessageSentStatus();
        $status->setBotUser($botUser);
        $status->setScheduledMessage($message);
        $status->setSentAt(new DateTime());

        /** @var Nutgram $bot */
        $bot = $this->getContainer()->get(Nutgram::class);
        try {
            $sendParams = [];
            if ($message->isUseMarkDown()) {
                $sendParams['parse_mode'] = 'Markdown';
            }
            if ($message->getButtonText() && $message->getButtonCommand()) {
                $sendParams['reply_markup'] = \SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup::make()
                    ->addRow(
                        \SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton::make(
                            text: $message->getButtonText(),
                            callback_data: $message->getButtonCommand()
                        )
                    );
            }

            if ($attachment = $message->getAttachment()) {
                if (str_starts_with((string) $attachment->getMimeType(), 'image') && !str_ends_with((string) $attachment->getMimeType(), 'gif')) {
                    $response = $bot->sendPhoto(
                        photo: InputFile::make($attachment->getPath()),
                        chat_id: $botUser->getId(),
                        caption: $message->getContent(), // caption
                        parse_mode: $sendParams['parse_mode'] ?? null,
                        reply_markup: $sendParams['reply_markup'] ?? null
                    );
                } elseif (str_starts_with((string) $attachment->getMimeType(), 'video')) {
                    $response = $bot->sendVideo(
                        video: InputFile::make($attachment->getPath()),
                        chat_id: $botUser->getId(),
                        caption: $message->getContent(), // caption
                        parse_mode: $sendParams['parse_mode'] ?? null,
                        reply_markup: $sendParams['reply_markup'] ?? null
                    );
                } else {
                    $response = $bot->sendDocument(
                        document: InputFile::make($attachment->getPath()),
                        chat_id: $botUser->getId(),
                        caption: $message->getContent(), // caption
                        parse_mode: $sendParams['parse_mode'] ?? null,
                        reply_markup: $sendParams['reply_markup'] ?? null
                    );
                }
            } else {
                $response = $bot->sendMessage(
                    chat_id: $botUser->getId(),
                    text: $message->getContent(),
                    parse_mode: $sendParams['parse_mode'] ?? null,
                    reply_markup: $sendParams['reply_markup'] ?? null
                );
            }

            $status->setTelegramMessageId($response->message_id);
            $em->persist($status);

            return new JsonResponse([
                'success' => true,
                'message' => 'Message sent',
                'response' => $response
            ]);

        } catch (\Throwable $throwable) {
            $status->setStatusCode($throwable->getCode());
            $status->setStatusText($throwable->getMessage());
            $em->persist($status);

            return new JsonResponse([
                'success' => false,
                'message' => $throwable->getMessage()
            ]);
        } finally {
            $em->flush();
        }
    }

    public function queueDeleteAction(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get(EntityManager::class);
        if (!($message = $em->find(ScheduledMessage::class, $this->params($request)->fromQuery('id')))) {
            return new JsonResponse([
                'error' => 'message not found'
            ], 404);
        }

        $dql = sprintf('update %s e set e.scheduledToDelete = true where e.deleted = false and e.scheduledMessage = :message',
            ScheduledMessageSentStatus::class);
        $dqlQuery = $em->createQuery($dql);
        $dqlQuery->execute([
            'message' => $message
        ]);

        return new JsonResponse([
            'success' => true
        ]);
    }

    public function queueUpdateAction(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get(EntityManager::class);
        if (!($message = $em->find(ScheduledMessage::class, $this->params($request)->fromQuery('id')))) {
            return new JsonResponse([
                'error' => 'message not found'
            ], 404);
        }

        $dql = sprintf('update %s e set e.scheduledToUpdate = true where e.deleted = false and e.scheduledMessage = :message', ScheduledMessageSentStatus::class);
        $dqlQuery = $em->createQuery($dql);
        $dqlQuery->execute([
            'message' => $message
        ]);

        return new JsonResponse([
            'success' => true
        ]);
    }
}
