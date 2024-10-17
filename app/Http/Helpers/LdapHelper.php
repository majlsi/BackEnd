<?php

namespace Helpers;


class LdapHelper
{


    public function __construct( )
    {
    }

    public function prepareLdapUserData($ldapUser){
        $data = [];
        
        $data['organization_id'] = config('LdapVariable.ldapOrganizationId');
        $data['username'] = $ldapUser[0]['userprincipalname'][0];
        $data['email'] = $ldapUser[0]['userprincipalname'][0];
        $data['password'] =config('LdapVariable.defaultPassword');
        $data['role_id']= config('roles.boardMembers');
        $data['oauth_provider'] = config('providers.custom');
        $data['is_verified'] = 1;
        $data['language_id'] = config('languages.ar');
        $data['name'] = $ldapUser[0]['name'][0];
        $data['name_ar'] = $ldapUser[0]['name'][0];
        return $data;
    }
}