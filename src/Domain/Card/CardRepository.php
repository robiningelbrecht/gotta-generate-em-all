<?php

namespace App\Domain\Card;

use App\Domain\FileType;
use App\Infrastructure\Environment\Settings;
use App\Infrastructure\Exception\EntityNotFound;
use App\Infrastructure\ValueObject\String\Description;
use App\Infrastructure\ValueObject\String\Name;
use SleekDB\Store;

class CardRepository
{
    public function __construct(
        private readonly Store $store
    ) {
    }

    public function find(CardId $cardId): Card
    {
        $file = Settings::getAppRoot().'/cards/'.$cardId.'.png';

        if (!file_exists($file)) {
            throw new EntityNotFound(sprintf('Card "%s" not found', $cardId));
        }

        if (!$row = $this->store->findOneBy(['cardId', '==', $cardId])) {
            throw new EntityNotFound(sprintf('Card "%s" not found', $cardId));
        }

        return $this->buildFromResult($row);
    }

    /**
     * @return \App\Domain\Card\Card[]
     */
    public function findAll(): array
    {
        return array_map(
            fn (array $row) => $this->buildFromResult($row),
            $this->store->findAll(['createdOn' => 'DESC'])
        );
    }

    public function countByCardType(): array
    {
        $countByCardType = [];
        $results = $this->store->createQueryBuilder()
            ->groupBy(['cardType'], 'count')
            ->getQuery()
            ->fetch();

        foreach ($results as $result) {
            $countByCardType[$result['cardType']] = $result['count'];
        }

        return $countByCardType;
    }

    public function findMostRecent(): ?Card
    {
        if ($rows = $this->store->findAll(['createdOn' => 'DESC'], 1)) {
            return $this->buildFromResult(reset($rows));
        }

        return null;
    }

    public function save(
        Card $card,
        string $fileContents,
    ): void {
        $file = Settings::getAppRoot().'/cards/'.$card->getCardId().'.'.$card->getFileType()->value;
        file_put_contents($file, $fileContents);

        $this->store->updateOrInsert([
            'cardId' => $card->getCardId(),
            'promptForName' => $card->getPromptForPokemonName(),
            'promptForDescription' => $card->getPromptForPokemonDescription(),
            'promptForVisual' => $card->getPromptForVisual(),
            'generatedName' => $card->getGeneratedName(),
            'generatedDescription' => $card->getGeneratedDescription(),
            'createdOn' => $card->getCreatedOn()->getTimestamp(),
            'cardType' => $card->getCardType()->value,
            'fileType' => $card->getFileType()->value,
        ]);
    }

    private function buildFromResult(array $result): Card
    {
        return Card::fromState(
            CardId::fromString($result['cardId']),
            Prompt::fromString($result['promptForName']),
            Prompt::fromString($result['promptForDescription']),
            Prompt::fromString($result['promptForVisual']),
            Name::fromString($result['generatedName']),
            Description::fromString($result['generatedDescription']),
            (new \DateTimeImmutable())->setTimestamp($result['createdOn']),
            CardType::from($result['cardType']),
            FileType::from($result['fileType'])
        );
    }
}
