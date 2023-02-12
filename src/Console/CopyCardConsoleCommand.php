<?php

namespace App\Console;

use App\Domain\Card\CardId;
use App\Domain\Card\CardRepository;
use App\Domain\Card\CardType;
use App\Domain\Card\Creature\CreaturePool;
use App\Domain\Card\GenerateCard\GenerateCard;
use App\Domain\Pokemon\PokemonRarity;
use App\Domain\Pokemon\PokemonSize;
use App\Infrastructure\CQRS\CommandBus;
use App\Infrastructure\ValueObject\String\Name;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:card:copy', description: 'Copy a Pokémon card')]
class CopyCardConsoleCommand extends Command
{
    public function __construct(
        private readonly CardRepository $cardRepository
    ) {
        parent::__construct();
    }

    public function configure()
    {
        parent::configure();

        $this
            ->addArgument('path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $copyFrom = $input->getArgument('path').'database/cards/data/1.json';

        var_dump(file_get_contents($copyFrom));

        return Command::SUCCESS;
    }
}
