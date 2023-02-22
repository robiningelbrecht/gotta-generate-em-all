<?php

namespace App\Console;

use App\Domain\Card\CardRepository;
use App\Infrastructure\Environment\Settings;
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
        private readonly CardRepository $cardRepository
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
        $readme = \Safe\file_get_contents($pathToReadMe);

        $readme = preg_replace(
            '/<!--START_SECTION:pokemon-name-->(.*?)<!--END_SECTION:pokemon-name-->/',
            '<!--START_SECTION:pokemon-name-->'.strtoupper($cardOfTheDay->getGeneratedName()).'<!--END_SECTION:pokemon-name-->',
            $readme
        );
        $readme = preg_replace(
            '/<!--START_SECTION:pokemon-visual-->\s(.*?)\s<!--END_SECTION:pokemon-visual-->/m',
            implode("\n", [
                '<!--START_SECTION:pokemon-visual-->',
                '<img src="'.$cardOfTheDay->getFullUri().'" alt="'.$cardOfTheDay->getGeneratedName().'">',
                '<!--END_SECTION:pokemon-visual-->',
            ]),
            $readme
        );

        \Safe\file_put_contents(
            $pathToReadMe,
            $readme
        );

        return Command::SUCCESS;
    }
}
