<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Think\Page;

/**
 * 后台ICountry列表控制器
 * @author huajie <banhuajie@163.com>
 */
class CountryController extends AdminController {


    /**
     * 列表页
     */
    public function index(){
        /* 查询条件初始化 */
        $map = array();
        if(isset($_GET['title'])){
            $map['title']    =   array('like', '%'.(string)I('title').'%');
        }
        $list = $this->lists('country', $map,'id desc');
        $this->assign('list', $list);
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();
    }



    /**
     * 设置一条或者多条数据的状态
     * @author huajie <banhuajie@163.com>
     */
    public function setStatus($model=''){
      $status = I('get.status');
      $status = $status == 1 ? 0 : 1;
      $this->editRow('country', array('status'=>$status), array('id'=>I('get.id')));
    }


     /**
     * 删除一条或者多条数据的状态
     * @author huajie <banhuajie@163.com>
     */
    public function delete(){
      $country = M('country');
      $country->delete(I('get.id'));
      $this->success('删除成功', Cookie('__forward__'),ture);
    }

        public function spec_delete(){
      $country = M('country_spec');
      $country->delete(I('get.id'));
      $this->success('删除成功', Cookie('__forward__'),ture);
    }

    /**
     * 文档新增页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function add(){
        //获取左边菜单

        $this->display();
    }

        public function spec_add(){
        //获取左边菜单

        $this->assign('id', I('get.id'));
        $this->display();
    }

    /**
     * 文档编辑页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function edit(){
        //获取左边菜单

        $id     =   I('get.id','');
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        // 获取详细数据
        $data  =M('country')->where(['id'=>$id])->find();
        $data['cover_path']  =M('picture')->where(['id'=>$data['cover_id'] ])->getField('path');
        //获取当前分类的文档类型
        $this->assign('data', $data);
        $this->display();
    }



    /**
     * 更新一条数据
     * @author huajie <banhuajie@163.com>
     */
    public function update(){
        $data = I('post.');
        if(empty($data['name'])){
             $this->error('请输入名称');
        }
        $country = M("country");
        $Sqldata =[
                'name'=>$data['name'],
                'status'=>1,
                'level'=>$data['level'],
            ];
        if($data['id']){
            $Sqldata['id'] = $data['id'];
            $country->data($Sqldata)->save();
            $country_id = $data['id'] ;
        }else{
            $country_id = $country->data($Sqldata)->add();
        }
       if(empty($country_id)) {
            $this->error('网络异常，提交失败');
       }
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