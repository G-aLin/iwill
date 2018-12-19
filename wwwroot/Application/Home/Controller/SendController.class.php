<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台控制器
 * 主要获取首页聚合数据
 */
class SendController extends HomeController {


    public function send_message(){
        $data = I('post.');
        empty($data['email']) && $this->error(L('tips_2'));
        $mail = new \Think\Mail();
        $res =  $mail->SendMail($data['email'],$data['name'],$data['inquiry'],'我是发件人');
        if($res){
             $redata['status']  = 1;
             $redata['msg']  = 'send ok';
        }else {
               $redata['status']  = 0;
               $redata['msg']  = 'send error';
        }
         $this->ajaxReturn($redata);
    }

}