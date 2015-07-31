<?php

$user = 'testuser';
  $pass = 'pass'; 
//  if (!isset($_SERVER['Authorization'])) {
  if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
      header('WWW-Authenticate: Basic realm="Private Page"');
      header('HTTP/1.0 401 Unauthorized');
      die("ログインするためには正しい入力情報が必要です");
  } else {
//	var_dump($_SERVER);
	$auth_tmp = explode( ' ', $_SERVER['HTTP_AUTHORIZATION'] );
	$auth_type = $auth_tmp[0];
	$auth_tmp = explode( ':', base64_decode( $auth_tmp[1] ) );
	$auth_user = $auth_tmp[0];
	$auth_pass = $auth_tmp[1];
	
      if ($auth_user != $user || $auth_pass != $pass) {
          header('WWW-Authenticate: Basic realm="Private Page"');
          header('HTTP/1.0 401 Unauthorized');
          die("入力情報が一致しません");
      }
  }
  
  echo "type:".$auth_type."\n";
  echo "user:".$auth_user."\n";
  echo "pass:".$auth_pass."\n";
  
echo"ログイン成功です";
