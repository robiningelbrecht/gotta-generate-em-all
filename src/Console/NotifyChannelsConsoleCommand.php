<?php

namespace App\Console;

use App\Domain\Card\CardRepository;
use App\Domain\Reddit\Reddit;
use App\Domain\Discourse\DiscourseClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:notify:channels', description: 'Notify channels')]
class NotifyChannelsConsoleCommand extends Command
{
    public function __construct(
        private readonly CardRepository $cardRepository,
        private readonly DiscourseClient $discourseClient,
        private readonly Reddit $reddit,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$card = $this->cardRepository->findMostRecent()) {
            return Command::SUCCESS;
        }

        $this->discourseClient->message($card);

       $response = $this->reddit->submitLink(
            'GottaGenerateEmAll',
            sprintf("[%s] Today's Pokémon is %s", $card->getCreatedOn()->format('d-m-Y'), strtoupper($card->getGeneratedName())),
            $card->getFullUri(),
            'c03f451a-c67c-11ed-bd35-5aa4a7173c6d',
        );

        if (empty($response['json']['data']['name'])) {
            return Command::SUCCESS;
        }

        sleep(2);
        $this->reddit->moderateApprove([$response['json']['data']['name']]);

        sleep(2);
        $this->reddit->comment($response['json']['data']['name'], '> '.$card->getGeneratedDescription());

        return Command::SUCCESS;
    }
}
