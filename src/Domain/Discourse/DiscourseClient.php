<?php

namespace App\Domain\Discourse;

use App\Domain\Card\Card;
use App\Infrastructure\Serialization\Json;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Lcobucci\Clock\Clock;

class DiscourseClient
{
    public function __construct(
        private readonly Client $client,
        private readonly DiscourseApiKey $discourseApiKey,
        private readonly DiscourseDomain $discourseDomain,
        private readonly Clock $clock
    )
    {
    }

    public function message(Card $card): void
    {
        $body = [
            'topic_id' => 98,
            'raw' => sprintf("**[%s] Today's Pokémon is %s**

%s

---

![%s](%s)

---

Check the full archive on https://gotta-generate-em-all.com/ or https://www.reddit.com/r/GottaGenerateEmAll/

",
                $this->clock->now()->format('d-m-Y'),
                strtoupper($card->getGeneratedName()),
                $card->getGeneratedDescription(),
                $card->getGeneratedName(),
                $card->getFullUri()
            ),
        ];

        $this->client->request(
            'POST',
            rtrim((string)$this->discourseDomain, '/') . '/posts.json',
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json',
                    'Api-Key' => (string)$this->discourseApiKey,
                    'Api-Username' => 'PokemonAI',
                ],
                RequestOptions::JSON => $body,
            ]
        );
    }
}
