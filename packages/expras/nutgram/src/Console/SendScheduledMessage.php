<?php

declare(strict_types=1);

namespace ExprAs\Nutgram\Console;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Nutgram\Entity\DefaultUser;
use ExprAs\Nutgram\Entity\ScheduledMessage;
use ExprAs\Nutgram\Entity\ScheduledMessageSentStatus;
use Laminas\Log\Logger;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Internal\InputFile;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function intval;
use function sprintf;

#[AsCommand(
    name: 'nutgram:send-scheduled-message',
    description: 'Send scheduled messages',
)]
class SendScheduledMessage extends Command
{
    use ServiceContainerAwareTrait;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get(EntityManager::class);

        $interval = 20;
        $now = new DateTime();
        $now->setTime(
            intval($now->format('H')),
            intval($now->format('i') / $interval) * $interval,
            0
        );

        /** @var Nutgram $bot */
        $bot = $this->getContainer()->get(Nutgram::class);

        // first send messages to users those are not depended on timezone
        $dql = sprintf(
            'select e from %s e where e.scheduledTime <= :current_time
                        and DATE_ADD(e.scheduledTime, %d, \'MINUTE\') > :current_time
                         ',
            ScheduledMessage::class,
            $interval
        );

        $dqlQuery = $em->createQuery($dql);

        $dqlQuery->setParameters([
            'current_time' => $now->format('Y-m-d H:i:s'),
        ]);

        /** @var ScheduledMessage[] $messages */
        $messages = $dqlQuery->getResult();

        $perPage = 180;

        $botUserEntity = $this->getContainer()->get('config')['nutgram']['userEntity'];
        

        foreach ($messages as $_message) {
            $qb = $em->createQueryBuilder();
            $qb->select('e')
                ->from($botUserEntity, 'e')
                ->where('1 = 1');

            if ($_message->getScheduledToUsers()->count() > 0) {
                $qb->andWhere('e.id IN (:sendToUsers)')
                    ->setParameter('sendToUsers', $_message->getScheduledToUsers()->toArray());
            }

            if ($_message->getScheduledToCriteria()) {
                $criteria = json_decode($_message->getScheduledToCriteria(), true);
                if (is_array($criteria)) {
                    foreach ($criteria as $criteriaParams) {
                        if (!method_exists(($expr = new Expr()), $operator = $criteriaParams['operator'])) {
                            continue;
                        }

                        $criterion = $expr->{$operator}('e.' . $criteriaParams['field'], ...$criteriaParams['args']);

                        if (is_array($criterion) && count($criterion) > 1 && method_exists(($expr = new Expr()), $criterion[1])) {
                            $_field = array_shift($criterion);
                            $operator = array_shift($criterion);
                            $criterion = $expr->{$operator}('e.' . $_field, ...$criterion);
                        }
                        $qb->andWhere($criterion);
                    }
                }
            }

            $subQb = $em->createQueryBuilder();
            $subQb->select('bu')
                ->from(ScheduledMessageSentStatus::class, 'ss')
                ->join('ss.botUser', 'bu')
                ->where('ss.scheduledMessage = :messageId');

            $qb->andWhere($qb->expr()->notIn('e.id', $subQb->getDQL()))
                ->setParameter('messageId', $_message->getId());

            $dqlQuery = $qb->getQuery();

            $dqlQuery->setMaxResults($perPage);

            /**
             * @var DefaultUser[] $users
             */
            $users = $dqlQuery->getResult();

            foreach ($users as $_user) {
                $status = new ScheduledMessageSentStatus();
                $status->setBotUser($_user);
                $status->setScheduledMessage($_message);
                $status->setSentAt(new DateTime());

                try {
                    $sendParams = [];
                    if ($_message->isUseMarkDown()) {
                        $sendParams['parse_mode'] = 'Markdown';
                    }
                    if ($_message->getButtonText() && $_message->getButtonCommand()) {
                        $sendParams['reply_markup'] = \SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup::make()
                            ->addRow(
                                \SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton::make(
                                    text: $_message->getButtonText(),
                                    callback_data: $_message->getButtonCommand()
                                )
                            );
                    }

                    if ($attachment = $_message->getAttachment()) {
                        if (str_starts_with((string) $attachment->getMimeType(), 'image') && !str_ends_with((string) $attachment->getMimeType(), 'gif')) {
                            $response = $bot->sendPhoto(
                                photo: InputFile::make($attachment->getPath()),
                                chat_id: $_user->getId(),
                                caption: $_message->getContent(), // caption
                                parse_mode: $sendParams['parse_mode'] ?? null,
                                reply_markup: $sendParams['reply_markup'] ?? null
                            );
                        } elseif (str_starts_with((string) $attachment->getMimeType(), 'video')) {
                            $response = $bot->sendVideo(
                                video: InputFile::make($attachment->getPath()),
                                chat_id: $_user->getId(),
                                caption: $_message->getContent(), // caption
                                parse_mode: $sendParams['parse_mode'] ?? null,
                                reply_markup: $sendParams['reply_markup'] ?? null
                            );
                        } else {
                            $response = $bot->sendDocument(
                                document: InputFile::make($attachment->getPath()),
                                chat_id: $_user->getId(),
                                caption: $_message->getContent(), // caption
                                parse_mode: $sendParams['parse_mode'] ?? null,
                                reply_markup: $sendParams['reply_markup'] ?? null
                            );
                        }
                    } else {
                        $response = $bot->sendMessage(
                            chat_id: $_user->getId(),
                            text: $_message->getContent(),
                            parse_mode: $sendParams['parse_mode'] ?? null,
                            reply_markup: $sendParams['reply_markup'] ?? null
                        );
                    }

                    $status->setTelegramMessageId($response->message_id);
                    $status->setStatusCode(0);
                } catch (Throwable $throwable) {
                    $status->setStatusCode($throwable->getCode());
                    $status->setStatusText($throwable->getMessage());
                    $em->persist($status);

                    $output->writeln(sprintf('Error sending message to user %s: %s', $_user->getUsername() ?? 'unknown', $throwable->getMessage()));
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
                } finally {
                    $em->persist($status);
                    $_message->addSentStatuses($status);
                    $em->persist($_message);
                }
            }

            $em->flush();
        }

        return self::SUCCESS;
    }
}
