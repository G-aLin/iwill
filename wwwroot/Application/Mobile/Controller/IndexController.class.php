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
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {

	//系统首页
    public function index(){
        $this->get_page_info(1);
        $this->assign('abs',1);
        $this->assign('index',1);
        $this->assign('nav',1);//

        $banner_list =M('banner b')->join('onethink_picture p ON p.id = b.picture_id')->where(['b.type'=>1,'b.status'=>1])->field('b.url,p.path')->order('b.level desc,b.id desc')->select();
        $this->assign('banner_list',$banner_list);//列表

        $rotation_list  =M('recommend b')->join('onethink_picture p ON p.id = b.picture_id')->where(['b.type'=>1,'b.status'=>1])->field('b.value,p.path')->order('b.level desc,b.id desc')->select();
        $this->assign('rotation_list',$rotation_list);//列表

        $categories_list  =M('recommend b')->join('onethink_picture p ON p.id = b.picture_id')->join('onethink_item_category i ON i.id = b.value')->where(['b.type'=>2,'b.status'=>1])->field('b.value,p.path,i.name')->order('b.level desc,b.id desc')->select();
        $this->assign('categories_list',$categories_list);//列表

        $hot_product_list  =M('recommend b')->join('onethink_picture p ON p.id = b.picture_id')->join('onethink_item i ON i.id = b.value')->where(['b.type'=>3,'b.status'=>1])->field('b.value,p.path,i.name,i.description')->order('b.level desc,b.id desc')->select();
        $this->assign('hot_product_list',$hot_product_list);//列表
// var_dump($rotation_list);
// var_dump($categories_list);
// var_dump($hot_product_list);exit;
        $data  =M('contact')->where(['id'=>2])->find();
        $this->assign('data',$data);//关于我们
        $this->display();
    }


}