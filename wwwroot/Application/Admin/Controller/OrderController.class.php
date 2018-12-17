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
            $list[$key]['email']    =   $userInfo['email'];
            $list[$key]['item']    =M('order_item')->where(['order_id'=>$value['id']])->find();
            $itemName = explode(",", $list[$key]['item']['item_spec_name'] );
            $itemSpec = explode(",", $list[$key]['item']['item_spec'] );
            if(!empty($itemName[0])){
                  $count = count($itemName);
                    for ($i=0; $i < $count ; $i++) {
                            $list[$key]['item']['spec_info'] .= $itemName[$i]."：".$itemSpec[$i]."；";
                    }
                    $list[$key]['item']['spec_info'] = rtrim($list[$key]['item']['spec_info'],";");
            }
        }
// var_dump($list) ;exit;
        $this->assign('_list', $list);
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();
    }

    /**
     * 查看行为日志
     * @author huajie <banhuajie@163.com>
     */
    public function edit($id = 0){
        empty($id) && $this->error('参数错误！');
        $info = M('order')->field(true)->find($id);
        $userInfo = M('ucenter_member')->where(['id'=>$info['uid']])->find();
        $info['username']    =   $userInfo['username'];
        $info['email']    =   $userInfo['email'];
        //回复记录
        $list = M('order_message')->where(['order_id'=>$id])->select();
        foreach ($list as $key => $value) {
            $list[$key]['admin_name'] = M('ucenter_member')->where(['id'=>$value['uid']])->getField('username');
        }
        $this->assign('info', $info);
        $this->assign('list', $list);
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
