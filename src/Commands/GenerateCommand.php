<?php

declare(strict_types=1);

namespace Corp104\Eloquent\Generator\Commands;

use Corp104\Eloquent\Generator\CodeBuilders\MultiDatabase;
use Corp104\Eloquent\Generator\CodeBuilders\SingleDatabase;
use Corp104\Eloquent\Generator\CodeWriter;
use Illuminate\Container\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
    use Concerns\DatabaseConnection,
        Concerns\Environment;

    protected function configure()
    {
        parent::configure();

        $this->setName('eloquent-generator')
            ->setDescription('Generate model')
            ->addOption('--env', null, InputOption::VALUE_REQUIRED, '.env file', '.env')
            ->addOption('--config-file', null, InputOption::VALUE_REQUIRED, 'Config file', 'config/database.php')
            ->addOption('--output-dir', null, InputOption::VALUE_REQUIRED, 'Relative path with getcwd()', 'build')
            ->addOption('--namespace', null, InputOption::VALUE_REQUIRED, 'Namespace prefix', 'App');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = $input->getOption('env');
        $configFile = $input->getOption('config-file');
        $outputDir = $input->getOption('output-dir');
        $namespace = $input->getOption('namespace');

        $this->loadDotEnv(
            $this->normalizePath($env)
        );

        $container = Container::getInstance();

        $this->prepareConnection(
            $container,
            $this->normalizePath($configFile)
        );

        $codeWriter = $container->make(CodeWriter::class);

        $codeWriter->generate(
            function () use ($container, $namespace) {
                if (count($this->connections) === 1) {
                    $codeBuilder = $container->make(SingleDatabase::class);

                    return $codeBuilder->build($namespace, array_keys($this->connections)[0]);
                }

                $codeBuilder = $container->make(MultiDatabase::class);

                return $codeBuilder->build($namespace, $this->connections);
            },
            $this->normalizePath($outputDir)
        );
    }

    /**
     * @param string $path
     * @return string
     */
    private function normalizePath($path): string
    {
        if ($path{0} !== '/') {
            $path = getcwd() . '/' . $path;
        }

        return $path;
    }
}
