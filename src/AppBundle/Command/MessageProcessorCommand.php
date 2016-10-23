<?php

namespace AppBundle\Command;

use AppBundle\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Goutte\Client;

class MessageProcessorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('message-processor')
            ->setDescription('...')
            ->addOption('destination', null, InputOption::VALUE_REQUIRED, 'Destination ID to process')
            ->addOption('retry', null, InputOption::VALUE_REQUIRED, 'How many times to retry deliver a message. Default 3.')
            ->addOption('retry-delay', null, InputOption::VALUE_REQUIRED, 'How many seconds wait before retry deliver a message. Default 1.')
            ->addOption('persistent', null, InputOption::VALUE_NONE, 'If set, msg processor will run until user cancel it')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Configuration parameters
        $messageFilter = array();
        $persistent = false;
        $destinationFilter = null;
        $retry = 3;
        $retryDelay = 1;

        if ($input->getOption('destination')) {
            $destinationFilter = $input->getOption('destination');
            $messageFilter['destination'] = $destinationFilter;
        }
        if ($input->getOption('retry')) {
            $retry = $input->getOption('retry');
        }
        if ($input->getOption('retry-delay')) {
            $retryDelay = $input->getOption('retry-delay');
        }
        if ($input->getOption('persistent')) {
            $persistent = true;
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        do {
            //Check if destination exists
            if ($destinationFilter AND !$em->getRepository('AppBundle:Destination')
                ->find($input->getOption('destination')))
            {
                $output->writeln('Selected destination doesn\'t exist');
                throw new Exception('Selected destination doesn\'t exist');
            }

            //Query for messages
            $messages = $em
                ->getRepository('AppBundle:Message')
                ->findBy(
                    $messageFilter,
                    array('id' => 'ASC')
                );

            foreach ($messages as $message) {
                //Check if it has more than 24 hours
                $diff = date_diff($message->getCreatedAt(), new \DateTime());
                if ($diff->d >= 1){
                    $output->writeln("Message id " . $message->getId() . " is in queue for more than 24h");
                    $output->writeln("Message will be removed from the queue");
                    $em->remove($message);
                    continue;
                }


                //Check if it's a non private ip
                if (!$this->checkDestinationIp($message)) {
                    $output->writeln("Destination id " . $message->getDestination()->getId() . " is unresolvable or resolves to a private IP." );
                    $output->writeln("Message will be removed from the queue");
                    $em->remove($message);
                    continue;
                }


                $deliveredOk = false;
                for ($i = 1; $i <= $retry; $i++) {
                    $status = $this->processMessage($message);

                    if ($status == 200) {
                        $output->writeln("Message id " . $message->getId() . " delivered successful");
                        $output->writeln("Message will be removed from the queue");
                        $em->remove($message);
                        $deliveredOk = true;
                        break;
                    }
                    //Wait 1 second before retry deliver message
                    sleep($retryDelay);
                }
                if ($deliveredOk) {
                    continue;
                }

                $output->writeln("Error delivering message id " . $message->getId() . "after 3 tries");
                $output->writeln("Status code: " . $status);
                $output->writeln("Message will be removed from the queue");

                $em->remove($message);
            }
            $em->flush();
            $em->clear();


            sleep(0.2);
        } while ($persistent);
    }
    private function processMessage(Message $message) {
        $client = new Client();
        $client->setHeader('Content-Type', $message->getContentType());
        $client->request(
            'POST',
            $message->getDestination()->getUrl(),
            array(),
            array(),
            array(),
            $message->getMsgBody()
        );

        return $client->getResponse()->getStatus();
    }

    private function checkDestinationIp(Message $message) {
        $ip = gethostbyname(parse_url($message->getDestination()->getUrl(), PHP_URL_HOST));

        //Can't resolve IP
        if (!$ip) {
            return false;
        }

        //Returns:
        //True: IP isn't in private range
        //False: IP is in private range
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE
        );

    }
}
