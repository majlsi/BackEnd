<?php

namespace Connectors;

use GuzzleHttp\Client;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Exception\GuzzleException;
use Tymon\JWTAuth\Facades\JWTAuth;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\BadResponseException;
use Log;


class StcConnector
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
    public static function sendRequest($method, $url, $data,$header=null,$isLogin = false)
    {
        $body=[];
        if($header){
            $body['headers']=$header;
        }
        if($data && $isLogin){
            $body['form_params']=$data;
        } else {
            $body['json']=$data;
        }
        $client = new Client();

        // try
        // {
            $response= $client->request($method, $url , $body);
            StcConnector::logData($url,$method,$body,$response,$header);
           
            return StcConnector::getResponse($response, config('stcConfig.successCode'),$body);
        // }
        // catch (GuzzleException $e)
        // {
        //     $response = ChatConnector::StatusCodeHandling($e,$method, $url , $body);
        //     StcConnector::logData($url,$method,$body,$response,$header);
        //     return $response;
        // }
    }

    
    /**
     *
     * Hendle Error Code 
     *
     * 
     * @param Response $e
     * @return Response
     */
    public static function StatusCodeHandling($e, $method, $url , $body)
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $response = json_decode($e->getResponse()->getBody()->getContents(), true);
        return ['is_success' => false, 'response' =>['error' => $response,'status_code'=> $statusCode]];
       
    }


    public static function sendEventCallBack($event){
        $header = StcConnector::generateHeader();
        $data = [];
        $data['status'] = $event['status'];
        if($event['status']=='error'){
            $data['message'] = $event['error'];
        }
        $data['ref_number'] = config('stcConfig.ref').$event['id'];
        $data['tenant'] = $event['tenant'];
        $response = StcConnector::sendRequest('PUT', config('stcConfig.callBackUrl') . $event['event_id'] .'/', $data,$header, false);
        return $response;

    }

    /**
     *
     * Get token form microsoft graph
     *
     * @return token
     */
    private static function getToken() 
    {
        $header = ['content-type' => 'application/x-www-form-urlencoded'];
        $data = ['client_id' => config('stcConfig.clientId'), 'client_secret' => config('stcConfig.clientSecret'),'grant_type' => config('stcConfig.grantType')];
        
        $response = StcConnector::sendRequest('POST' , config('stcConfig.tokenUrl'), $data,$header,true);

        return $response['is_success']? $response['response']['access_token'] : null;        
    }

    /**
     *
     * Generate header for request
     *
     * @return header
     */
    private static function generateHeader() 
    {
        $header = ['content-type' => 'application/json'];
        $header['Authorization'] = 'Bearer '.StcConnector::getToken();

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


    //log request 
    public static function logData($url,$method,$body,$response,$headers){
        Log::channel('stc_events')->info(['url'=> $url, 'method' => $method,'request_body' => $body,'headers' => $headers,'request_response' => $response]);
    }

    public static function logWebhookData($url,$method,$body,$response,$headers,$payLoad){
        Log::channel('stc_events')->info(['url'=> $url, 'method' => $method,'request_body' => $body,'headers' => $headers,'request_response' => $response]);
        if(isset($payLoad)){
            Log::channel('stc_events')->info("-------------------------");
            Log::channel('stc_events')->info("Payload");
            Log::channel('stc_events')->info("-------------------------");
            Log::channel('stc_events')->info($payLoad);
        }
    }
}