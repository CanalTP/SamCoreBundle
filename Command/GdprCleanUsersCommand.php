<?php

namespace CanalTP\SamCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputOption;

class GdprCleanUsersCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('sam:gdpr')
            ->setDescription('Remove incative users according to GDPR rules')
            ->setHelp(<<<EOT
The <info>sam:gdpr</info> command removes incative users according to the General Data Protection Regulation rules

<info>php app/console sam:gdpr</info>
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $this->getContainer()->get('sam.gdpr.handler');
        $service->run();
    }
}
