<?php

namespace Connectors;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class SmsConnector
{

    public function __construct(){
    }

    public static function sendRequest($method, $url, $data,$header=null)
    {
        $body=[];
        if($header){
            $body['headers']=$header;
        }
        if($data){
            $body['json']=$data;
        }
        $body['http_errors'] = true;
        
        $client = new Client();
        $response= $client->request($method, $url , $body);
        return  $response;
       
    }

/*     public static function sendSMS($tagname, $numbers, $msg, $variableList, $replacementList, $sendDateTime=0){

        $username = config('yamamah.USER_NAME');
        $password = config('yamamah.PASSWORD');
        $url = config('yamamah.URL');
        $headers['Content-Type'] = 'application/json';
        if($tagname == null){
            $tagname=$username;
        }

        $data = [
            'Username' => $username,
            'Password' => $password,
            'Tagname' => $tagname,
            'RecepientNumber' => $numbers,
            'VariableList' => $variableList,
            'ReplacementList' => $replacementList,
            'Message' => $msg,
            'SendDateTime' => $sendDateTime
        ];
        
        $response = SmsConnector::sendRequest('POST', $url,$data, $headers);

        $jsonResponse = json_decode($response->getBody()->getContents(),true);

        $retuenResponse['code_status'] = $jsonResponse['Status'];

        if($jsonResponse['Status'] == config('yamamahSMSResponse.code_1')){
            $retuenResponse['success'] = true;
        }else{
            $retuenResponse['success'] = false;
        }
        $retuenResponse['status_description'] = $jsonResponse['StatusDescription'];
        return $retuenResponse;
    } */

    public static function sendSMS($numbers, $msg){

        $ar['userName'] = config('msegat.USER_NAME');
        $ar['userSender'] = 'Mjlsi';
		$ar['apiKey'] = config('msegat.APIKEY');
        $ar['msgEncoding'] = "UTF8";
        $ar['By'] = "Link";
        $ar['reqDlr'] = false;
        $ar['numbers'] = $numbers;
        $ar['msg'] = $msg;
        
		$url = config('msegat.URL');
        $headers['Content-Type'] = 'application/json';
        try{
            $response = SmsConnector::sendRequest('POST', $url, $ar, $headers);
    
            $jsonResponse = json_decode($response->getBody()->getContents(),true);
          
            if($jsonResponse == '1' || $jsonResponse == 'M0000'){
                $retuenResponse['success'] = true;
            }else{
                $retuenResponse['success'] = false;
            }
       
            return $retuenResponse;
        } catch(Exception $e){
            return null;
        }
    }

    public static function getCredet()
	{
		$ar['userName'] = null;//config('msegat.USER_NAME');
		$ar['apiKey'] = null;//config('msegat.APIKEY');
		$ar['msgEncoding'] = null;//"UTF8";
		$w = http_build_query($ar);
		$url = "http://msegat.com/gw/Credits.php?".$w;
        
        
        $headers['Content-Type'] = 'multipart/form-data';
        

        
        
        $response = SmsConnector::sendRequest('POST', $url,null, $headers);

        $jsonResponse = json_decode($response->getBody()->getContents(),true);
   
        $retuenResponse['code_status'] = $jsonResponse['Status'];

        if($jsonResponse['Status'] == config('yamamahSMSResponse.code_1')){
            $retuenResponse['success'] = true;
        }else{
            $retuenResponse['success'] = false;
        }
        $retuenResponse['status_description'] = $jsonResponse['StatusDescription'];
        return $retuenResponse;
	}
}
