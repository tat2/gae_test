<?php
// 日本語

/*
Consumer key: *
2hIs8NcwdL5JgbGistitldbJ4

Consumer secret: 
qfIYHJ9qi23HLcjV4fvK7UBV13CcaMeCuJKeWAGWTAAU0iljRK

Request URI: 
https://api.twitter.com/1.1/
*/

session_start(); 

require_once VENDORS."Abraham/TwitterOAuth/autoload.php";
//use Abraham\TwitterOAuth\TwitterOAuth;

const TWITTER_CONSUMER_KEY ='2hIs8NcwdL5JgbGistitldbJ4';
const TWITTER_CONSUMER_SECRET ='qfIYHJ9qi23HLcjV4fvK7UBV13CcaMeCuJKeWAGWTAAU0iljRK';


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

$req = $connection->post("statuses/update", array("status"=>"テストツイート ".microtime()." #tattestapp"));

echo "<pre>";
var_dump($connection->getLastHttpCode());
var_dump($req);
echo "</pre>";

/*
//$tw_arr = json_decode($req);
$tw_arr = $req;

if (isset($tw_arr)) {
    foreach ($tw_arr as $key => $val) {
        echo $tw_arr[$key]->text;
        echo ' '.date('Y-m-d H:i:s', strtotime($tw_arr[$key]->created_at));
        echo '<br>';
    }
} else {
    echo 'つぶやきはありません。';
}
*/

