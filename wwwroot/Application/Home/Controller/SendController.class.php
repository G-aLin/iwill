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
             $redata['msg']  = 'Send successfully';
        }else {
               $redata['status']  = 0;
               $redata['msg']  = 'fail in send';
        }
         $this->ajaxReturn($redata);
    }

           public function message(){
                        $post = I('post.');
                        if(!check_verify($post['captcha'])){
                            $this->error('Verification code input error！');
                        } ;
                        $data['uid'] = session('user_auth')['uid'] ;
                        $data['item_id'] = 0 ;
                        $data['status'] = 1 ;
                        $data['type'] = 1 ;
                        $data['reply_id'] = 0 ;
                        $data['content'] = $post['inquiry'] ;
                        $data['name'] = $post['name'] ;
                        $data['email'] = $post['email'] ;
                        $data['create_time'] = date('Y-m-d H:i:s',time());
                        $res = M("message")->add($data);
                          if($res !== false){ //成功
                                send_email_tpl($data['email'],3);
                                $this->success('Submit successfully');
                            } else { //注册失败，显示错误信息
                                $this->error($this->showRegError($uid));
                        }
    }

}