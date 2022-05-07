<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ApiService
{
    const TIMEOUT = 120;

    protected $baseUri = '';

    protected $apiKey = '';

    /**
     * @param $method
     * @param $uri
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function getResponse($method, $uri, $data)
    {
        try {
            $client = new Client([
                'base_uri' => $this->baseUri,
                'timeout'  => self::TIMEOUT,
            ]);

            $response = $client->request($method, $uri, $data);

            $result = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

            Log::channel('api')->info('external request', [
                'uri'      => $uri,
                'request'  => $data,
                'response' => $result
            ]);

            return $result;
        } catch (GuzzleException $e) {
            Log::channel('api')->error('external request', [
                'uri'      => $uri,
                'request'  => $data,
                'response' => $e->getResponse()->getBody()->getContents()
            ]);

            throw $e;
        } catch (\Exception $e) {
            Log::channel('api')->error('external request', [
                'uri'      => $uri,
                'request'  => $data,
                'response' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
