<?php
namespace Connectors;

use Illuminate\Support\Facades\Config;
use GuzzleHttp\Exception\GuzzleException;
use LdapRecord\Connection;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\BadResponseException;
use Log;

/**
 * Wallet Third Party Connector 
 *
 * @author 
 */
class LdapConnector
{
    public function __construct()
    {
    }

    public static function createLdapConnection($userName, $password,$isAdmin = false ){


        $ldapConfig = (array) config('ldap.connections.default');
        Config::set('ldap.default', array_merge($ldapConfig));

        $connection = new Connection(config('ldap.default'));

        try
        {
            Log::channel('ldap')->info(['admin-login-command'=> "ldapsearch -h ".$ldapConfig['hosts'][0]." -b '".$ldapConfig['base_dn']."' -D ".$ldapConfig['username']." -w ".$ldapConfig['password']." -x"]);


            $connection->connect();
            if(!$isAdmin){
            Log::channel('ldap')->info(['get-user-info-command'=> "ldapsearch -xLLL -h ".$ldapConfig['hosts'][0]." -b '".$ldapConfig['base_dn']."' -D ".$ldapConfig['username']." -w ".$ldapConfig['password']." -x mail=".$userName."*"]);
            $user = $connection->query()
            ->whereStartsWith('mail', $userName)
            ->firstOrFail();

            Log::channel('ldap')->info(['distinguishedname'=> $user]);

            if ($connection->auth()->attempt($user['distinguishedname'][0], $password)) {
                Log::channel('ldap')->info(['connection'=> $connection]);
               return $connection;
            }
            else{
                return null;
            }
            }
            else{
                return $connection;
            }
 
        }
        catch (\Exception $e)
        {

            report($e);
        }
    }
 
}
