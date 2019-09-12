<?php

namespace BitPress\AutoDiscovery\Console;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
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
            ->addOption('write', 'w', InputOption::VALUE_NONE, 'Write the result in the composer.json in given directory');
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

        $extra = [
            'extra' => [
                'laravel' => $laravel
            ]
        ];

        if ($input->getOption('write')) {
            $composerJsonPath = $path.DIRECTORY_SEPARATOR.'composer.json';

            $output->writeln('<info>Write result in:</info> '.$composerJsonPath);

            if (! $this->validComposerFile($composerJsonPath)) {
                $output->writeln('<error>No composer.json found.</error>');
                return;
            }

            // @todo Avoid merging duplicate extra and possibly make the merging solution more elegenat :D
            $composerJson = json_decode(file_get_contents($composerJsonPath), true);
            $composerJson['extra']['laravel']['providers'] = array_unique(array_merge($composerJson['extra']['laravel']['providers'] ?? [], $extra['extra']['laravel']['providers']));
            $composerJson['extra']['laravel']['aliases'] = array_merge($composerJson['extra']['laravel']['aliases'] ?? [], $extra['extra']['laravel']['aliases']);

            $newJson = json_encode($composerJson, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

            $question = new ConfirmationQuestion("$newJson\n\nYou are about to overwrite the composer file, please verify the above contents.\nShould I continue? (y/n)", false);

            if (! $this->getHelper('question')->ask($input, $output, $question)) {
                $output->writeln('<info>You canceled the write change, aborting.</info>');
                return;
            }

            file_put_contents($composerJsonPath, $newJson);
        } else {
            $output->writeln(json_encode($extra, JSON_PRETTY_PRINT));
        }

        $output->writeln('<info>Done.</info>');
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

    private function validComposerFile($path)
    {
        return (
            file_exists($path)
            && is_file($path)
            && is_readable($path)
            && is_writeable($path)
        );
    }
}
