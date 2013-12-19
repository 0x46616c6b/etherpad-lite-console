<?php

namespace EtherpadLite\Helper;

use EtherpadLite\Client;
use EtherpadLite\Response;

class Pad
{
    /**
     * @param $padId
     * @param $apiKey
     * @param string $host
     * @throws \Exception
     * @return string
     */
    public static function deletePad($padId, $apiKey, $host = 'http://localhost:9001')
    {
        $client = new Client($apiKey, $host);
        $response = $client->deletePad($padId);

        if ($response->getCode() == Response::CODE_OK) {
            return 'The pad was deleted successfully!';
        } else {
            throw new \Exception('An error occurred!' . "\n" . $response->getMessage());
        }
    }

    /**
     * @param $apiKey
     * @param string $host
     * @return array
     * @throws \Exception
     */
    public static function getAllPadIds($apiKey, $host = 'http://localhost:9001')
    {
        $client = new Client($apiKey, $host);
        $response = $client->listAllPads();

        if ($response->getCode() == Response::CODE_OK) {
            return $response->getData()['padIDs'];
        } else {
            throw new \Exception('An error occurred!' . "\n" . $response->getMessage());
        }
    }

    /**
     * @param $padId
     * @param $apiKey
     * @param string $host
     * @return int
     * @throws \Exception
     */
    public static function getLastEdited($padId, $apiKey, $host = 'http://localhost:9001')
    {
        $client = new Client($apiKey, $host);
        $response = $client->getLastEdited($padId);

        if ($response->getCode() == Response::CODE_OK) {
            return $response->getMessage() * 1000;
        } else {
            throw new \Exception('An error occurred!' . "\n" . $response->getMessage());
        }
    }
}