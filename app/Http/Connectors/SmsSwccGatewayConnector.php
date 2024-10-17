<?php

namespace Connectors;

use Exception;
use GuzzleHttp\Client;
use Log;

class SmsSwccGatewayConnector
{

    public function __construct()
    {
    }

    public static function sendRequest($method, $url, $data, $header = null)
    {
        $body = [];
        if ($header) {
            $body['headers'] = $header;
        }
        if ($data) {
            $body['json'] = $data;
        }

        $client = new Client();
        try {
            $response = $client->request($method, $url, $body);
            Log::channel('sms')->info(['response' => $response, 'method' => $method, 'url' => $url, 'body' => $body]);
        } catch (\Exception $e) {
            Log::channel('sms')->info(['exception_message' => $e->getMessage(), 'method' => $method, 'url' => $url, 'body' => $body]);
        }
        return $response;

    }
    public static function sendSMS($numbers, $msg)
    {

        $baseURL = config('swccGateway.URL');
        $queryParams = http_build_query([
            'appName' => 'committees',
            'mobileNo' => $numbers,
            'message' => $msg,
        ]);
        $url = $baseURL . '?' . $queryParams;
        $headers['Content-Type'] = 'application/json';
        try {
            $response = SmsSwccGatewayConnector::sendRequest('GET', $url, null, $headers);

            $jsonResponse = json_decode($response->getBody()->getContents(), true);

            if ($jsonResponse == '1' || $jsonResponse == 'M0000') {
                $retuenResponse['success'] = true;
            } else {
                $retuenResponse['success'] = false;
            }

            return $retuenResponse;
        } catch (Exception $e) {
            return null;
        }
    }
}
