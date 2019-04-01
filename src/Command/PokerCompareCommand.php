<?php

namespace App\Command;

use App\Model\PokerHand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PokerCompareCommand extends Command
{
    protected static $defaultName = 'poker:compare';

    protected function configure(): void
    {
        $this
            ->setDescription('Compare two hands of poker (5 cards each)')
            ->addArgument('h1', InputArgument::REQUIRED, 'First hand')
            ->addArgument('h2', InputArgument::REQUIRED, 'Second hand');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $h1 = $input->getArgument('h1');
        $h2 = $input->getArgument('h2');

        $pokerHand1 = PokerHand::createFromString($h1);

        $pokerHand2 = PokerHand::createFromString($h2);

        $result = '';
        switch ($pokerHand1->compareWith($pokerHand2)) {
            case 1:
                $result = sprintf('You won with %s', $pokerHand1->getBestHandAsString());
                $io->block($result, null, 'fg=green');
                break;
            case 2:
                $result = sprintf('Your opponent won with %s', $pokerHand2->getBestHandAsString());
                $io->block($result, null, 'fg=red');
                break;
            case 3:
                $result = sprintf(
                    'All tied up with %s and %s',
                    $pokerHand1->getBestHandAsString(),
                    $pokerHand2->getBestHandAsString()
                );
                $io->block($result, null, 'fg=yellow');
                break;
        }
    }
}
