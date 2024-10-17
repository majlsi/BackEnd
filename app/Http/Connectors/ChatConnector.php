<?php
namespace Connectors;

use Illuminate\Support\Facades\Config;
use GuzzleHttp\Exception\GuzzleException;
use Tymon\JWTAuth\Facades\JWTAuth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\BadResponseException;
use Log;

/**
 * Wallet Third Party Connector 
 *
 * @author heba.mamdouh
 */
class ChatConnector
{
    public function __construct()
    {
    }

    /**
     *
     * Send Client Http Request to chat
     *
     * 
     * @param array $method, $url, $body
     *
     * @return Response
     */
    private static function sendClientRequest($method, $url , $body){

        $client = new Client(['base_uri' => config('chat.apiBaseURL')]);

        try
        {
            $response= $client->request($method, $url , $body);
            ChatConnector::logData($response, $method, $url , $body);
            return ['is_success' => true, 'response' => json_decode($response->getBody()->getContents(),true), 'resopnse_code' => $response->getStatusCode()];
        }
        catch (GuzzleException $e)
        {
            $response = ChatConnector::StatusCodeHandling($e,$method, $url , $body);
            return $response;
        }
    }

     /**
     *
     * Send Http Request to chat
     *
     * 
     * @param array $method, $url, $data,$header
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
        $body['http_errors'] = true;
        
        return ChatConnector::sendClientRequest($method, $url , $body);
    }

    /**
     *
     * Send Upload Http Request to chat
     *
     * 
     * @param array $method, $url, $data,$header
     *
     * @return Response
     */
    public static function sendUploadRequest($method, $url, $data,$header=null)
    {
        $body=[];
        if($header){
            $body['headers'] = $header;
        }
        if($data){
            $body['multipart'] = $data;
        }
        $body['http_errors'] = true;
       
        return ChatConnector::sendClientRequest($method, $url , $body);
    }

    /**
     *
     * Generate header for request
     *
     * @return header
     */
    private static function prepareHeadersData($isCurrentToken,$tokenData=null){
        $headers = [];
        $token = null;

        $headers['Content-Type'] = 'application/json';

        if ($isCurrentToken) {
            $payload = JWTAuth::parseToken()->getPayload();
            $token = $payload->get('chat_token');
        } else {
            $token = $tokenData;
        }

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $headers;
    }

    /**
     *
     * Register new user in chat
     *
     * 
     * @param array $userData
     *
     * @return Response
     */
    public static function register($userData)
    {
       return ChatConnector::sendRequest('POST','authenticate/register',$userData);
    }


    /**
     *
     * Login 
     *
     * 
     * @param string $username
     * @return Response
     */
    public static function login($loginData)
    {
        $response = ChatConnector::sendRequest('POST','authenticate',$loginData);
        return $response;
    }

    /**
     *
     * Create Chat Room 
     *
     * 
     * @param array $chatRoomData
     * @return Response
     */
    public static function createChatRoom($chatRoomData,$tokenData)
    {
        $headers = ChatConnector::prepareHeadersData(false,$tokenData);
        $response = ChatConnector::sendRequest('POST','chat-rooms',$chatRoomData,$headers);
       
        return $response;
        
    }

    /**
     *
     * Update Chat Room  users
     *
     * 
     * @param array $data
     * @return Response
     */
    public static function updateChatRoomUsers($chatRoomId,$data,$tokenData)
    {
        $headers = ChatConnector::prepareHeadersData(false,$tokenData);
        $response = ChatConnector::sendRequest('PUT','chat-rooms/' .$chatRoomId,$data,$headers);
       
        return $response;
        
    }

     /**
     *
     * Send Message
     *
     * 
     * @param array $chatMessageData
     * @return Response
     */
    public static function sendMessage($chatMessageData,$tokenData)
    {
        $headers = ChatConnector::prepareHeadersData(false,$tokenData);
        $response =ChatConnector::sendRequest('POST','chat-messages',$chatMessageData,$headers);
        return $response;
        
    }

     /**
     *
     * Get Messages History
     *
     * 
     * @param array $chatMessageData
     * @return Response
     */
    public static function getMessagesHistory($messagesHistoryData)
    {
        $headers = ChatConnector::prepareHeadersData(true, null);
        $response = ChatConnector::sendRequest('POST','chat-messages/filtered-list',$messagesHistoryData,$headers);
        return $response;
        
    }


    /**
     *
     * Get Chat Rooms For admin
     *
     * 
     * @param array $chatMessageData
     * @return Response
     */
    public static function getChatRooms($chatRoomsData)
    {
        $headers = ChatConnector::prepareHeadersData(true, null);
        $response = ChatConnector::sendRequest('POST','chat-rooms/filtered-list',$chatRoomsData,$headers);
        return $response;
        
    }


    /**
     *
     * Close or Reopen Chat Rooms For admin
     *
     * 
     * @param array $chatMessageData
     * @return Response
     */
    public static function closeChatRoom($chatRoomId)
    {
        $headers = ChatConnector::prepareHeadersData(true, null);
        $response = ChatConnector::sendRequest('POST','chat-rooms/'.$chatRoomId.'/close-room',null,$headers);
        return $response;
        
    }

    /**
     *
     * join users to Chat Rooms 
     *
     * @param int $chatRoomId
     * @param array $usersData
     * @return Response
     */
    public static function joinUsersToChatRoom($chatRoomId,$data)
    {
        $headers = ChatConnector::prepareHeadersData(true,null);
        $response = ChatConnector::sendRequest('POST','chat-rooms/'.$chatRoomId.'/add-users',$data,$headers);
        return $response;
        
    }

    /**
     *
     * check users exist in Chat Room
     *
     * @param int $chatRoomId
     * @param array $usersData
     * @return Response
     */
    public static function checkUserExistInChatRoom($chatRoomId,$data)
    {
        $headers = ChatConnector::prepareHeadersData(true,null);
        $response = ChatConnector::sendRequest('POST','chat-rooms/'.$chatRoomId.'/check-user-exist',$data,$headers);

        
        return $response;
        
    }

    /**
     *
     * Show Chat Room Details
     *
     * @param int $chatRoomId
     * @return Response
     */
    public static function showChatRoomDetails($chatRoomId)
    {
        $headers = ChatConnector::prepareHeadersData(true, null);
        $response = ChatConnector::sendRequest('GET','chat-rooms/'.$chatRoomId,null,$headers);
        return $response;
        
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
        ChatConnector::logData($response, $method, $url , $body);
        return ['is_success' => false, 'response' =>['error' => $response,'status_code'=> $statusCode]];
       
    }

    /**
     *
     * Hendle Error Code 
     *
     * @param Response $response
     */
    public static function logData($response,$method, $url , $body){
        Log::channel('chat')->info(['response'=> $response,'method' => $method, 'url' =>$url ,'body' => $body]);
    }

    /**
     *
     * Upload Attachment
     *
     * 
     * @param array $attachmentFile
     * @return Response
     */
    public static function uploadAttachment($attachmentData)
    {
        $headers = [];
        $response =ChatConnector::sendUploadRequest('POST','upload-attachment',$attachmentData,$headers);
        return $response;  
    }

    /**
     *
     * Send Attachment
     *
     * 
     * @param array $chatAttachmentData
     * @return Response
     */
    public static function sendAttachment($chatAttachmentData,$tokenData)
    {
        $headers = ChatConnector::prepareHeadersData(false,$tokenData);
        $response =ChatConnector::sendRequest('POST','chat-messages/attachment',$chatAttachmentData,$headers);
        return $response;  
    }

    /**
     *
     * get chat Attachments list
     *
     * 
     * @param array $chatAttachmentData
     * @return Response
     */
    public static function getChatRoomAttachments($chatRoomId,$chatRoomAttachmentsData)
    {
        $headers = ChatConnector::prepareHeadersData(true, null);
        $response = ChatConnector::sendRequest('POST','chat-rooms/'.$chatRoomId.'/attachments/filtered-list',$chatRoomAttachmentsData,$headers);
        return $response;
    }

    /**
     *
     * delete chat room user
     *
     * 
     * @param array $chatRoomId,$userId
     * @return Response
     */
    public static function deleteUserAtChatRoom($chatRoomId,$userId)
    {
        $headers = ChatConnector::prepareHeadersData(true, null);
        $response = ChatConnector::sendRequest('DELETE','chat-rooms/'.$chatRoomId.'/chat-room-users/'.$userId,null,$headers);
        return $response;
    }

    /**
     *
     * Update chat user
     *
     * 
     * @param array $userData
     *
     * @return Response
     */
    public static function updateChatUser($chatUserId,$chatUserData,$tokenData)
    {
        $headers = ChatConnector::prepareHeadersData(false,$tokenData);
        return ChatConnector::sendRequest('PUT','users/'.$chatUserId,$chatUserData,$headers);
    }
}