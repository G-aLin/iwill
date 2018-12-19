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
        $banner_list =M('banner b')->join('onethink_picture p ON p.id = b.picture_id')->where(['b.type'=>1,'b.status'=>1])->field('b.url,p.path')->order('b.level desc,b.id desc')->select();

        $this->assign('banner_list',$banner_list);//列表
        $this->assign('page',D('Document')->page);//分页
// var_dump($banner_list);
        $this->display();
    }

}