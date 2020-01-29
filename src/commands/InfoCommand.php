<?php declare(strict_types=1);

namespace AYashenkov\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends Command {

    public function configure()
    {
        $this->setName('info')
            ->setDescription('Info about console app.')
            ->setHelp('This command allows you to see information about this application.');
    }

    public function execute(InputInterface $input, OutputInterface $output): string
    {
        $output->writeln([
            '=======================================================',
            '====**** Noritsu dirs logs watcher console app ****====',
            '==== This app watches dirs and parse logs in dirs. ====',
            '==== Before starting watch command edit .env file. ====',
            '=======================================================',
            '',
        ]);
    }
}