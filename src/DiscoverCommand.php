<?php

namespace BitPress\AutoDiscovery\Console;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DiscoverCommand extends Command
{
    private $providers = [];

    private $aliases = [];

    public function configure()
    {
        $this
            ->setName('scan')
            ->setDescription('Scan a path for service providers and aliases')
            ->addArgument('path', InputArgument::OPTIONAL, 'The path to scan - by default the current directory', '.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = realpath($input->getArgument('path'));

        if ($path === false) {
            throw new InvalidArgumentException('The path does not exist');
        }

        $output->writeln('<info>Search in:</info> '.$path);

        $finder = new Finder();

        $finder
            ->files()
            ->name('*.php')
            ->notPath('#vendor/.*#');

        /** @var SplFileInfo $file */
        foreach ($finder->in($path) as $file) {
            $this->detectServiceProvider($file);
            $this->detectAlias($file);
        }

        if (empty($this->providers) && empty($this->aliases)) {
            $output->writeln('<info>No providers or aliases found.</info>');
            return;
        }
        $laravel = [];

        if (!empty($this->providers)) {
            $laravel['providers'] = $this->providers;
        }

        if (!empty($this->aliases)) {
            $laravel['aliases'] = $this->aliases;
        }

        $output->writeln(json_encode([
            'extra' => [
                'laravel' => $laravel
            ]
        ], JSON_PRETTY_PRINT));
    }

    private function detectServiceProvider(SplFileInfo $file)
    {
        if (preg_match('/use Illuminate\\\\Support\\\\ServiceProvider/', $file->getContents())) {
            $this->extractServiceProvider($file);
        }
    }

    private function detectAlias(SplFileInfo $file)
    {
        if (preg_match('/use Illuminate\\\\Support\\\\Facades\\\\Facade/', $file->getContents())) {
            $this->extractAlias($file);
        }
    }

    private function extractServiceProvider(SplFileInfo $file)
    {
        [$namespace, $class] = $this->extractClass($file);

        if ($class && $namespace) {
            $this->providers[] = $namespace . '\\' . $class;
        }
    }

    private function extractAlias(SplFileInfo $file)
    {
        [$namespace, $class] = $this->extractClass($file);

        if ($class && $namespace) {
            $this->aliases[$class] = $namespace . '\\' . $class;
        }
    }

    private function extractClass(SplFileInfo $file)
    {
        preg_match('/namespace\s+([^;]+);/', $file->getContents(), $matches);
        $namespace = $matches[1];
        preg_match('/class\s+([^\s]+)\s/', $file->getContents(), $matches);
        $class = $matches[1];

        return [$namespace, $class];
    }
}
