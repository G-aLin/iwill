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
class CommentController extends AdminController {

    /**
     * 列表
     * @author huajie <banhuajie@163.com>
     */
    public function index(){
        //获取列表数据
        $map['status']    =   array('gt', -1);
        if(isset($_GET['name'])){
            $where['username']    =   array('like', '%'.(string)I('name').'%');
            $userInfo = M('ucenter_member')->field('id')->where($where)->select();
            $ids = array_column($userInfo, 'id');
            $map['uid'] = $ids ? array('in', $ids) : 0;
        }
        $list   =   $this->lists('comment', $map);
        int_to_string($list);
        foreach ($list as $key=>$value){
            $userInfo = M('ucenter_member')->where(['id'=>$value['uid']])->find();
            $list[$key]['username']    =   $userInfo['username'];
            $list[$key]['email']    =   $userInfo['email'];
            $list[$key]['item']    =  M('item')->where(['id'=>$value['item_id']])->getField('name');
        }
        $this->assign('_list', $list);
        $this->display();
    }

    /**
     * 查看行为日志
     * @author huajie <banhuajie@163.com>
     */
    public function edit($id = 0){
        empty($id) && $this->error('参数错误！');

        $info = M('comment')->field(true)->find($id);

        $this->assign('info', $info);
        $this->meta_title = '查看行为日志';
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
        $res = M('comment')->where($map)->delete();
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
      $this->editRow('comment', array('status'=>$status), array('id'=>I('get.id')));
    }

    /**
     * 清空日志
     */
    public function clear(){
        $res = M('comment')->where('1=1')->delete();
        if($res !== false){
            $this->success('删除成功！');
        }else {
            $this->error('删除失败！');
        }
    }

}
