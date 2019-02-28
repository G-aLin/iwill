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
     * 设置阅读状态
     * @author huajie <banhuajie@163.com>
     */
    public function setIsread($model=''){
      $status = I('get.status');
      $status = $status == 1 ? 0: 1;
      $this->editRow('message', array('is_read'=>$status), array('id'=>I('get.id')));
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

        /**
     * 显示左边菜单，进行权限控制
     * @author huajie <banhuajie@163.com>
     */
    protected function getMenu(){
        //获取动态分类
        $cate_auth  =   AuthGroupModel::getAuthCategories(UID); //获取当前用户所有的内容权限节点
        $cate_auth  =   $cate_auth == null ? array() : $cate_auth;
        $cate       =   M('Category')->where(array('status'=>1))->field('id,title,pid,allow_publish')->order('pid,sort')->select();

        //没有权限的分类则不显示
        if(!IS_ROOT){
            foreach ($cate as $key=>$value){
                if(!in_array($value['id'], $cate_auth)){
                    unset($cate[$key]);
                }
            }
        }

        $cate           =   list_to_tree($cate);    //生成分类树

        //获取分类id
        $cate_id        =   I('param.cate_id');
        $this->cate_id  =   $cate_id;

        //是否展开分类
        $hide_cate = false;
        if(ACTION_NAME != 'recycle' && ACTION_NAME != 'draftbox' && ACTION_NAME != 'mydocument'){
            $hide_cate  =   true;
        }

        //生成每个分类的url
        foreach ($cate as $key=>&$value){
            $value['url']   =   'Article/index?cate_id='.$value['id'];
            if($cate_id == $value['id'] && $hide_cate){
                $value['current'] = true;
            }else{
                $value['current'] = false;
            }
            if(!empty($value['_child'])){
                $is_child = false;
                foreach ($value['_child'] as $ka=>&$va){
                    $va['url']      =   'Article/index?cate_id='.$va['id'];
                    if(!empty($va['_child'])){
                        foreach ($va['_child'] as $k=>&$v){
                            $v['url']   =   'Article/index?cate_id='.$v['id'];
                            $v['pid']   =   $va['id'];
                            $is_child = $v['id'] == $cate_id ? true : false;
                        }
                    }
                    //展开子分类的父分类
                    if($va['id'] == $cate_id || $is_child){
                        $is_child = false;
                        if($hide_cate){
                            $value['current']   =   true;
                            $va['current']      =   true;
                        }else{
                            $value['current']   =   false;
                            $va['current']      =   false;
                        }
                    }else{
                        $va['current']      =   false;
                    }
                }
            }
        }
        $this->assign('nodes',      $cate);
        $this->assign('cate_id',    $this->cate_id);
        //获取面包屑信息
        $nav = get_parent_category($cate_id);
        $this->assign('rightNav',   $nav);

        //获取回收站权限
        $this->assign('show_recycle', IS_ROOT || $this->checkRule('Admin/article/recycle'));
        //获取草稿箱权限
        $this->assign('show_draftbox', C('OPEN_DRAFTBOX'));
        //获取审核列表权限
        $this->assign('show_examine', IS_ROOT || $this->checkRule('Admin/article/examine'));
    }

}
