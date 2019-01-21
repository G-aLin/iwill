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
class AboutController extends HomeController {

   //关于我们
    public function us(){

        $this->get_page_info(10);
        $this->assign('abs',1);//列表

        $data  =M('contact')->where(['id'=>2])->find();
        $data['img_path']  =M('picture')->where(['id'=>$data['img'] ])->getField('path');
        $data_img =M('contact_team_pic c')->join('onethink_picture p ON p.id = c.picture_id')->where(['c.status'=>1])->field('p.path')->order('c.level desc')->select();
        $this->assign('data',$data);//
        $this->assign('data_img',$data_img);//
        $this->display();
    }

   //contact我们
    public function contact(){

        $this->get_page_info(9);
        $this->assign('abs',1);//
        $this->assign('nav',5);//
        $this->display();
    }

       //policy
    public function policy(){
        $id= I('get.id');
        $this->get_page_info(1);
        $this->assign('fixed',1);//
        $data  =M('policy')->where(['status'=>1])->field('id,title')->order('level desc')->select();
        $this->assign('data',$data);//

        $id =  $id ?  $id : $data[0]['id'];
        $info  =M('policy')->where(['id'=>$id,'status'=>1])->find();
        $this->assign('info',$info);//
        $this->assign('id',$id);//
        $this->display();
    }

}