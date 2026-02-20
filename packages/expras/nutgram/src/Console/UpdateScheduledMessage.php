<?php

declare(strict_types=1);

namespace ExprAs\Nutgram\Console;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Nutgram\Entity\ScheduledMessageSentStatus;
use Laminas\Log\Logger;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Internal\InputFile;
use SergiX44\Nutgram\Telegram\Types\Media\InputMediaDocument;
use SergiX44\Nutgram\Telegram\Types\Media\InputMediaPhoto;
use SergiX44\Nutgram\Telegram\Types\Media\InputMediaVideo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function ceil;
use function count;
use function intval;
use function sprintf;

#[AsCommand(
    name: 'nutgram:update-sent-messages',
    description: 'Update sent messages',
)]
class UpdateScheduledMessage extends Command
{
    use ServiceContainerAwareTrait;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get(EntityManager::class);

        /** @var Nutgram $bot */
        $bot = $this->getContainer()->get(Nutgram::class);

        $dql = sprintf('select e from %s e where e.deleted = false and e.scheduledToUpdate = true', ScheduledMessageSentStatus::class);

        $dqlQuery = $em->createQuery($dql);

        // Create Doctrine Paginator
        $perPage = 100;
        $paginator = new Paginator($dqlQuery, true);
        $totalItems = count($paginator);
        $totalPages = (int)ceil($totalItems / $perPage);

        $output->writeln(sprintf('Scheduled total items: %s, total pages: %s', $totalItems, $totalPages));

        for ($page = 1; $page <= $totalPages; $page++) {
            $dqlQuery->setFirstResult(($page - 1) * $perPage)
                ->setMaxResults($perPage);

            /**
             * @var ScheduledMessageSentStatus[] $entities
             */
            $entities = $dqlQuery->getResult();

            $saveOnError = true;
            foreach ($entities as $entity) {
                try {
                    $output->writeln(sprintf('Processing %s', $entity->getTelegramMessageId()));
                    sleep(2);

                    $message = $entity->getScheduledMessage();
                    $sendParams = [];

                    if ($message->isUseMarkDown()) {
                        $sendParams['parse_mode'] = 'Markdown';
                    }
                    if (($attachment = $message->getAttachment())) {
                        if (str_starts_with((string) $attachment->getMimeType(), 'image') && !str_ends_with((string) $attachment->getMimeType(), 'gif')) {
                            $media = new InputMediaPhoto(
                                media: InputFile::make($attachment->getPath()),
                                caption: $message->getContent(),
                                parse_mode: $message->isUseMarkDown() ? 'Markdown' : null
                            );
                        } elseif (str_starts_with((string) $attachment->getMimeType(), 'video')) {
                            $media = new InputMediaVideo(
                                media: InputFile::make($attachment->getPath()),
                                caption: $message->getContent(),
                                parse_mode: $message->isUseMarkDown() ? 'Markdown' : null
                            );
                        } else {
                            $media = new InputMediaDocument(
                                media: InputFile::make($attachment->getPath()),
                                caption: $message->getContent(),
                                parse_mode: $message->isUseMarkDown() ? 'Markdown' : null
                            );
                        }
                        $bot->editMessageMedia([
                            'chat_id' => $entity->getBotUser()->getId(),
                            'message_id' => $entity->getTelegramMessageId(),
                            'media' => $media
                        ]);
                    } else {
                        $bot->editMessageText(
                            text: $message->getContent(),
                            parameters: [
                                'chat_id' => $entity->getBotUser()->getId(),
                                'message_id' => $entity->getTelegramMessageId(),
                                ...$sendParams
                            ]
                        );
                    }

                    $entity->setStatusCode(0);
                    $entity->setStatusText('');
                    $saveOnError = false;

                } catch (Throwable $throwable) {
                    preg_match('/retry after (\d)/', $throwable->getMessage(), $matches);
                    if (isset($matches[1])) {
                        sleep(intval($matches[1]));
                        $saveOnError = false;
                    } else {
                        $saveOnError = true;
                        $entity->setStatusCode($throwable->getCode());
                        $entity->setStatusText($throwable->getMessage());
                        $em->persist($entity);

                        try {
                            $container = $this->getContainer();
                            if ($container->has('expras_error_logger')) {
                                /**
                                 * @var Logger $logger
                                 */
                                $logger = $container->get('expras_error_logger');
                                $extra = [
                                    'file' => $throwable->getFile(),
                                    'line' => $throwable->getLine(),
                                ];
                                $logger->err($throwable->getMessage(), $extra);
                            }
                        } catch (\Throwable $ex) {
                            $output->writeln('Error logging error: ' . $ex->getMessage());
                        }
                    }
                } finally {
                    if ($saveOnError) {
                        $entity->setScheduledToUpdate(false);
                        $em->persist($entity);
                        $em->flush();
                    }
                }
            }

            $em->flush();
        }
        return self::SUCCESS;
    }
}
