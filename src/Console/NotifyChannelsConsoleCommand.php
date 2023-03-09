<?php

namespace App\Console;

use App\Domain\Card\CardRepository;
use App\Domain\Slack\SlackClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:notify:channels', description: 'Notify channels')]
class NotifyChannelsConsoleCommand extends Command
{
    public function __construct(
        private readonly CardRepository $cardRepository,
        private readonly SlackClient $slackClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$card = $this->cardRepository->findMostRecent()) {
            return Command::SUCCESS;
        }

        $this->slackClient->message($card);

        return Command::SUCCESS;
    }
}
