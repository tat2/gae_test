<?php
// 日本語

/*
Consumer key: *

Consumer secret: 
qfIYHJ9qi23HLcjV4fvK7UBV13CcaMeCuJKeWAGWTAAU0iljRK

Request URI: 
https://api.twitter.com/1.1/
*/

session_start(); 

require_once VENDORS."Abraham/TwitterOAuth/autoload.php";
//use Abraham\TwitterOAuth\TwitterOAuth;

const TWITTER_CONSUMER_KEY ='';
const TWITTER_CONSUMER_SECRET ='';
const TWITTER_OAUTH_CALLBACK = 'http://fit-visitor-597.appspot.com/pages/twittercallback';

//TwitterOAuth をインスタンス化
$connection = new Abraham\TwitterOAuth\TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);

//echo "<pre>";
//コールバックURLをここでセット
try {
	$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => TWITTER_OAUTH_CALLBACK));
	//$request_token = $connection->oauth('oauth/request_token');
//	var_dump($request_token);
} catch (Abraham\TwitterOAuth\TwitterOAuthException $e) {
//	echo 'exception: '.$e->getMessage();
}
//echo "</pre>";

//callback.phpで使うのでセッションに入れる
$_SESSION['oauth_token'] = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

//Twitter.com 上の認証画面のURLを取得( この行についてはコメント欄も参照 )
$url = $connection->url('oauth/authenticate', array('oauth_token' => $request_token['oauth_token']));


//echo "<pre>";
//var_dump($url);
//echo "</pre>";

//Twitter.com の認証画面へリダイレクト
header( 'Location: '. $url );
exit();

