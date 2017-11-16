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
//        /** @var GoogleAnalyticsReportingService $googleAnalyticsService */
//        $googleAnalyticsService = $this->getContainer()->get('elama.google_analytics.reporting');
//        try {
//            var_dump($googleAnalyticsService->getRegistrationReport());
//        } catch (\Exception $e) {
//            echo $e->getMessage();
//        }
        echo "google info send";
    }
}
