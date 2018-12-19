<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Mobile\Controller;
use OT\DataDictionary;

/**
 * 前台控制器
 * 主要获取首页聚合数据
 */
class SendController extends HomeController {


    public function send_message(){
        echo 11;exit;
        $data = I('post.');
        empty($data['content']) && $this->error('请输入回复内容！');
        $mail = new \Think\Mail();
        $res =  $mail->SendMail($data['email'],$data['item'],$data['content'],'我是发件人');
        if($res){
            $Sqldata =[
                'uid'=>session('user_auth.uid'),
                'order_id'=>$data['order_id'],
                'content'=>$data['content'],
                'create_time'=>date('Y-m-d H:s:',time())
            ];
            M('order_message')->data($Sqldata)->add();
            $this->success('提交成功', Cookie('__forward__'));
        }else {
             $this->error('网络异常，提交失败');
        }
    }

}