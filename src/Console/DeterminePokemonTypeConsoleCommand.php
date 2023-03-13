<?php

namespace App\Console;

use App\Domain\Card\CardRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:card:determine-type', description: 'Determines next card type to create')]
class DeterminePokemonTypeConsoleCommand extends Command
{
    public function __construct(
        private readonly CardRepository $cardRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $countPerCardType = $this->cardRepository->countByCardType();
        $total = array_sum($countPerCardType);

        $weightPerCardType = [];

        $numberOfCardTypes = count($countPerCardType);
        foreach ($countPerCardType as $cardType => $count) {
            $percentageToLeanTo = round(100 / $numberOfCardTypes);
            // The weights are relative values to each other.
            // We strive to reach 11% for each card type (there are 9).
            // The lower the card type is currently represented, the higher the chances are
            // this card type will be generated.
            $weightPerCardType[$cardType] = round($percentageToLeanTo / (($count / $total) * 100) * 100);
        }

        asort($weightPerCardType);
        $rand = mt_rand(1, (int) array_sum($weightPerCardType));

        foreach ($weightPerCardType as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                $output->write($key);

                return Command::SUCCESS;
            }
        }

        return Command::SUCCESS;
    }
}
