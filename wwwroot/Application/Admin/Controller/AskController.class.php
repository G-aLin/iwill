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
class AskController extends AdminController {

    /**
     * 列表
     * @author huajie <banhuajie@163.com>
     */
    public function index(){
        //获取列表数据
        $map['status']    =   array('gt', -1);
        $map['type']    =   array('eq', 1);
        if(isset($_GET['name'])){
            $where['username']    =   array('like', '%'.(string)I('name').'%');
            $userInfo = M('ucenter_member')->field('id')->where($where)->select();
            $ids = array_column($userInfo, 'id');
            $map['uid'] = $ids ? array('in', $ids) : 0;
        }
        $list   =   $this->lists('item_question', $map);
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
     * 查看行为日志
     * @author huajie <banhuajie@163.com>
     */
    public function edit($id = 0){
        empty($id) && $this->error('参数错误！');

        $info = M('item_question')->field(true)->find($id);
        $userInfo = M('ucenter_member')->where(['id'=>$info['uid']])->find();
        $info['username']    =   $userInfo['username'];
        // $list[$key]['email']    =   $userInfo['email'];
        $info['item']    =  M('item')->where(['id'=>$info['item_id']])->getField('name');
        $info['reply_content']    =  M('item_question')->where(['reply_id'=>$id])->getField('content');
        $info['rid']    =  M('item_question')->where(['reply_id'=>$id])->getField('id');
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
        $res = M('item_question')->where($map)->delete();
        if($res !== false){
            $this->success('删除成功！');
        }else {
            $this->error('删除失败！');
        }
    }

        /**
     * 设置一条或者多条数据的状态
     * @author huajie <banhuajie@163.com>
     */
    public function setStatus($model=''){
      $status = I('get.status');
      $status = $status == 1 ? 2: 1;
      $this->editRow('item_question', array('status'=>$status), array('id'=>I('get.id')));
    }

    /**
     * 清空日志
     */
    public function clear(){
        $res = M('item_question')->where('1=1')->delete();
        if($res !== false){
            $this->success('删除成功！');
        }else {
            $this->error('删除失败！');
        }
    }

        public function update(){
        $data = I('post.');
        $item_question = M("item_question");
        $Sqldata =[
                'type'=>2,
                'reply_id'=>$data['id'],
                'uid'=>session('user_auth')['uid'],
                'item_id'=>$data['item_id'],
                'content'=>$data['content'],
                'status'=>1,
                'create_time'=>date('Y-m-d H:i:s',time())
            ];
        if($data['rid']){
            $Sqldata['id'] = $data['rid'];
            $item_question->data($Sqldata)->save();
        }else{
            $item_question->data($Sqldata)->add();
            $item_question->where(['id'=>$data['id']])->setField('is_reply',1);
        }
        $this->success('提交成功', Cookie('__forward__'));
    }

}
