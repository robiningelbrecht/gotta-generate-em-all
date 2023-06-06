<?php

namespace App\Domain\Slack;

use App\Domain\Card\Card;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class SlackClient
{
    public function __construct(
        private readonly Client $client,
        private readonly SlackWebhookUrl $webhookUrl
    ) {
    }

    public function message(Card $card): void
    {
        $message = [
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => sprintf("Today's Pokémon is %s", strtoupper($card->getGeneratedName())),
                        'emoji' => true,
                    ],
                ],
                [
                    'type' => 'context',
                    'elements' => [
                        0 => [
                            'type' => 'plain_text',
                            'text' => $card->getGeneratedDescription(),
                            'emoji' => true,
                        ],
                    ],
                ],
                [
                    'type' => 'divider',
                ],
                [
                    'type' => 'image',
                    'image_url' => $card->getFullUri(),
                    'alt_text' => ucfirst($card->getGeneratedName()),
                ],
                [
                    'type' => 'divider',
                ],
                [
                    'type' => 'context',
                    'elements' => [
                        0 => [
                            'type' => 'mrkdwn',
                            'text' => 'Check the full archive on https://gotta-generate-em-all.com/ or https://www.reddit.com/r/GottaGenerateEmAll/',
                        ],
                    ],
                ],
            ],
        ];

        var_dump($message);

        $this->client->request('POST', (string) $this->webhookUrl, [RequestOptions::JSON => $message]);
    }
}
