<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 行为控制器
 * @author huajie <banhuajie@163.com>
 */
class MessageController extends AdminController {

    /**
     * 列表
     * @author huajie <banhuajie@163.com>
     */
    public function index(){
        //获取列表数据
        $map['status']    =   array('gt', -1);
        $map['type']    =   array('eq', 1);
           if(isset($_GET['name'])){
            // $where['username']    =   array('like', '%'.(string)I('name').'%');
            // $userInfo = M('ucenter_member')->field('id')->where($where)->select();
            // $ids = array_column($userInfo, 'id');
            //   $map['uid'] = $ids ? array('in', $ids) : 0;
            $map['name']    =   array('like', '%'.(string)I('name').'%');
        }
        $list   =   $this->lists('message', $map);
        int_to_string($list);
        foreach ($list as $key=>$value){
            $userInfo = M('ucenter_member')->where(['id'=>$value['uid']])->find();
            $list[$key]['username']    =   $userInfo['username'];
            // $list[$key]['email']    =   $userInfo['email'];
            $list[$key]['item']    =  M('item')->where(['id'=>$value['item_id']])->getField('name');
        }
        $this->assign('_list', $list);
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();
    }

        /**
     */
    public function add(){
        $info = M('config_email')->where('id=1')->find();
        $this->assign('data', $info);
        $this->display();
    }

    /**
     * 查看行为日志
     * @author huajie <banhuajie@163.com>
     */
    public function edit($id = 0){
        empty($id) && $this->error('参数错误！');
        $info = M('message')->field(true)->find($id);
        $this->assign('info', $info);
        $data = M('config_email')->where('id=1')->find();
        $this->assign('data', $data);
          //回复记录
        $list = M('message')->where(['type'=>2,'reply_id'=>$id])->select();
        $this->assign('list', $list);
        $this->display();
    }



    public function send_message(){
        $data = I('post.');
        empty($data['content']) && $this->error('请输入回复内容！');
        $info = M('config_email')->where('id=1')->find();
        $mail = new \Think\Mail();
        $res =  $mail->SendMailByInfo($data['email'],$data['title'],$data['content'],$info,$info['send_name']);
        if($res){
            $Sqldata =[
                'type'=>2,
                'uid'=>session('user_auth.uid'),
                'reply_id'=>$data['id'],
                'item_id'=>$data['item_id'],
                'content'=>$data['content'],
                'create_time'=>date('Y-m-d H:s:',time())
            ];
            M('message')->data($Sqldata)->add();
            M('message')->where(['id'=>$data['id']])->setField('status',2);
            $this->success('提交成功', Cookie('__forward__'));
        }else {
             $this->error('网络异常，提交失败');
        }
    }


    /**
     * 删除日志
     * @param mixed $ids
     * @author huajie <banhuajie@163.com>
     */
    public function remove($ids = 0){
        empty($ids) && $this->error('参数错误！');
        if(is_array($ids)){
            $map['id'] = array('in', $ids);
        }elseif (is_numeric($ids)){
            $map['id'] = $ids;
        }
        $res = M('message')->where($map)->delete();
        if($res !== false){
            $this->success('删除成功！');
        }else {
            $this->error('删除失败！');
        }
    }

    /**
     * 清空日志
     */
    public function clear(){
        $res = M('message')->where('1=1')->delete();
        if($res !== false){
            $this->success('日志清空成功！');
        }else {
            $this->error('日志清空失败！');
        }
    }

        /**
     * 更新一条数据
     * @author huajie <banhuajie@163.com>
     */
    public function update(){
        $data = I('post.');
        empty($data['address']) && $this->error('邮箱地址不能为空！');
        empty($data['smtp']) && $this->error('邮箱SMTP服务器不能为空！');
        empty($data['name']) && $this->error('邮箱登录帐号不能为空！');
        empty($data['passpord']) && $this->error('邮箱密码不能为空！');
        M('config_email')->where(['id'=>1])->data($data)->save();
        $this->success('提交成功', Cookie('__forward__'));
    }

}
