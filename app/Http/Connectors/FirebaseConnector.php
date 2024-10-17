<?php

namespace Connectors;

use Illuminate\Support\Facades\Config;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

/**
 * Low-level client for Firebase
 *
 * @author eman.mohamed
 */
class FirebaseConnector
{

    public function __construct()
    {

    }

    /**
     *
     * Send Notification To One Device
     *
     * @param array $token
     * @param Options $option
     * @param PayloadData $data
     * @param PayloadNotification $notification
     *
     * @return Response
     */
    public function sendNotification($token, $title, $body)
    {
        $option             = $this->buildOptions();
        $data               = $this->buildDataPayload($title, $body);
        $notification       = $this->buildNotificationPayload($title, $body);
        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();
        $downstreamResponse->tokensToDelete();
        $downstreamResponse->tokensToModify();
        $downstreamResponse->tokensToRetry();

        return $downstreamResponse;
    }

    public function buildNotificationPayload($title, $body)
    {
        $icon                = Config::get('notification.notificationIcon');
        $clickAction         = Config::get('notification.notificationClick');
        $tag                 = Config::get('notification.tag');
        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($body)
            ->setSound('default')
            ->setIcon($icon)
            ->setTag($tag)
            ->setClickAction($clickAction);

        $notification = $notificationBuilder->build();

        return $notification;
    }

    public function buildOptions()
    {
        $optionBuiler = new OptionsBuilder();
        $optionBuiler->setTimeToLive(60 * 20);
        $option       = $optionBuiler->build();
        return $option;
    }

    public function buildDataPayload($title, $body)
    {
        $icon        = Config::get('notification.notificationIcon');
        $clickAction = Config::get('notification.notificationClick');
        $tag         = Config::get('notification.tag');

        $dataPayload = [
            'title' => $title,
            'body' => $body,
            'icon' => $icon,
            'click_action' => $clickAction,
            'tag' => $tag
        ];
        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($dataPayload);
        $data        = $dataBuilder->build();
        return $data;
    }
}