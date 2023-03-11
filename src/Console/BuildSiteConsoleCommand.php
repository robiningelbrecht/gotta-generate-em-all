<?php

namespace App\Console;

use App\Domain\Card\CardRepository;
use App\Domain\ReadMe;
use App\Domain\Sitemap;
use App\Infrastructure\Environment\Settings;
use Lcobucci\Clock\Clock;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

#[AsCommand(name: 'app:build:site', description: 'Build site')]
class BuildSiteConsoleCommand extends Command
{
    public function __construct(
        private readonly Environment $twig,
        private readonly CardRepository $cardRepository,
        private readonly Clock $clock
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pathToBuildDir = Settings::getAppRoot().'/build';

        $cards = $this->cardRepository->findAll();
        $cardOfTheDay = array_shift($cards);

        $numberOfCardsPerPage = 12;
        $totalNumberOfPages = ceil(count($cards) / $numberOfCardsPerPage);
        $cardsForFirstPage = array_slice($cards, 0, $numberOfCardsPerPage);

        $template = $this->twig->load('index.html.twig');
        $cardsTemplate = $this->twig->load('cards.html.twig');
        \Safe\file_put_contents($pathToBuildDir.'/index.html', $template->render([
            'cardOfTheDay' => $cardOfTheDay,
            'totalPages' => $totalNumberOfPages,
            'cards' => $cardsTemplate->render([
                'cards' => $cardsForFirstPage,
            ]),
        ]));

        for ($i = 0; $i < $totalNumberOfPages; ++$i) {
            $cardsForPage = array_splice($cards, 0, $numberOfCardsPerPage);
            \Safe\file_put_contents($pathToBuildDir.'/pages/page-'.($i + 1).'.html', $cardsTemplate->render([
                'cards' => $cardsForPage,
            ]));
        }

        $pathToReadMe = Settings::getAppRoot().'/README.md';
        $readme = ReadMe::fromPathToReadMe($pathToReadMe);
        $readme
            ->updatePokemonName(strtoupper($cardOfTheDay->getGeneratedName()))
        ->updatePokemonVisual('<img src="'.$cardOfTheDay->getFullUri().'" alt="'.$cardOfTheDay->getGeneratedName().'">');

        \Safe\file_put_contents(
            $pathToReadMe,
            (string) $readme
        );

        $pathToSiteMap = Settings::getAppRoot().'/build/sitemap.xml';
        $sitemap = Sitemap::fromPath($pathToSiteMap);
        $sitemap->updateLastMod($this->clock->now());

        \Safe\file_put_contents(
            $pathToSiteMap,
            (string) $sitemap
        );

        return Command::SUCCESS;
    }
}
