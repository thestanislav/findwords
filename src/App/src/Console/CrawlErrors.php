<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 3/25/2018
 * Time: 12:28
 */

namespace App\Console;


use http\Url;
use Laminas\Http\Client;
use Laminas\Uri\Uri;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlErrors extends Command
{

    protected static $defaultName = 'app:crawl-errors';

    protected $linkStack = [];
    protected $crawled = [];

    protected function configure(): void
    {
        $this
            // ...
            ->addArgument('link', InputArgument::REQUIRED, 'Start link');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->linkStack[] = $input->getArgument('link');
        $this->start($output);
        return 1;
    }

    protected function start(OutputInterface $output)
    {

        if (!count($this->linkStack)) {
            return;
        }


        $url = new Uri(array_shift($this->linkStack));
        array_push($this->crawled, $url->getPath());

        $request = new Client($url->toString(), [
            'timeout' => 30
        ]);
        $response = $request->send();
        if ($response->getStatusCode() == 200) {
            $document = new \DOMDocument();
            @$document->loadHTML($response->getBody());
            $links = $document->getElementsByTagName('a');
            foreach ($links as $_link) {
                if (substr($_link->getAttribute('href'), 0, 1) === '/') {
                    $foundUrl = new Uri(sprintf('%s://%s%s', $url->getScheme(), $url->getHost(), $_link->getAttribute('href')));
                    if (!in_array($foundUrl->getPath(), $this->crawled)) {
                        $this->linkStack[] = $foundUrl->toString();
                    }
                }
            }
            //$output->write($response->getStatusCode());
        }else {
            $output->write(sprintf('<fg=red>%s</>', $response->getStatusCode()));
            $output->writeln(' -> ' . $url->toString());
        }




        $this->start($output);

    }
}