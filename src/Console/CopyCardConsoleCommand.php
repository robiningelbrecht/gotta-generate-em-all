<?php

namespace App\Console;

use App\Domain\Card\Card;
use App\Domain\Card\CardId;
use App\Domain\Card\CardRepository;
use App\Domain\Card\CardType;
use App\Domain\Card\Creature\CreaturePool;
use App\Domain\Card\GenerateCard\GenerateCard;
use App\Domain\Card\Prompt;
use App\Domain\Pokemon\PokemonRarity;
use App\Domain\Pokemon\PokemonSize;
use App\Infrastructure\CQRS\CommandBus;
use App\Infrastructure\Environment\Settings;
use App\Infrastructure\Serialization\Json;
use App\Infrastructure\ValueObject\String\Description;
use App\Infrastructure\ValueObject\String\Name;
use App\Infrastructure\ValueObject\String\Svg;
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
    )
    {
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
        $copyFrom = Settings::getAppRoot() . '/' . $input->getArgument('path') . '/database/cards/data/1.json';
        $data = Json::decode(file_get_contents($copyFrom));

        $card = Card::create(
            CardId::fromString($data['cardId']),
            Prompt::fromString($data['promptForName']),
            Prompt::fromString($data['promptForDescription']),
            Prompt::fromString($data['promptForVisual']),
            Name::fromString($data['generatedName']),
            Description::fromString($data['generatedDescription']),
            (new \DateTimeImmutable())->setTimestamp($data['createdOn'])
        );

        $file = Settings::getAppRoot() . '/' . $input->getArgument('path') . '/public/cards/' . $card->getCardId() . '.svg';

        $this->cardRepository->save(
            $card,
            Svg::fromString(file_get_contents($file)),
        );

        return Command::SUCCESS;
    }
}
