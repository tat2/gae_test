<?php
// 日本語

/*
Consumer key: *

Consumer secret: 

Request URI: 
https://api.twitter.com/1.1/
*/

session_start(); 

require_once VENDORS."Abraham/TwitterOAuth/autoload.php";
//use Abraham\TwitterOAuth\TwitterOAuth;

const TWITTER_CONSUMER_KEY ='';
const TWITTER_CONSUMER_SECRET ='';


  echo 'user_id:'     . $_SESSION['access_token']['user_id'] . '<br />';
  echo 'screen_name:' . $_SESSION['access_token']['screen_name'] . '<br />';    
  echo 'oauth_token:' . $_SESSION['access_token']['oauth_token'] . '<br />';    
  echo 'oauth_token_secret:' . $_SESSION['access_token']['oauth_token_secret'] . '<br />';

  echo "<br />";
/*
  echo "<pre>";
  var_dump($_SESSION['access_token']);
  echo "</pre>";
*/

//OAuth トークンも用いて TwitterOAuth をインスタンス化
$connection = new Abraham\TwitterOAuth\TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $_SESSION['access_token']['oauth_token'], $_SESSION['access_token']['oauth_token_secret']);

//$req = $twObj->OAuthRequest("https://api.twitter.com/1.1/statuses/user_timeline.json","GET",array("count"=>"10"));
//$req = $connection->get("users/show.json", array("screen_name"=>"tat_twitte",));
//$req = $connection->get("account/verify_credentials");

$req = $connection->get("statuses/home_timeline", array("count"=>"10"));
if (isset($req)) {
    foreach ($req as $key => $val) {
        echo $val->id;
        echo ' '.$val->user->name;
        echo ' '.date('Y-m-d H:i:s', strtotime($val->created_at));
        echo '<br>';
        echo $val->text;
        echo '<br>';
        echo '<br>';
    }
} else {
    echo 'No Tweets!';
}
/*
$req = $connection->get("search/tweets", array("q"=>"#地震", "count"=>"10",));
if (isset($req)) {
    foreach ($req->statuses as $key => $val) {
        echo $val->id;
        echo ' '.$val->user->name;
        echo ' '.date('Y-m-d H:i:s', strtotime($val->created_at));
        echo '<br>';
        echo $val->text;
        echo '<br>';
        echo '<br>';
    }
} else {
    echo 'No Tweets!';
}
*/

echo "<pre>";
var_dump($connection->getLastHttpCode());
var_dump($req);
echo "</pre>";

