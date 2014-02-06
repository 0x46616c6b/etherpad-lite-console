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
     * @return boolean
     */
    public static function deletePad($padId, $apiKey, $host = 'http://localhost:9001')
    {
        $client = new Client($apiKey, $host);
        try {
           $response = $client->deletePad($padId);
        } catch (\Exception $e) {
            return false;
        }

        if ($response->getCode() == Response::CODE_OK) {
            return true;
        } else {
            return false;
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

        try {
            $response = $client->listAllPads();
        } catch (\Exception $e) {
            return false;
        }

        if ($response->getCode() == Response::CODE_OK) {
            return $response->getData()['padIDs'];
        } else {
            return false;
        }
    }

    /**
     * @param $padId
     * @param $apiKey
     * @param string $host
     * @return int|false
     */
    public static function getLastEdited($padId, $apiKey, $host = 'http://localhost:9001')
    {
        $client = new Client($apiKey, $host);
        try {
            $response = $client->getLastEdited($padId);
        } catch (\Exception $e) {
            return false;
        }

        if ($response->getCode() == Response::CODE_OK) {
            return $response->getData()['lastEdited'] / 1000;
        } else {
            return false;
        }
    }
}