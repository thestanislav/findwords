<?php

declare(strict_types=1);

namespace ExprAs\Nutgram\Console;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Nutgram\Entity\ScheduledMessageSentStatus;
use Laminas\Log\Logger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SergiX44\Nutgram\Nutgram;
use Throwable;

use function ceil;
use function count;
use function sprintf;

#[AsCommand(
    name: 'nutgram:delete-sent-messages',
    description: 'Remove sent messages from users telegram chat',
)]
class DeleteScheduledMessage extends Command
{
    use ServiceContainerAwareTrait;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get(EntityManager::class);

        /** @var Nutgram $bot */
        $bot = $this->getContainer()->get(Nutgram::class);

        $dql = sprintf('select e from %s e where e.deleted = false and e.scheduledToDelete = true', ScheduledMessageSentStatus::class);

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

            foreach ($entities as $entity) {
                try {
                    $bot->deleteMessage(
                        chat_id: $entity->getBotUser()->getId(),
                        message_id: $entity->getTelegramMessageId()
                    );

                    $entity->setStatusCode(0);
                    $entity->setStatusText('');
                    $entity->setDeleted(true);

                } catch (Throwable $throwable) {
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
                } finally {
                    $entity->setScheduledToDelete(false);
                    $em->persist($entity);
                }
            }

            $em->flush();
        }
        return self::SUCCESS;
    }
}
