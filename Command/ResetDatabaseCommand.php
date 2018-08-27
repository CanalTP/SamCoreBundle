<?php

namespace CanalTP\SamCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command that purges the database
 *
 * @author David Quintanel <david.quintanel@canaltp.fr>
 */
class ResetDatabaseCommand extends ContainerAwareCommand
{
    private $env = null;
    private $console = null;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('sam:database:reset')
            ->setDescription('Drop and create the database, create the different schemas and load the fixtures')
            ->addOption('load-fixtures', null, InputOption::VALUE_NONE, 'Load fixtures into the database')
            ->setHelp(<<<EOT
The <info>sam:database:reset</info> command Drop and create the database,
create the different schemas:

<info>php app/console sam:database:reset --load-fixtures</info>
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->env = $this->getContainer()->get('kernel')->getEnvironment();
        $this->console = $this->getApplication();

        $this->console->setAutoExit(false);
        $this->console->setCatchExceptions(false);

        // Drop the database
        $this->runCommand('doctrine:database:drop', array('--force'  => true), $output);

        // Create the database
        $this->runCommand('doctrine:database:create', array(), $output);

        $applicationFinder = $this->getContainer()->get('canal_tp_sam.application.finder');
        $applications = $applicationFinder->getApplicationBundles();
        // Create tables for each aplications
        foreach ($applications as $application) {
            $this->runCommand('claroline:migration:upgrade', array('bundle' => $application['bundle'], '--target' => 'farthest'), $output);
        }

        // Fixtures
        if ($input->getOption('load-fixtures')) {
            if (!array_key_exists('DoctrineFixturesBundle', $this->getContainer()->get('kernel')->getBundles())) {
                throw new \Exception('doctrine-fixtures-bundle must be installed and loaded into AppKernel');
            }
            $this->runCommand('doctrine:fixtures:load', array('--append' => true), $output);
        }
    }

    private function runCommand($command, $arguments, OutputInterface $output)
    {

        $input = new ArrayInput(
            array_merge(
                array('command' => $command),
                array_merge($arguments, array('--env'=> $this->env, '-n' => true))
            )
        );
        try {
            $status = $this->console->run($input, $output);
        } catch (Exception $e) {
            $application->renderException($e, $output);

            $statusCode = $e->getCode();
            $statusCode = is_numeric($statusCode) && $statusCode ? $statusCode : 1;
            exit($statusCode);
        }
    }
}
