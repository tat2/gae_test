<?php

//App::import('Vendor', 'Mail_mimeDecode', array('file'=>'Mail/mimeDecode.php'));
require_once 'Mail/mimeDecode.php';




$host = 'imap.spmode.ne.jp'; // ホスト
$port = 993; // ポート番号
$user = ''; // ユーザー名
$password = ''; // パスワード

/*
// メールボックスを開きます
// INBOX は受信トレイになります。
$mbox = imap_open("{{$host}:{$port}/imap/ssl}INBOX", $user, $password) or exit("Connection Error");

// 閉じる
imap_close($mbox);
*/

$ch = curl_init();
if($ch) {
	
	/* Set username and password */ 
	curl_setopt($ch, CURLOPT_USERNAME, $user);
	curl_setopt($ch, CURLOPT_PASSWORD, $password);
	
	/* This will fetch message 1 from the user's inbox */ 
	//curl_setopt($ch, CURLOPT_URL, 'imaps://'.$host.':'.$port.'/INBOX/');
	curl_setopt($ch, CURLOPT_URL, 'imaps://'.$host.':993/INBOX/;UID=143');
	
/*
imap://user:password@mail.example.com - Performs a top level folder list
imap://user:password@mail.example.com/INBOX - Performs a folder list on the user's inbox
imap://user:password@mail.example.com/INBOX/;UID=1 - Selects the user's inbox and fetches message 1
imap://user:password@mail.example.com/INBOX;UIDVALIDITY=50/;UID=2 - Selects the user's inbox, checks the UIDVALIDITY of the mailbox is 50 and fetches message 2 if it is
imap://user:password@mail.example.com/INBOX/;UID=3/;SECTION=TEXT - Selects the user's inbox and fetches the text portion of message 3
imap://user:password@mail.example.com/INBOX/;UID=4/;PARTIAL=0.1024 - Selects the user's inbox and fetches the first 1024 octets of message 4
imap://user:password@mail.example.com/INBOX?NEW - Selects the user's inbox and checks for NEW messages
imap://user:password@mail.example.com/INBOX?SUBJECT%20shadows - Selects the user's inbox and searches for messages containing "shadows" in the subject line
*/
	
	$proxy = '127.0.0.1:8888';
	//$proxyauth = 'user:password';

	//curl_setopt($ch, CURLOPT_PROXY, $proxy);
	//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	
	/* Perform the fetch */ 
	$res = curl_exec($ch);
	
	/* Always cleanup */ 
	curl_close($ch);
	
$params = array();
$params['include_bodies'] = true;
$params['decode_bodies'] = true;
$params['decode_headers'] = true;

	$decoder = new Mail_mimeDecode( $res ); // MIME分解
$structure = $decoder->decode($params);
	
	echo "<pre>";
	//var_dump($res);
	var_dump($structure);
	echo "</pre>";
	
	
	
	
}

