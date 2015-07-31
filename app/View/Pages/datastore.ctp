<?php

const SERVICE_ACCOUNT_NAME = '';
const DATASET_ID = 'fit-visitor-597';


//require_once VENDORS.'google-api-php-client/src/Google_Client.php';
//require_once VENDORS.'google-api-php-client/src/contrib/Google_DatastoreService.php';
require_once VENDORS.'Google/autoload.php';

$client = new Google_Client();
$client->setApplicationName("test");
$client->setClientID("");

$key = file_get_contents(VENDORS.'');
$client->setAssertionCredentials(
    new Google_Auth_AssertionCredentials(
        SERVICE_ACCOUNT_NAME,
        array('https://www.googleapis.com/auth/userinfo.email',
              'https://www.googleapis.com/auth/datastore'),
        $key)
);

$datastore = new Google_Service_Datastore($client);

$lookup = new Google_Service_Datastore_LookupRequest();

$path1 = new Google_Service_Datastore_KeyPathElement();
$path1->setKind('Guestbook');
$path1->setName('default_guestbook');

$path2 = new Google_Service_Datastore_KeyPathElement();
$path2->setKind('Greeting');
# this is just an example check a real entity id in your datastore
# if you do not have ancestor entity you only need one (path1) element
$path2->setId('5639445604728832');

$key = new Google_Service_Datastore_Key();
$key->setPath([$path1,$path2]);

$keyArray = array();
$keyArray[] = $key;
$lookup->setKeys($keyArray);

echo '<pre>';
if(array_key_exists('catchError', $_GET)){
//if(true){
    try{
        $result = $datastore->datasets->lookup(DATASET_ID, $lookup);
        var_dump($result);
    }
    catch(Google_ServiceException $e){
        var_dump($e);
    }
}
else{
    $result = $datastore->datasets->lookup(DATASET_ID, $lookup);
    var_dump($result);
}
echo '</pre>';

