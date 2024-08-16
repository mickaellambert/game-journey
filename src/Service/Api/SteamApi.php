<?php

namespace App\Service\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SteamApi
{
    private HttpClientInterface $client;
    private string $clientId;

    public function __construct(HttpClientInterface $client, string $clientId)
    {
        $this->client = $client;
        $this->clientId = $clientId;
    }

    public function findAllGames(int $steamId): ?array 
    {
        $response = $this->client->request(
            'GET',
            'http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/',
            [
                'query' => [
                    'key' => $this->clientId,
                    'steamid' => $steamId,
                    'include_appinfo' => 1,
                    'include_played_free_games' => 1,
                    'format' => 'json'
                ]
            ]
        );

        $data = $response->toArray();

        return $data['response']['games'] ?? [];
    }
}
