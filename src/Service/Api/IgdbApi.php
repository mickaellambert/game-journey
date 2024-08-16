<?php

namespace App\Service\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class IgdbApi
{
    private HttpClientInterface $client;
    private string $clientId;
    private string $clientSecret;
    private string $accessToken;

    public function __construct(HttpClientInterface $client, string $clientId, string $clientSecret)
    {
        $this->client = $client;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken = $this->getAccessToken();
    }

    private function getAccessToken(): string
    {
        $response = $this->client->request('POST', 'https://id.twitch.tv/oauth2/token', [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = $response->toArray();
        return $data['access_token'];
    }

    public function findGameById(int $id): ?array 
    {
        $url = 'https://api.igdb.com/v4/games';

        $response = $this->client->request('POST', $url, [
            'headers' => [
                'Client-ID' => $this->clientId,
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'text/plain',
            ],
            'body' => "fields id, name, platforms.name, first_release_date, summary, cover.url, rating, genres.name, game_modes.name, involved_companies.company.name, age_ratings.rating; where id = $id;",
        ]);

        $data = $response->toArray();
        return $data[0] ?? [];
    }

    public function findAllByDump(string $endpoint): string
    {
        $url = 'https://api.igdb.com/v4/dumps/' . $endpoint;

        $response = $this->client->request('GET', $url, [
            'headers' => [
                'Client-ID' => $this->clientId,
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/json',
            ],
        ]);

        $data = $response->toArray();

        // Download the dump file
        $fileResponse = $this->client->request('GET', $data['s3_url']);
        $fileContent = $fileResponse->getContent();

        // Save the dump file locally
        $filePath = 'dumps/' . $data['file_name'];
        file_put_contents($filePath, $fileContent);

        return $filePath;
    }
}
