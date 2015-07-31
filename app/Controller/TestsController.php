<?php

App::uses('AppController', 'Controller');

class TestsController extends AppController {

	public $uses = array();
	
	public $layout = null;
	
	public $autoRender = null;
	
	
	public function index() {
		
		print "Hello, Beutiful World!";
		
		
	}
	
	
/*	public function imap()
	{
		// 個別設定/////////////////
		// メールボックスの設定-----
		$host    = 'imap.spmode.ne.jp';
		$user    = '09098397279';
		$pass    = 'adgj3374';
//		$host    = 'imap.gmail.com';
//		$user    = 'tat.googl@gmail.com';
//		$pass    = '4096adgj';
		//--------------------------

		/////////////////////////////////////////////////////////////////////////////////

		$file_pointer = FsockOpen($host, 993, $err, $errno, 10) or die("サーバに接続不能!!");
		$memo = FgetS($file_pointer, 512);
		FputS($file_pointer, "USER $user\r\n");
		$memo = FgetS($file_pointer, 512);
		FputS($file_pointer, "PASS $pass\r\n");
		$memo = FgetS($file_pointer, 512);
		FputS($file_pointer, "STAT\r\n");
		$memo = FgetS($file_pointer, 512);
		List($strings, $kensu, $size) = Explode(' ', $memo);

			if ($kensu == 0) {
				$announcement = '新着メールはありません。';
			}
			else {
				$announcement = '新着メールがあります。';
			}
		FputS($file_pointer, "QUIT\r\n");
		Fclose($file_pointer);

		//============================ html 表示 ==============================================
		 print "<html><head>\n";
		 print "<title>imap</title>\n";
		 print "</head>\n";
		 print "<body topmargin=50 leftmargin=50>\n";
		 print "{$announcement}<br>\n";	
		 print "</body></html>\n";
		 exit;
	}
	*/
}
