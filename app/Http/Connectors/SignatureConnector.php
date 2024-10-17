<?php

namespace Connectors;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Log;

/**
 * Signature Third Party Connector
 *
 * @author eman.mohamed
 */
class SignatureConnector
{

    public function __construct()
    {

    }
    /**
     *
     * Send Http Request to signature
     *
     *
     * @param array $userData
     *
     * @return Response
     */
    public static function sendRequest($method, $url, $data, $header = null)
    {
        $body = [];
        if ($header) {
            $body['headers'] = $header;
        }
        if ($data) {
            $body['json'] = $data;
        }
        $body['http_errors'] = false;

        $client = new Client(['base_uri' => Config::get('signature.SIGNATURE_URL')]);
        $response = $client->request($method, $url, $body);
	//dd($response);
        SignatureConnector::logData($response, $method, $url , $body);
        return $response;

    }

    public static function updateUserByEmail($token, $oldEmail, $newEmail, $phone)
    {
        $headers = SignatureConnector::prepareHeadersData($token, false);
        $data = [
            'OldEmail' => $oldEmail,
            'Email' => $newEmail,
            'Phone' => $phone,
        ];
        $response = SignatureConnector::sendRequest('POST', 'users/UpdateByEmail', $data, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return null;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }
    }

    public static function sendUploadRequest($method, $url, $data, $header = null)
    {
        $body = [];
        if ($header) {
            $body['headers'] = $header;
        }
        if ($data) {
            $body['multipart'] = $data;
        }
        $body['http_errors'] = false;

        $client = new Client(['base_uri' => Config::get('signature.SIGNATURE_URL')]);
        $response = $client->request($method, $url, $body);
        SignatureConnector::logData($response, $method, $url , $body);
        return $response;

    }

    /**
     *
     * Login
     *
     * @return Response
     */
    public static function appLogin($signatureUsername, $signaturePassword)
    {
        $loginData['ApplicationEmail'] = $signatureUsername;
        $loginData['ApplicationPassword'] = $signaturePassword;

        $response = SignatureConnector::sendRequest('POST', 'token', $loginData);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            $token = $jsonResponse['token'];
            return $token;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => ['error' => $jsonResponse], 'status_code' => $statusCode];
        }

    }

    /**
     *
     * Login
     *
     * @return Response
     */
    public static function userLogin($appToken, $userEmail, $documentId)
    {
        $loginData['UserName'] = $userEmail;
        $loginData['DocumentID'] = $documentId;

        $headers = SignatureConnector::prepareHeadersData($appToken);
        $response = SignatureConnector::sendRequest('POST', 'users/token', $loginData, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();

        if ($statusCode == 200) {
            $userToken = $jsonResponse['token'];
            return $userToken;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }

    public static function upload($file, $token)
    {
        $headers = SignatureConnector::prepareHeadersData($token, false);
        $data = [
            [
                'name' => 'file',
                'contents' => $file,
                'filename' => time() . '_signature.pdf',
            ],
        ];
        $response = SignatureConnector::sendUploadRequest('POST', 'Upload', $data, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            $path = $jsonResponse['Results'];
            return $path;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }

    /**
     *
     * Create Document
     *
     *
     * @param string $token
     * @param string $documentUrl
     * @param Array[string] $emails
     * @return Response
     */
    public static function createDocument($token, $documentUrl, $participantEmails, $languageId)
    {
        $data['OriginalDocumentUrl'] = $documentUrl;
        $data['DocumentFields'] = [];

        foreach (collect($participantEmails)->chunk(8) as $key1 => $emailGroup) {
            $xPositionStart = $languageId == config('languages.ar') ? 53 : 9;
            $yPositionStartLeft = 12;
            $yPositionStartRight = 12;
            $nickNameSpacing = 1;
            foreach (array_values($emailGroup->toArray()) as $key => $val) {
                if ($key == 0) {
                    if ($val["hasNick"] == false) {
                        $yPositionStartRight = $yPositionStartRight - $nickNameSpacing;
                    }
                    $yPositionStart = $yPositionStartRight;
                }
                if ($key == 1) {
                    if ($val["hasNick"] == false) {
                        $yPositionStartLeft = $yPositionStartLeft - $nickNameSpacing;
                    }
                    $xPositionStart = $languageId == config('languages.ar') ? 9 : 53;
                    $yPositionStart = $yPositionStartLeft;
                }
                if ($key > 1) {
                    if ($key % 2 == 0) {
                        if ($val["hasNick"] == false) {
                            $yPositionStartRight = $yPositionStartRight - $nickNameSpacing;
                        }
                        $yPositionStartRight = $yPositionStartRight + 16;
                        $xPositionStart = $languageId == config('languages.ar') ? 53 : 9;
                        $yPositionStart = $yPositionStartRight;
                    } else {
                        if ($val["hasNick"] == false) {
                            $yPositionStartLeft = $yPositionStartLeft - $nickNameSpacing;
                        }
                        $yPositionStartLeft = $yPositionStartLeft + 16;
                        $xPositionStart = $languageId == config('languages.ar') ? 9 : 53;
                        $yPositionStart = $yPositionStartLeft;
                    }
                }

                $data['DocumentFields'][] = [
                    "FieldTypeID" => config('signature.filedTypes.signature'),
                    "XPosition" => $xPositionStart,
                    "YPosition" => $yPositionStart,
                    "DocumentFieldUserEmail" => $val["email"],
                    "DocumentFieldUserPhoneNumber" => $val["phone"],
                    "PageNumber" => 4,
                ];

            }
        }
        $headers = SignatureConnector::prepareHeadersData($token);
        $response = SignatureConnector::sendRequest('POST', 'documents', $data, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        //dd($response->getBody()->getStatusCode());
        //die('MBM');
        $statusCode = $response->getStatusCode();
	//dd($statusCode);
        if ($statusCode == 200) {
            return $jsonResponse["Results"]["DocumentID"];
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }

    public static function createApprovalDocument($token, $documentUrl, $approvalMembers, $languageId)
    {
        $data['OriginalDocumentUrl'] = $documentUrl;
        $data['DocumentFields'] = [];
        $data["IsApproval"] = true;

        foreach ($approvalMembers as $approvalMember) {
            $data['DocumentFields'][] = [
                "FieldTypeID" => config('signature.filedTypes.signature'),
                "XPosition" => intval($approvalMember->signature_x_upper_left),
                "YPosition" => intval($approvalMember->signature_y_upper_left),
                "DocumentFieldUserEmail" => $approvalMember->member->email,
                "DocumentFieldUserPhoneNumber" => $approvalMember->member->phone,
                "PageNumber" => intval($approvalMember->signature_page_number),
            ];
        }
        $headers = SignatureConnector::prepareHeadersData($token);
        $response = SignatureConnector::sendRequest('POST', 'documents', $data, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $jsonResponse["Results"]["DocumentID"];
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }

    public static function updateApprovalDocument($token, $documentUrl, $documentId, $approvalMembers, $languageId)
    {
        $data['OriginalDocumentUrl'] = $documentUrl;
        $data['DocumentID'] = $documentId;
        $data['DocumentFields'] = [];
        $data["IsApproval"] = true;

        foreach ($approvalMembers as $approvalMember) {
            $data['DocumentFields'][] = [
                "FieldTypeID" => config('signature.filedTypes.signature'),
                "XPosition" => intval($approvalMember->signature_x_upper_left),
                "YPosition" => intval($approvalMember->signature_y_upper_left),
                "DocumentFieldUserEmail" => $approvalMember->member->email,
                "DocumentFieldUserPhoneNumber" => $approvalMember->member->phone,
                "PageNumber" => intval($approvalMember->signature_page_number),
            ];
        }
        $headers = SignatureConnector::prepareHeadersData($token);
        $response = SignatureConnector::sendRequest('PUT', 'documents/updateDocument', $data, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $jsonResponse["Results"]["DocumentID"];
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }

    /**
     *
     * getDocumentBinary
     *
     * @return Response file
     */
    public static function getDocumentBinary($userToken, $documentId, $diffHours = 0)
    {
        $headers = SignatureConnector::prepareHeadersData($userToken, false, true);
        $response = SignatureConnector::sendRequest('GET', 'documents/documentBinaries/' . $documentId . '?timeZone=' . $diffHours, null, $headers);

        $jsonResponse = $response->getBody()->getContents();
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $jsonResponse;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }

    public static function getUserSignatures($userToken)
    {
        $headers = SignatureConnector::prepareHeadersData($userToken);
        $response = SignatureConnector::sendRequest('GET', 'UserSignatures', null, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $jsonResponse;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }

    public static function sendDocumentSignatureCode($userToken, $data, $lang)
    {
        $headers = SignatureConnector::prepareHeadersData($userToken);
        $response = SignatureConnector::sendRequest('POST', 'DocumentSignatureCodes/' . $lang, $data, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $jsonResponse;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }

    public static function verifyCode($userToken, $data)
    {
        $headers = SignatureConnector::prepareHeadersData($userToken);
        $response = SignatureConnector::sendRequest('POST', 'DocumentSignatureCodes/VerifyCode', $data, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $jsonResponse;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }

    public static function getDocument($userToken, $lang)
    {
        $headers = SignatureConnector::prepareHeadersData($userToken);
        $response = SignatureConnector::sendRequest('GET', 'documents/documentPages/' . $lang, null, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $jsonResponse;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }

    public static function saveSignature($userToken, $data)
    {
        $headers = SignatureConnector::prepareHeadersData($userToken);
        $response = SignatureConnector::sendRequest('POST', 'UserSignatures', $data, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $jsonResponse;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }


    public static function sign($userToken, $data ,$documentFieldId)
    {
        $headers = SignatureConnector::prepareHeadersData($userToken);
        $response = SignatureConnector::sendRequest('PUT', 'documentfields/sign/'.$documentFieldId, $data, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $jsonResponse;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }


    public static function reject($userToken, $data ,$documentFieldId)
    {
        $headers = SignatureConnector::prepareHeadersData($userToken);
        $response = SignatureConnector::sendRequest('PUT', 'documentfields/reject/'.$documentFieldId, $data, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $jsonResponse;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }

    public static function prepareHeadersData($token, $addContentType = true)
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $headers = [];
        $headers['Authorization'] = 'Bearer ' . $token;
        if ($addContentType) {
            $headers['Content-Type'] = 'application/json';
        }

        return $headers;
    }
        /**
     *
     * Hendle Error Code 
     *
     * @param Response $response
     */
    public static function logData($response,$method, $url , $body){
        Log::channel('signature')->info(['response'=> $response,'method' => $method, 'url' =>$url ,'body' => $body]);
    }

    public static function getDocumentById($documentId, $userToken)
    {
        $headers = SignatureConnector::prepareHeadersData($userToken);
        $response = SignatureConnector::sendRequest('GET', 'documents/' . $documentId, null, $headers);
        $jsonResponse = json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return $jsonResponse;
        } elseif ($statusCode == 500) {
            return ['error' => ['error' => 'something went wrong'], 'status_code' => 400];

        } else {
            return ['error' => $jsonResponse, 'status_code' => $statusCode];
        }

    }
}
