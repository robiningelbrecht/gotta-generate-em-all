<?php

namespace App\Domain\Reddit;

use App\Infrastructure\Serialization\Json;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Reddit
{
    public function __construct(
        private readonly RedditUsername $username,
        private readonly RedditUserPassword $password,
        private readonly RedditClientId $clientId,
        private readonly RedditClientSecret $clientSecret,
        private readonly Client $client,
    ) {
    }

    private function request(
        string $method,
        string $path,
        array $options = []
    ): array {
        $response = $this->client->post('https://www.reddit.com/api/v1/access_token', [
            RequestOptions::AUTH => [
                (string) $this->clientId,
                (string) $this->clientSecret,
            ],
            RequestOptions::FORM_PARAMS => [
                'grant_type' => 'password',
                'username' => (string) $this->username,
                'password' => (string) $this->password,
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Authentication failed');
        }

        if (!$json = Json::decode($response->getBody()->getContents())) {
            throw new \RuntimeException('Authentication failed');
        }

        if (empty($json['access_token'])) {
            throw new \RuntimeException('Authentication failed');
        }

        $options['base_uri'] = 'https://oauth.reddit.com/';
        $options[RequestOptions::HEADERS]['Authorization'] = 'Bearer '.$json['access_token'];
        $options[RequestOptions::HEADERS]['User-Agent'] = 'php.GottaGenerateEmAll:v1.0 (by /u/GottaGenerateEmAll)';

        $response = $this->client->request($method, $path, $options);

        return Json::decode($response->getBody()->getContents());
    }

    public function submitLink(string $toSubreddit, string $title, string $url, string $flair_id = null): array
    {
        $options = [
            RequestOptions::FORM_PARAMS => [
                'api_type' => 'json',
                'sr' => $toSubreddit,
                'title' => $title,
                'kind' => 'link',
                'url' => $url,
                'flair_id' => $flair_id,
            ],
        ];

        return $this->request('POST', 'api/submit', $options);
    }

    public function comment(string $parent, string $text): array
    {
        $options = [
            RequestOptions::FORM_PARAMS => [
                'api_type' => 'json',
                'text' => $text,
                'thing_id' => $parent,
            ],
        ];

        return $this->request('POST', 'api/comment', $options);
    }

    public function moderateApprove(array $ids): array
    {
        $options = [
            RequestOptions::QUERY => ['raw_json' => 1, 'gilding_detail' => 1],
            RequestOptions::JSON => ['ids' => $ids],
        ];

        return $this->request('POST', 'api/v1/modactions/approve', $options);
    }
}
