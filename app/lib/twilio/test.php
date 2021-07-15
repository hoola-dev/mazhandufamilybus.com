<?php
ini_set("display_errors", "On");
error_reporting(E_ALL|E_STRICT);
// Require the bundled autoload file - the path may need to change
// based on where you downloaded and unzipped the SDK
require __DIR__ . '/src/Twilio/autoload.php';

// Use the REST API Client to make requests to the Twilio REST API
use Twilio\Rest\Client;

// Your Account SID and Auth Token from twilio.com/console
$sid = 'AC6e2d4f3f53ec38317904826ccd65072d';
$token = '1f1e30e4f93fc5803345216377a2d5ea';
$client = new Client($sid, $token);

// Use the client to do fun stuff like send text messages!
$response = $client->messages->create(
    // the number you'd like to send the message to
    '+919447580215',
    array(
        // A Twilio phone number you purchased at twilio.com/console
        'from' => '+12026014900',
        // the body of the text message you'd like to send
        'body' => "Hey Babu! Good luck on the bar exam!"
    )
);

echo "<pre>";
print($response);


