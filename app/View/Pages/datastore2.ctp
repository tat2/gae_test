<?php

require_once VENDORS.'Google/autoload.php';

require_once VENDORS.'GDS/'.'Entity.php';
require_once VENDORS.'GDS/'.'Gateway.php';
require_once VENDORS.'GDS/'.'Mapper.php';
require_once VENDORS.'GDS/'.'Schema.php';
require_once VENDORS.'GDS/'.'Store.php';

const APP_NAME ='test';	//何でも良い
const ACCOUNT_NAME ='';	//api認証情報のメールアドレス
const DATASET_ID ='fit-visitor-597';	//プロジェクトID

$key = VENDORS.'';	//api
//$key = file_get_contents(VENDORS.'');

$obj_client = GDS\Gateway::createGoogleClient(APP_NAME, ACCOUNT_NAME, $key);
$obj_gateway = new GDS\Gateway($obj_client, DATASET_ID);
$obj_book_store = new GDS\Store($obj_gateway, 'Book');	//エンティティの種類(名前)

//Create a record and insert into the Datastore (see below for Alternative Array Syntax)
$obj_book = new GDS\Entity();
$obj_book->setKeyId('5715999101812736');
//$obj_book->setKeyName('testname2');	//
//$obj_book->title = 'Perman';	//各プロパティ
//$obj_book->author = 'Fujiko F Fujio';	//各プロパティ
//$obj_book->isbn = '567890';	//各プロパティ

// Write it to Datastore
//$ret = $obj_book_store->upsert($obj_book);
$ret = $obj_book_store->delete($obj_book);
echo "<pre>";
var_dump($ret);
echo "</pre>";

//Fetch all the Books from the Datastore and display their titles and ISBN numbers
$obj_data_store = new GDS\Store($obj_gateway, 'Book');
$arr_key = array('testname','testname2',);
echo "<pre>";
foreach($obj_data_store->fetchAll() as $obj_data_tmp) {
//foreach($obj_data_store->fetchByIds($arr_key) as $obj_data_tmp) {
//foreach($obj_data_store->fetchByNames($arr_key) as $obj_data_tmp) {
	//echo "Title: {$obj_data_tmp->title}, ISBN: {$obj_data_tmp->isbn}", PHP_EOL;
	var_dump($obj_data_tmp);
}
echo "</pre>";




