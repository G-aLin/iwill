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
class OrderController extends AdminController {

    /**
     * 列表
     * @author huajie <banhuajie@163.com>
     */
    public function index(){
        //获取列表数据
        $map['status']    =   array('gt', 0);
        if(isset($_GET['name'])){
            $where['username']    =   array('like', '%'.(string)I('name').'%');
            $userInfo = M('ucenter_member')->field('id')->where($where)->select();
            $ids = array_column($userInfo, 'id');
              $map['uid'] = $ids ? array('in', $ids) : 0;
        }
        $list   =   $this->lists('order', $map);
        int_to_string($list);
        foreach ($list as $key=>$value){
            $userInfo = M('ucenter_member')->where(['id'=>$value['uid']])->find();
            $list[$key]['username']    =   $userInfo['username'];
            // $list[$key]['email']    =   $userInfo['email'];
        }
// var_dump($list) ;exit;
        $this->assign('_list', $list);
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();
    }

               /**
     * 设置阅读状态
     * @author huajie <banhuajie@163.com>
     */
    public function setIsread($model=''){
      $status = I('get.status');
      $status = $status == 1 ? 0: 1;
      $this->editRow('order', array('is_read'=>$status), array('id'=>I('get.id')));
    }

    public function setallisread($ids = 0){
        empty($ids) && $this->error('参数错误！');
        if(is_array($ids)){
            $map['id'] = array('in', $ids);
        }elseif (is_numeric($ids)){
            $map['id'] = $ids;
        }
        $res = M('order')->where($map)->save(['is_read'=>1]);
        $this->success('设置成功！');
    }

    /**
     * 查看行为日志
     * @author huajie <banhuajie@163.com>
     */
    public function edit($id = 0){
        empty($id) && $this->error('参数错误！');
        $info = M('order')->field(true)->find($id);
        //回复记录
        $list = M('order_message')->where(['order_id'=>$id])->order('id desc')->select();
        $this->assign('info', $info);
        $this->assign('list', $list);
        $this->display();
    }

        public function detail($id = 0){
        empty($id) && $this->error('参数错误！');
        $info = M('order')->field(true)->find($id);
        $userInfo = M('ucenter_member')->where(['id'=>$info['uid']])->find();
        $info['username']    =   $userInfo['username'];
        $this->assign('info', $info);
        $this->display();
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
        $res = M('order')->where($map)->save(['status'=>0]);
        if($res !== false){
            $this->success('删除成功！');
        }else {
            $this->error('删除失败！');
        }
    }

        public function update(){
        $data = I('post.');
        $news = M("order");
        $Sqldata =[
                'num'=>$data['num'],
                'unit_price'=>$data['unit_price'],
                'subtotal'=>$data['subtotal'],
                'shipping'=>$data['shipping'],
                'taxex'=>$data['taxex'],
                'total'=>$data['total']
            ];
            $Sqldata['id'] = $data['id'];
            $news->data($Sqldata)->save();
        $this->success('提交成功', Cookie('__forward__'));
    }

    /**
     * 清空日志
     */
    public function clear(){
        $res = M('order')->where('1=1')->delete();
        if($res !== false){
            $this->success('日志清空成功！');
        }else {
            $this->error('日志清空失败！');
        }
    }

    public function send_message(){
        $data = I('post.');
        if(empty($data['content']) && empty($data['web_content'])) $this->error('请输入回复内容！');
        $Sqldata =[
                'uid'=>$data['uid'],
                'order_id'=>$data['order_id'],
                'status'=>1,
                'is_read'=>0,
                'create_time'=>date('Y-m-d H:s:',time())
            ];
        if($data['web_content']){
                 $Sqldata['type'] = 1;
                 $Sqldata['content'] = $data['web_content'];
                 M('order_message')->data($Sqldata)->add();
        }
        if($data['content']){
                $info = M('config_email')->where('id=1')->find();
                $mail = new \Think\Mail();
                $res =  $mail->SendMailByInfo($data['email'],$data['item'],$data['content'],$info,$info['send_name']);
                if($res){
                    $Sqldata['type'] = 2;
                    $Sqldata['content'] = $data['content'];
                    $Sqldata['email'] = $data['email'];
                    M('order_message')->data($Sqldata)->add();
                }else {
                     $this->error('网络异常，提交失败');
                }
        }
        $this->success('提交成功', Cookie('__forward__'));
    }

}
