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

//login.phpでセットしたセッション
$request_token = [];  // [] は array() の短縮記法。詳しくは以下の「追々記」参照
$request_token['oauth_token'] = $_SESSION['oauth_token'];
$request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

//Twitterから返されたOAuthトークンと、あらかじめlogin.phpで入れておいたセッション上のものと一致するかをチェック
if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
    die( 'Error!' );
}

//OAuth トークンも用いて TwitterOAuth をインスタンス化
$connection = new Abraham\TwitterOAuth\TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);

//アプリでは、access_token(配列になっています)をうまく使って、Twitter上のアカウントを操作していきます
$_SESSION['access_token'] = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));
/*
ちなみに、この変数の中に、OAuthトークンとトークンシークレットが配列となって入っています。
*/

//セッションIDをリジェネレート
session_regenerate_id();

//マイページへリダイレクト
header( 'Location: /pages/twitterdisp' );
exit();

