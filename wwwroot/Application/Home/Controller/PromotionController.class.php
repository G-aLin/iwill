<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class PromotionController extends HomeController {

   //
    public function deals(){

        $this->get_page_info(8);
        $this->assign('abs',1);
        $this->assign('nav',4);//

        $banner =M('banner b')->join('onethink_picture p ON p.id = b.picture_id')->where(['b.type'=>3,'b.status'=>1])->field('b.url,p.path')->find();
        $ad =M('banner b')->join('onethink_picture p ON p.id = b.picture_id')->where(['b.type'=>4,'b.status'=>1])->field('b.url,p.path')->find();
     // var_dump($banner)  ;
     // var_dump($ad)  ; exit;
        $this->assign('banner',$banner);
        $this->assign('ad',$ad);//

        $count = M('promotion n')->where(['n.status'=>1])->count();
        $page = new \Think\Page($count,10);
        $list =  M('promotion n')->where(['n.status'=>1])->order('level desc,id desc')->limit($page->firstRow.','.$page->listRows)->select();
        $page->setConfig('prev','Prev page');
        $page->setConfig('next','Next page');
        $this->assign('_page',$page->show());
        $this->assign('list', $list);
        $this->display();
    }


}