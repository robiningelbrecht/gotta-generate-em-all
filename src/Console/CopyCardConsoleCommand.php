<?php

namespace App\Console;

use App\Domain\Card\Card;
use App\Domain\Card\CardId;
use App\Domain\Card\CardRepository;
use App\Domain\Card\CardType;
use App\Domain\Card\Prompt;
use App\Domain\FileType;
use App\Infrastructure\Environment\Settings;
use App\Infrastructure\Serialization\Json;
use App\Infrastructure\ValueObject\String\Description;
use App\Infrastructure\ValueObject\String\Name;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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
        $copyFrom = Settings::getAppRoot().'/'.$input->getArgument('path').'/database/cards/data/1.json';
        $data = Json::decode(file_get_contents($copyFrom));

        $card = Card::create(
            CardId::fromString($data['cardId']),
            Prompt::fromString($data['promptForName']),
            Prompt::fromString($data['promptForDescription']),
            Prompt::fromString($data['promptForVisual']),
            Name::fromString($data['generatedName']),
            Description::fromString($data['generatedDescription']),
            (new \DateTimeImmutable())->setTimestamp($data['createdOn']),
            cardType::from($data['cardType']),
            FileType::from($data['fileType'])
        );

        $file = Settings::getAppRoot().'/'.$input->getArgument('path').'/public/cards/'.$card->getCardId().'.'.$card->getFileType()->value;

        $this->cardRepository->save(
            $card,
            file_get_contents($file),
        );

        $output->write(sprintf('Generated an new %s-type Pokémon named %s', $card->getCardType()->value, $card->getGeneratedName()));

        return Command::SUCCESS;
    }
}
