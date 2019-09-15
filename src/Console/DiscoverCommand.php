<?php

namespace BitPress\AutoDiscovery\Console;

use BitPress\AutoDiscovery\Discovery\SourceFile;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
        if (! $path = realpath($input->getArgument('path'))) {
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
            $source = new SourceFile($file);

            if ($source->isServiceProvider()) {
                $this->providers[] = $source->fullyQualifiedClass();
            } else if ($source->isFacade()) {
                $this->aliases[$source->getNamespace()] = $source->fullyQualifiedClass();
            }
        }

        if (empty($this->providers) && empty($this->aliases)) {
            $output->writeln('<info>No providers or aliases found.</info>');
            return;
        }

        $output->writeln(json_encode([
            'extra' => [
                'laravel' => $this->getDiscoveryConfig()
            ]
        ], JSON_PRETTY_PRINT));
    }

    private function getDiscoveryConfig()
    {
        $results = [];

        if (! empty($this->providers)) {
            $results['providers'] = $this->providers;
        }

        if (! empty($this->aliases)) {
            $results['aliases'] = $this->aliases;
        }

        return $results;
    }
}
