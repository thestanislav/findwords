<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 3/25/2018
 * Time: 12:28
 */

namespace App\Console;

use App\Entity\Word;
use Doctrine\ORM\EntityManager;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Doctrine\Hydrator\DoctrineEntity;
use ExprAs\Doctrine\Repository\DefaultRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PhonemeRebuild extends Command
{
    use ServiceContainerAwareTrait;

    protected static $defaultName = 'app:phoneme-rebuild';

    protected $_entityManager;

    /**
     * @return EntityManager
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getEntityManager()
    {
        if (!$this->_entityManager) {
            $this->_entityManager = $this->getContainer()->get(EntityManager::class);
        }
        return $this->_entityManager;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get(EntityManager::class);
        /** @var DefaultRepository $repo */
        $repo = $em->getRepository(Word::class);

        $paginator = $repo->createPaginator();
        $paginator->setItemCountPerPage(100);
        $totalPages =  $paginator->count();

        foreach (range(1, $totalPages) as $_pageNo) {

            $output->writeln(sprintf('Parsing page %d of %d', $_pageNo, $totalPages));

            /** @var Word[] $items */
            $items = $paginator->getItemsByPage($_pageNo);
            foreach ($items as $_item) {
                exec(sprintf('/usr/bin/espeak-ng -xq -vru "%s"', $_item->getWord()), $phoneme);
                if (!count($phoneme)) {
                    continue;
                }
                $phoneme = $phoneme[0];
                $_item->setPhoneme($phoneme);
                $_item->setPhonemeLastFourSymbols(substr($phoneme, -4));
                $_item->setPhonemeLastTreeSymbols(substr($phoneme, -3));
                $_item->setPhonemeLastTwoSymbols(substr($phoneme, -2));
                $em->persist($_item);
            }

            $em->flush();
            $em->clear();
        }

        return 1;
    }
}