<?php

namespace Connectors;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Log;

class MicrosoftTeamConnector
{  
    public function __construct(){

    }

    /**
     *
     * Send Http Request to microsoft graph
     *
     * 
     * @param array $data
     *
     * @return Response
     */
    public static function sendRequest($baseURL,$method, $url, $data,$header=null,$islogin=false)
    {
        $body=[];
        if($header){
            $body['headers']=$header;
        }
        if($data && $islogin){
            $body['form_params']=$data;
        } else {
            $body['json']=$data;
        }
        $body['http_errors'] = false;

        $client = new Client(['base_uri' => $baseURL]);
        MicrosoftTeamConnector::logData($baseURL.$url,$method,$body,"",$header);
        $response= $client->request($method, $url , $body);
        MicrosoftTeamConnector::logData($baseURL.$url,$method,$body,$response,$header);
        return  $response;
    }

    /**
     *
     * Get token form microsoft graph
     *
     * @return token
     */
    private static function getToken($appClientId,$appTenantId,$appClientSecret) 
    {
        $header = ['content-type' => 'application/x-www-form-urlencoded'];
        $data = ['client_id' => $appClientId, 'scope' => config('microsoftGraph.scope'),'client_secret' => $appClientSecret,'grant_type' => config('microsoftGraph.grantType')];
        
        $response = MicrosoftTeamConnector::sendRequest(config('microsoftGraph.loginBaseURL'),'GET', $appTenantId. config('microsoftGraph.loginEndPoint'), $data,$header, true);
        $responseFormated = MicrosoftTeamConnector::getResponse($response, config('microsoftGraph.successCode'),null);

        return $responseFormated['is_success']? $responseFormated['response']['access_token'] : null;        
    }

    /**
     *
     * Generate header for request
     *
     * @return header
     */
    private static function generateHeader($appClientId,$appTenantId,$appClientSecret) 
    {
        $header = ['content-type' => 'application/json'];
        $header['Authorization'] = 'Bearer '.MicrosoftTeamConnector::getToken($appClientId,$appTenantId,$appClientSecret);

        return $header;
    }
    /**
     *
     * get response of request
     *
     * @return response
     */
    private static function getResponse($response, $responseCode, $data)
    {
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();

        if ($statusCode == $responseCode) {
            return ['is_success' => true,'response' => $jsonResponse, 'request' => $data];
        } else {
            return ['is_success' => false,'response' => $jsonResponse, 'request' => $data];
        }
    }


    /**
     *
     * create meeting on microsoft graph
     *
     * 
     * @return Response
     */
    public static function createMeeting($data){
        $header = MicrosoftTeamConnector::generateHeader($data['header']['microsoft_azure_app_id'],$data['header']['microsoft_azure_tenant_id'],$data['header']['microsoft_azure_client_secret']);
        $body = $data;
        unset($data['header']);
        $response = MicrosoftTeamConnector::sendRequest(config('microsoftGraph.apiBaseURL'),'POST', 'app/onlineMeetings', $data,$header, false);
        return MicrosoftTeamConnector::getResponse($response, config('microsoftGraph.meetingSuccessCode'),$body);
    }

    //log request 
    public static function logData($url,$method,$body,$response,$headers){
        Log::channel('microsoft_teams')->info(['url'=> $url, 'method' => $method,'request_body' => $body,'headers' => $headers,'request_response' => $response]);
    }
}