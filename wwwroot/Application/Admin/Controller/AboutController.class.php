<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use Think\Page;

/**
 * 后台列表控制器
 * @author huajie <banhuajie@163.com>
 */
class AboutController extends AdminController {

    /* 保存允许访问的公共方法 */
    static protected $allow = array( 'draftbox','mydocument');

       private $cate_id        =   null; //文档分类id

    /**
     */
    public function us(){
        //获取左边菜单
        $this->getMenu();
        // 获取详细数据
        $data  =M('contact')->where(['id'=>2])->find();
        $data['img_path']  =M('picture')->where(['id'=>$data['img'] ])->getField('path');
        //获取当前分类的文档类型
        $this->assign('data', $data);
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();
    }

        public function update_about(){
        $data = I('post.');
        $result = M("contact")->data($data)->save();
    
        $this->success('提交成功', Cookie('__forward__'));
    }


        /**
     * 列表页
     */
    public function index(){
         $this->getMenu();
        /* 查询条件初始化 */
        $map = array();
        if(isset($_GET['title'])){
            $map['name']    =   array('like', '%'.(string)I('title').'%');
        }
        $list = $this->lists('contact_team_pic', $map,'level desc');
        $pictureM = M('picture') ;
        foreach ($list as $key => $value) {
            $list[$key]['picture_url'] = $pictureM->where(['id'=>$value['picture_id']])->getField('path');
        }
        $this->assign('list', $list);
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();
    }

       /**
     * 删除一条或者多条数据的状态
     * @author huajie <banhuajie@163.com>
     */
    public function delete(){
      $item_category = M('contact_team_pic');
      $item_category->delete(I('get.id'));
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
        $data  =M('contact_team_pic')->where(['id'=>$id])->find();
        $data['picture_path']  =M('picture')->where(['id'=>$data['picture_id'] ])->getField('path');
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
        if(empty($data['picture_id'])){
             $this->error('请上传图片');
        }
        $item_category = M("contact_team_pic");
        $Sqldata =[
                'picture_id'=>$data['picture_id'],
                'level'=>$data['level'],
            ];
        if($data['id']){
            $Sqldata['id'] = $data['id'];
            $item_category->data($Sqldata)->save();
            $item_category_id = $data['id'] ;
        }else{
            $item_category_id = $item_category->data($Sqldata)->add();
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