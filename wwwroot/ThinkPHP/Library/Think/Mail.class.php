<?php
namespace Think;

class Mail{
	/**
	 * @param string address 收邮人地址
	 * @param string title 商品类别id
	 * @param string message 邮件正文
	 * @param string fromname 商品类别id
	 */
	function SendMail($address,$title,$message,$fromname='NONE'){
		$mail = new PHPMailer\PHPMailer;
		$mail->IsSMTP();
		$mail->CharSet=C('MAIL_CHARSET');
		$mail->AddAddress($address);
		$mail->Body=$message;
		$mail->From= C('MAIL_ADDRESS');
		$mail->FromName=$fromname;
		$mail->Subject=$title;
		$mail->Host=C('MAIL_SMTP');
		$mail->SMTPAuth=C('MAIL_AUTH');
		$mail->Username=C('MAIL_LOGINNAME');
		$mail->Password=C('MAIL_PASSWORD');
		$mail->IsHTML(C('MAIL_HTML'));
		return($mail->Send());
	}

	function SendMailByInfo($address,$title,$message,$info,$fromname='NONE'){
		$mail = new PHPMailer\PHPMailer;
		$mail->IsSMTP();
		$mail->CharSet=C('MAIL_CHARSET');
		$mail->AddAddress($address);
		$mail->Body=$message;
		$mail->From= $info['address'];
		$mail->FromName=$fromname;
		$mail->Subject=$title;
		$mail->Host=$info['smtp'];
		$mail->SMTPAuth=C('MAIL_AUTH');
		$mail->Username=$info['name'];
		$mail->Password=$info['passpord'];
		$mail->IsHTML(C('MAIL_HTML'));
		return($mail->Send());
	}
}