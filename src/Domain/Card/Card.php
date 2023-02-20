<?php

namespace App\Domain\Card;

use App\Domain\FileType;
use App\Infrastructure\ValueObject\String\Description;
use App\Infrastructure\ValueObject\String\Name;

class Card
{
    private function __construct(
        private readonly CardId $cardId,
        private readonly Prompt $promptForPokemonName,
        private readonly Prompt $promptForPokemonDescription,
        private readonly Prompt $promptForVisual,
        private readonly Name $generatedName,
        private readonly Description $generatedDescription,
        private readonly \DateTimeImmutable $createdOn,
        private readonly ?CardType $cardType,
        private readonly ?FileType $fileType,
    ) {
    }

    public function getCardId(): CardId
    {
        return $this->cardId;
    }

    public function getPromptForPokemonName(): Prompt
    {
        return $this->promptForPokemonName;
    }

    public function getPromptForPokemonDescription(): Prompt
    {
        return $this->promptForPokemonDescription;
    }

    public function getPromptForVisual(): Prompt
    {
        return $this->promptForVisual;
    }

    public function getGeneratedName(): Name
    {
        return $this->generatedName;
    }

    public function getGeneratedDescription(): Description
    {
        return $this->generatedDescription;
    }

    public function getCreatedOn(): \DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function getCardType(): CardType
    {
        return $this->cardType;
    }

    public function getFileType(): FileType
    {
        return $this->fileType;
    }

    public function getFullUri(): string
    {
        return 'https://raw.githubusercontent.com/robiningelbrecht/gotta-generate-em-all/master/cards/'.$this->getCardId().'.'.$this->getFileType()->value;
    }

    public static function create(
        CardId $cardId,
        Prompt $promptForPokemonName,
        Prompt $promptForPokemonDescription,
        Prompt $promptForVisual,
        Name $generatedName,
        Description $generatedDescription,
        \DateTimeImmutable $createdOn,
        CardType $cardType,
        FileType $fileType,
    ): self {
        return new self(
            $cardId,
            $promptForPokemonName,
            $promptForPokemonDescription,
            $promptForVisual,
            $generatedName,
            $generatedDescription,
            $createdOn,
            $cardType,
            $fileType,
        );
    }

    public static function fromState(
        CardId $cardId,
        Prompt $promptForPokemonName,
        Prompt $promptForPokemonDescription,
        Prompt $promptForVisual,
        Name $generatedName,
        Description $generatedDescription,
        \DateTimeImmutable $createdOn,
        CardType $cardType,
        FileType $fileType,
    ): self {
        return new self(
            $cardId,
            $promptForPokemonName,
            $promptForPokemonDescription,
            $promptForVisual,
            $generatedName,
            $generatedDescription,
            $createdOn,
            $cardType,
            $fileType,
        );
    }
}
