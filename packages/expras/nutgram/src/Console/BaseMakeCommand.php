<?php

namespace ExprAs\Nutgram\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

abstract class BaseMakeCommand extends Command
{
    public function __construct(protected ContainerInterface $container, ?string $name = null)
    {
        parent::__construct($name);
    }
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the class');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $output->writeln("Creating {$this->getSubDirName()} {$name}...");

        $stub = $this->getStubContent();
        $content = $this->replacePlaceholders($stub, $name);

        $filePath = $this->getFilePath($name);
        $this->ensureDirectoryExists(dirname($filePath));

        if (file_put_contents($filePath, $content)) {
            $output->writeln("<info>{$this->getSubDirName()} {$name} created successfully!</info>");
            $output->writeln("<info>File: {$filePath}</info>");
            return Command::SUCCESS;
        }

        $output->writeln("<error>Failed to create {$this->getSubDirName()} {$name}</error>");
        return Command::FAILURE;
    }

    protected function getStubContent(): string
    {
        $stubPath = $this->getStubPath();
        if (!file_exists($stubPath)) {
            throw new \RuntimeException("Stub file not found: {$stubPath}");
        }
        return file_get_contents($stubPath);
    }

    protected function replacePlaceholders(string $stub, string $name): string
    {
        $namespace = $this->getNamespace();
        $className = $this->getClassName($name);

        return str_replace(
            ['{{ namespace }}', '{{ name }}'],
            [$namespace, $className],
            $stub
        );
    }

    protected function getFilePath(string $name): string
    {
        $config = $this->container->get('config');
        $nutgramConfig = $config['nutgram'] ?? [];
        $basePath = $nutgramConfig['handlers_path'] ?? getcwd() . '/src/Bot/src/Handler';
        $subDir = $this->getSubDirName();
        $className = $this->getClassName($name);

        return "{$basePath}/{$subDir}/{$className}.php";
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    protected function getNamespace(): string
    {
        $config = $this->container->get('config');
        $nutgramConfig = $config['nutgram'] ?? [];
        return $nutgramConfig['handlers_namespace'] ?? 'Bot\\Handler';
    }

    protected function getClassName(string $name): string
    {
        return ucfirst($name);
    }

    abstract protected function getSubDirName(): string;
    abstract protected function getStubPath(): string;
}
