<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 3/25/2018
 * Time: 12:28
 */

namespace ExprAs\Core\Console;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Laminas\Cache\Storage\FlushableInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Laminas\Cache\StorageFactory;


#[AsCommand(name: 'expras:cache:flush', description: 'Flush the cache')]

class CacheFlush extends Command
{
    use ServiceContainerAwareTrait;


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $cacheManager = $this->getContainer()->get(StorageFactory::class);
        if ($cacheManager instanceof FlushableInterface) {
            file_put_contents('php://stdout', 'Clearing cache '. "...\n", FILE_APPEND);
            $cacheManager->flush();
        } else {
            file_put_contents('php://stdout', $cacheManager::class . ' cache adapter  does not implements Flushable Interface'. ".\n", FILE_APPEND);
        }

        return 0;
    }

}
