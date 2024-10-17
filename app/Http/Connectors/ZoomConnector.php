<?php

namespace Connectors;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Log;

class ZoomConnector
{  

    public function __construct(){

    }

    /**
     *
     * Send Http Request to zoom
     *
     * 
     * @param array $data
     *
     * @return Response
     */
    public static function sendRequest($method, $url, $data,$header=null)
    {
        $body=[];
        if($header){
            $body['headers']=$header;
        }
        if($data){
            $body['json']=$data;
        }
        $body['http_errors'] = false;

        $client = new Client(['base_uri' => config('zoom.apiBaseURL')]);
        ZoomConnector::logData(config('zoom.apiBaseURL').$url,$method,$body,"",$header);
        $response= $client->request($method, $url , $body);
        ZoomConnector::logData(config('zoom.apiBaseURL').$url,$method,$body,$response,$header);
        return  $response;
    }

    /**
     *
     * Generate jwt token to zoom
     *
     * @return token
     */
    private static function generateToken($zoom_api_key,$zoom_api_secret,$zoom_exp_minutes) 
    {
        $header = json_encode(["typ" => "JWT","alg" => "HS256"]);
        $payload = json_encode(["aud" => null,"iss" => $zoom_api_key,"exp" => Carbon::now()->addMinutes($zoom_exp_minutes)->timestamp,"iat" => Carbon::now()->timestamp]);
        
        $base64Header =  base64_encode($header);
        $base64Payload = base64_encode($payload);

        $signature = hash_hmac('sha256',$base64Header . '.' . $base64Payload,$zoom_api_secret,true);
        $base64Signature = base64_encode($signature);

        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    /**
     *
     * Generate header for request
     *
     * @return header
     */
    private static function generateHeader($zoom_api_key,$zoom_api_secret,$zoom_exp_minutes) 
    {
        $header = ['content-type' => 'application/json'];
        $header['Authorization'] = 'Bearer '.ZoomConnector::generateToken($zoom_api_key,$zoom_api_secret,$zoom_exp_minutes);

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
     * Authentication to zoom
     *
     * 
     * @return Response
     */
    public static function authentication($headerData){
        $header = ZoomConnector::generateHeader($headerData['zoom_api_key'],$headerData['zoom_api_secret'],$headerData['zoom_exp_minutes']);
        $response = ZoomConnector::sendRequest('GET', 'users/me/token', null,$header);

        return ZoomConnector::getResponse($response, config('zoom.success_code'),null);
    }

    /**
     *
     * create meeting on zoom
     *
     * 
     * @return Response
     */
    public static function createMeeting($data){
        $header = ZoomConnector::generateHeader($data['header']['zoom_api_key'],$data['header']['zoom_api_secret'],$data['header']['zoom_exp_minutes']);
        $body = $data;
        //unset($data['header']);
        $response = ZoomConnector::sendRequest('POST', 'users/me/meetings', $data,$header);
        return ZoomConnector::getResponse($response, config('zoom.success_created_code'),$data);
    }

    /**
     *
     * update meeting on zoom
     *
     * 
     * @return Response
     */
    public static function updateMeeting($data,$meetingId){
        $header = ZoomConnector::generateHeader($data['header']['zoom_api_key'],$data['header']['zoom_api_secret'],$data['header']['zoom_exp_minutes']);
        $body = $data;
        unset($data['header']);
        $response = ZoomConnector::sendRequest('PATCH', 'meetings/'.$meetingId, $data,$header);
        return ZoomConnector::getResponse($response, config('zoom.success_updated_code'),$data);
    }

    /**
     *
     * get meeting on zoom
     *
     * 
     * @return Response
     */
    public static function getMeeting($meetingId,$headerData){
        $header = ZoomConnector::generateHeader($headerData['zoom_api_key'],$headerData['zoom_api_secret'],$headerData['zoom_exp_minutes']);
        $response = ZoomConnector::sendRequest('GET', 'meetings/'.$meetingId, null,$header);
        return ZoomConnector::getResponse($response, config('zoom.success_code'),null);
    }

    /**
     *
     * Register a participant for a meeting on zoom
     *
     * 
     * @return Response
     */
    public static function registerParticipantMeeting($data,$meetingId){
        $header = ZoomConnector::generateHeader($data['header']['zoom_api_key'],$data['header']['zoom_api_secret'],$data['header']['zoom_exp_minutes']);
        unset($data['header']);
        $response = ZoomConnector::sendRequest('POST', 'meetings/'.$meetingId. '/registrants', $data,$header);

        return ZoomConnector::getResponse($response, config('zoom.success_created_code'),$data);
    }

    /**
     *
     * end a meeting on zoom
     *
     * 
     * @return Response
     */
    public static function endMeeting($meetingId,$headerData){
        $header = ZoomConnector::generateHeader($headerData['zoom_api_key'],$headerData['zoom_api_secret'],$headerData['zoom_exp_minutes']);
        $data = ['action' => config('zoom.endMeetingAction')];
        $response = ZoomConnector::sendRequest('PUT', 'meetings/'.$meetingId. '/status', $data,$header);


        return ZoomConnector::getResponse($response, config('zoom.success_updated_code'),$data);
    }

    /**
     *
     * get user data
     *
     * 
     * @return Response
     */
    public static function getUser($headerData){
        $header = ZoomConnector::generateHeader($headerData['zoom_api_key'],$headerData['zoom_api_secret'],$headerData['zoom_exp_minutes']);
        $response = ZoomConnector::sendRequest('GET', 'users/me', null,$header);

        return ZoomConnector::getResponse($response, config('zoom.success_code'),null);
    }

    public static function logData($url,$method,$body,$response,$headers){
        Log::channel('zoom')->info(['url'=> $url, 'method' => $method,'request_body' => $body,'headers' => $headers,'request_response' => $response]);
    }
}