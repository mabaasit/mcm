<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class NASA extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('process:nasa')->setDescription('Process data from NASA.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting to process data from NASA');
        $output->writeln('Requesting NASA for data');
        
        $nasa = new \AppBundle\Service\NASA();

        // Fetch data from NASA 
        $data = $nasa->getNearEarthObjects();

	    $output->writeln("Data (" . count($data) . " items) fetched from NASA");
        $output->writeln('Inserting data');

        // Get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        // Insert data into db
        $result = $nasa->insertNearEarthObjects($em, $data);

        if(isset($result['error']) and ! $result['error']) {
            $output->writeln('Data processed: Failed');
            $output->writeln($result['message']);
        } else {
            $output->writeln('Data processed: ' . $result['count'] . ' records inserted');
        }

    }
}
