<?php

namespace Services;

use Illuminate\Support\Facades\Config;
use Repositories\LanguageRepository;
use Connectors\LdapConnector;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Models\User;
use Log;

class LdapService extends BaseService
{

    public function __construct(DatabaseManager $database, LanguageRepository $repository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
    }

    public function prepareCreate(array $data)
    {
        return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function authenticateWithLdap(Request $request, string $username, string $password)
    {      

        $connection = LdapConnector::createLdapConnection($username, $password);

        if($connection != null){
            $ldapConfig = (array) config('ldap.default');
            Log::channel('ldap')->info(['get-user-command'=> "ldapsearch -xLLL -h ".$ldapConfig['hosts'][0]." -b '".$ldapConfig['base_dn']."' -D ".$ldapConfig['username']." -w ".$ldapConfig['password']." -x mail=".$username]);
            $ldapUser = $connection->query()
                ->where('mail', '=', $username)->get();
            Log::channel('ldap')->info(['authenticateWithLdap' => $ldapUser]);

            if (!empty($ldapUser)) {
                $user = $this->registerUserFromLdap($request, $ldapUser);
            }
            return $ldapUser;
        }
        else{
            return null;
        }
    }

    public function registerUserFromLdap(Request $request, array $user)
    {
        $data = $request->all();
        $data['organization_id'] = config('LdapVariable.ldapOrganizationId');
        $data['username'] = $data['email'];
        $data['role_id'] = config('roles.boardMembers');
        $data['oauth_provider'] = config('providers.custom');
        $data['is_verified'] = 1;
        $data['language_id'] = config('languages.ar');
        $data['name'] = $user[0]['name'][0];
        $data['name_ar'] = $user[0]['name'][0];
        return $data;
    }

    public function getLdapUsers(string $userName)
    {

        if ($userName) {
            $connection = LdapConnector::createLdapConnection(config('LdapVariable.ldapUserName'), config('LdapVariable.ldapPassword'),true);
            $ldapConfig = (array) config('ldap.default');
            Log::channel('ldap')->info(['search-command'=> "ldapsearch -xLLL -h ".$ldapConfig['hosts'][0]." -b '".$ldapConfig['base_dn']."' -D ".$ldapConfig['username']." -w ".$ldapConfig['password']." -x name=".$userName."* -z 5"]);
            $ldapUsers = $connection->query()
                ->whereStartsWith('name', $userName)
                ->limit(5)
                ->get();
            Log::channel('ldap')->info(['ldapUsersSearchResult' => $ldapUsers]);
            if (!empty($ldapUsers)) {
                $users = collect($ldapUsers)->map(function ($ldapUser) {
                    $user = new User();
                    $user['organization_id'] = config('LdapVariable.ldapOrganizationId');
                    $user['username'] = isset($ldapUser['mail'][0]) ? $ldapUser['mail'][0]:($ldapUser['userprincipalname'][0] ?? null);
                    $user['email'] = isset($ldapUser['mail'][0]) ? $ldapUser['mail'][0]:($ldapUser['userprincipalname'][0] ?? null);
                    $user['password'] = config('LdapVariable.defaultPassword');
                    $user['role_id'] = config('roles.boardMembers');
                    $user['oauth_provider'] = config('providers.custom');
                    $user['is_verified'] = 1;
                    $user['language_id'] = config('languages.ar');
                    $user['name'] = $ldapUser['name'][0];
                    $user['name_ar'] = $ldapUser['name'][0];
                    return $user;
                });
                return $users;
            }
        }

        return [];
    }
    public function getLdapUser(string $userName = null)
    {

        if ($userName) {
            $connection = LdapConnector::createLdapConnection(config('LdapVariable.ldapUserName'), config('LdapVariable.ldapPassword'),true);
            $ldapUser = $connection->query()->whereStartsWith('mail', $userName)->get();
            Log::channel('ldap')->info(['getUser' => $ldapUser]);
            if (!empty($ldapUser)) {
                return $ldapUser;
            }
        }

        return null;
    }
}