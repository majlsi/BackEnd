<?php

namespace Helpers;

use Connectors\SignatureConnector;

class SignatureHelper
{

    private $signatureConnector;

    public function __construct(SignatureConnector $signatureConnector)
    {
        $this->signatureConnector = $signatureConnector;
    }

    public function createDocument($organization, $pdfFile, $usersEmails, $languageId)
    {
        $appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);
	//dd($appToken);
        if (!isset($appToken['error'])) {
            $uplodedDocUrl = $this->signatureConnector->upload($pdfFile, $appToken);
		//print_r($uplodedDocUrl);
		//die();
            if (!isset($uplodedDocUrl['error'])) {
                $docId = $this->signatureConnector->createDocument($appToken, ltrim($uplodedDocUrl, '/'), $usersEmails, $languageId);
                //var_dump($appToken, ltrim($uplodedDocUrl, '/'), $usersEmails, $languageId);
                //dd($docId);
		//die();
		if (!isset($docId["error"])) {
                    return $docId;
                }
            }
        }
        return null;
    }

    public function createApprovalDocument($organization, $pdfFile, $usersEmails, $languageId)
    {
        $appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);
        if (!isset($appToken['error'])) {
            $uplodedDocUrl = $this->signatureConnector->upload($pdfFile, $appToken);
            if (!isset($uplodedDocUrl['error'])) {
                $docId = $this->signatureConnector->createApprovalDocument($appToken, ltrim($uplodedDocUrl, '/'), $usersEmails, $languageId);
                if (!isset($docId["error"])) {
                    return $docId;
                }
            }
        }
        return null;
    }

    public function updateApprovalDocument($organization, $pdfFile, $documentId, $usersEmails, $languageId)
    {
        $appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);
        if (!isset($appToken['error'])) {
            $result = $this->signatureConnector->getDocumentById($documentId, $appToken);
            if (!isset($uplodedDocUrl['error'])) {
                $docId = $this->signatureConnector->updateApprovalDocument($appToken, ltrim($result['Results']['OriginalDocumentUrl'], '/'), $documentId, $usersEmails, $languageId);
                if (!isset($docId["error"])) {
                    return $docId;
                }
            }
        }
        return null;
    }

    public function updateUserByEmail($organization, $oldEmail, $newEmail, $phone)
    {
      if(isset($organization)){
		$appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);
        	if (!isset($appToken['error'])) {
           	 $reponse = $this->signatureConnector->updateUserByEmail($appToken, $oldEmail, $newEmail, $phone);
        	}
	}
    }

    public function loginUser($organization, $userEmail, $documentId)
    {
        $appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);

        $userToken = $this->signatureConnector->userLogin($appToken, $userEmail, $documentId);

        if (!isset($userToken["error"])) {
            return $userToken;
        }
        return null;

    }

    public function getDocumentBinary($organization, $documentId, $userEmail)
    {
        $appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);

        // $userToken = $this->signatureConnector->userLogin($appToken, $userEmail, $documentId);
        if (!isset($appToken["error"])) {
            $file = $this->signatureConnector->getDocumentBinary($appToken, $documentId, $organization->timeZone->diff_hours);

            if (!isset($file["error"])) {
                return $file;
            }
            return null;
        }
        return null;

    }

    public function getUserSignatures($organization, $userEmail)
    {
        $appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);

        $userToken = $this->signatureConnector->userLogin($appToken, $userEmail, null);
        if (!isset($userToken["error"])) {
            $signatures = $this->signatureConnector->getUserSignatures($userToken);

            if (!isset($signatures["error"])) {
                return $signatures;
            }
            return null;
        }
        return null;
    }

    public function sendDocumentSignatureCode($organization, $userEmail, $documentId, $data, $lang)
    {
        $appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);
        $userToken = $this->signatureConnector->userLogin($appToken, $userEmail, $documentId);
        if (!isset($userToken["error"])) {
            $result = $this->signatureConnector->sendDocumentSignatureCode($userToken, $data, $lang);

            if (!isset($result["error"])) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function verifyCode($organization, $userEmail, $documentId, $data)
    {
        $appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);
        $userToken = $this->signatureConnector->userLogin($appToken, $userEmail, $documentId);
        if (!isset($userToken["error"])) {
            $result = $this->signatureConnector->verifyCode($userToken, $data);

            if (!isset($result["error"])) {
                return $result;
            }
            return null;
        }
        return null;
    }

    public function getDocument($organization, $userEmail, $documentId, $lang)
    {
        $appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);

        $userToken = $this->signatureConnector->userLogin($appToken, $userEmail, $documentId);
        if (!isset($userToken["error"])) {
            $document = $this->signatureConnector->getDocument($userToken ,$lang);

            if (!isset($document["error"])) {
                return $document;
            }
            return null;
        }
        return null;
    }

    public function saveSignature($organization, $userEmail, $data)
    {
        $appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);
        $userToken = $this->signatureConnector->userLogin($appToken, $userEmail, null);
        if (!isset($userToken["error"])) {
            $result = $this->signatureConnector->saveSignature($userToken, $data);

            if (!isset($result["error"])) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function sign($organization, $userEmail, $data ,$documentFieldId ,$documentId)
    {
        $appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);
        $userToken = $this->signatureConnector->userLogin($appToken, $userEmail, $documentId);
        if (!isset($userToken["error"])) {
            $result = $this->signatureConnector->sign($userToken, $data ,$documentFieldId);

            if (!isset($result["error"])) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function reject($organization, $userEmail, $data ,$documentFieldId ,$documentId)
    {
        $appToken = $this->signatureConnector->appLogin($organization->signature_username, $organization->signature_password);
        $userToken = $this->signatureConnector->userLogin($appToken, $userEmail, $documentId);
        if (!isset($userToken["error"])) {
            $result = $this->signatureConnector->reject($userToken, $data ,$documentFieldId);

            if (!isset($result["error"])) {
                return true;
            }
            return false;
        }
        return false;
    }
    

}
