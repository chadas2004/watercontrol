<?php
require_once 'vendor/autoload.php';

$config = ClickSend\Configuration::getDefaultConfiguration()
    ->setUsername('bryce1@basketth.com')
    ->setPassword('E678AF35-2868-AD22-645F-CC65956F17F6');

$apiInstance = new ClickSend\Api\SMSApi(new GuzzleHttp\Client(), $config);

$msg = new \ClickSend\Model\SmsMessage();
$msg->setBody("Test SMS depuis PHP");
$msg->setTo("+2290195792329"); // Remplace par ton numéro
$msg->setSource("php");

$sms_messages = new \ClickSend\Model\SmsMessageCollection();
$sms_messages->setMessages([$msg]);

try {
    $result = $apiInstance->smsSendPost($sms_messages);
    print_r($result);
} catch (Exception $e) {
    echo 'Erreur SMS : ', $e->getMessage();
}
