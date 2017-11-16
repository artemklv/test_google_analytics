<?php

namespace Elama\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrmRegisterInfoSendCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('crm:registration_info:send');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gaService = $this->getContainer()->get('elama.core.google_analytics_reporting');
        var_dump($gaService->getRegisterReport());
    }
}
