<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use OT\File;

/**
 * 在线更新
 * @author huajie <banhuajie@163.com>
 */
class UpdateController extends AdminController{

	/**
	 * 初始化页面
	 * @author huajie <banhuajie@163.com>
	 */
	public function index(){
		$this->meta_title = '在线更新';
		if(IS_POST){
			$this->display();
			//检查新版本
			// $version = $this->checkVersion();
			//在线更新
			// $this->update($version);
		}else{
			$this->display();
		}
	}

	/**
	 * 检查新版本
	 * @author huajie <banhuajie@163.com>
	 */
	private function checkVersion(){
		$this->showMsg('未发现新版本', 'success');
	}

	/**
	 * 在线更新
	 * @author huajie <banhuajie@163.com>
	 */
	private function update($version){
		//PclZip类库不支持命名空间

	}

	/**
	 * 获取远程数据
	 * @author huajie <banhuajie@163.com>
	 */
	private function getRemoteUrl($url = '', $method = '', $param = ''){
		$opts = array(
			CURLOPT_TIMEOUT        => 20,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL            => $url,
			CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'],
		);
		if($method === 'post'){
			$opts[CURLOPT_POST] = 1;
			$opts[CURLOPT_POSTFIELDS] = $param;
		}

		/* 初始化并执行curl请求 */
		$ch = curl_init();
		curl_setopt_array($ch, $opts);
		$data  = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);
		return $data;
	}

	/**
	 * 实时显示提示信息
	 * @param  string $msg 提示信息
	 * @param  string $class 输出样式（success:成功，error:失败）
	 * @author huajie <banhuajie@163.com>
	 */
	private function showMsg($msg, $class = ''){
		echo "<script type=\"text/javascript\">showmsg(\"{$msg}\",\"{$class}\")</script>";
		flush();
		ob_flush();
	}

	/**
	 * 生成更新文件夹名
	 * @author huajie <banhuajie@163.com>
	 */
	private function getUpdateFolder(){
		$key = sha1(C('DATA_AUTH_KEY'));
		return 'update_'.$key;
	}
}