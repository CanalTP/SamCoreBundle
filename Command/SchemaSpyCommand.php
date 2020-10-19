<?php

namespace CanalTP\SamCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class SchemaSpyCommand extends ContainerAwareCommand
{
    private $command = null;

    private function initCommand(InputInterface $input)
    {
        $this->command = 'java';
        $this->command .= ' -jar ' . $input->getArgument('path_to_schema_spy_jar');
        $this->command .= ' -t ' . $input->getOption('database_type');
        $this->command .= ' -dp ' . $input->getArgument('path_to_bdd_drivers');
        $this->command .= ' -db ' . $this->getContainer()->getParameter('database_name');
        $this->command .= ' -host ' . $this->getContainer()->getParameter('database_host');
        $this->command .= ' -port ' . $this->getContainer()->getParameter('database_port');
        $this->command .= ' -u ' . $this->getContainer()->getParameter('database_user');
        $this->command .= ' -p ' . $this->getContainer()->getParameter('database_password');
        $this->command .= ' -schemas public';
        $this->command .= ' -o docs/schemaSpy/';
    }

    private function runProcess()
    {
        $process = new Process($this->command);

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
    }

    protected function configure()
    {
        $this
            ->setName('doc:schema_spy')
            ->setDescription('Generate full documentation about database structure of the application')
            ->addArgument('path_to_schema_spy_jar', InputArgument::REQUIRED, 'Path to jar file of schemaSpy')
            ->addArgument('path_to_bdd_drivers', InputArgument::REQUIRED, 'Looks for drivers here before looking in driverPath in [databaseType].properties. The drivers are usually contained in .jar or .zip files and are typically provided by your database vendor.')
            ->addOption('database_type', 'dt', InputOption::VALUE_OPTIONAL, 'Type of database (e.g. ora, db2, etc.). Use -dbhelp for a list of built-in types.', 'pgsql')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initCommand($input);
        $this->runProcess();
    }
}
