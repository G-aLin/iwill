<?php
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 功能测试页面
 */
class TestController{
	function testemail(){
    	$mail = new \Think\Mail();
    	$mail->SendMail('128402131@qq.com','邮件标题','邮件正文','我测试来着');
    	exit;
	}
}
