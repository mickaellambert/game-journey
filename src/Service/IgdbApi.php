<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class IgdbApi
{
    private $client;
    private $clientId;
    private $clientSecret;
    private $accessToken;

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

    public function selectGameById(int $id): ?array 
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
}
