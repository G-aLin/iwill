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
        $this->getMenu();
        $this->display();
    }

        public function spec_add(){
        //获取左边菜单
        $this->getMenu();
        $this->assign('id', I('get.id'));
        $this->display();
    }

    /**
     * 文档编辑页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function edit(){
        //获取左边菜单
        $this->getMenu();
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





}