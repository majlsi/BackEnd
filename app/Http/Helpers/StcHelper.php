<?php

namespace Helpers;

use Carbon\Carbon;
use Connectors\StcConnector;

class StcHelper
{

    public function sendEventCallBack($event){
        return StcConnector::sendEventCallBack($event);
    }


    public function mapStcEvent($data)
    {
        $event_model = [];
        $event_model['event_id'] = $data['id'];
        $event_model['event_type'] = $data['type'];
        $event_model['creation_date']= Carbon::parse($data['created_at']);
        $event_model['api_version'] = $data['api_version'];
        $event_model['tenant'] = $data['tenant'];
        if(isset($data['data'])){
            $event_model['data'] = json_encode($data['data']);
        }
        return $event_model;
    }

    public function mapSubscriptionOrganization($subscription){
        $organization = [];
        $organization['organization_name_en'] = isset($subscription['extra_fields']['Company/ Organization Name*'])? $subscription['extra_fields']['Company/ Organization Name*'] : (isset($subscription['customer']['name'])? $subscription['customer']['name'] : 'organization name');
        $organization['organization_name_ar'] = isset($subscription['extra_fields']['Company/ Organization Name*'])? $subscription['extra_fields']['Company/ Organization Name*'] :  (isset($subscription['customer']['name'])? $subscription['customer']['name'] : 'organization name');
        $organization['organization_phone'] = isset($subscription['extra_fields']['Phone Number'])? $subscription['extra_fields']['Phone Number']: "012000000";
        $organization['password'] = isset($subscription['extra_fields']['Password'])? $subscription['extra_fields']['Password'] : "123456";
        $organization['email'] = isset($subscription['extra_fields']['Email'])? $subscription['extra_fields']['Email'] : "test.gmail.com";
        $organization['name'] = isset($subscription['extra_fields']['Full Name'])? $subscription['extra_fields']['Full Name'] : "full name";
        $organization['name_ar'] = isset($subscription['extra_fields']['Full Name'])? $subscription['extra_fields']['Full Name'] : "full name";
        $organization['organization_number_of_users'] = isset($subscription['plan']['metadata']['userscount'])? $subscription['plan']['metadata']['userscount'] : 0;
        $organization['stc_customer_ref'] = $subscription['customer']['id'];

        return $organization;
    }

    // public function mapUser($subscription){
    //     $user_obj = $subscription['extra_fields']['user_obj'];
    //     $user = [];
    //     $subscription[''];

    //     return $user;
    // }

}