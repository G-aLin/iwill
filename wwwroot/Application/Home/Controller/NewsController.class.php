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
class NewsController extends HomeController {

   //
    public function lists(){
        $this->get_page_info(6);
        $this->assign('abs',1);//列表
        $this->assign('nav',3);//

        $count = M('news n')->where(['n.status'=>1])->count();
        $page = new \Think\Page($count,6);
        $news_list =M('news n')->join('onethink_picture p ON p.id = n.cover_id')->where(['n.status'=>1])->field('n.*,p.path')->order('n.level desc,n.publish_time desc')->limit($page->firstRow.','.$page->listRows)->select();
        $page->setConfig('prev','Prev page');
        $page->setConfig('next','Next page');
        $pageStyle = $page->show();
        foreach ($news_list as $key => $value) {
            $publish_time = strtotime($value['publish_time']);
            $news_list[$key]['year'] = date('Y',$publish_time);
            $news_list[$key]['month'] =date('F',$publish_time);
            $news_list[$key]['day'] = date('d',$publish_time);
        }
// var_dump($news_list);
// exit;
        $this->assign('_page',$pageStyle);
        $this->assign('data',$news_list);
        $this->display();
    }

       //
    public function detail(){
        $this->get_page_info(7);
        $this->assign('abs',1);//列表
        $this->assign('nav',3);//
        $id   =   I('get.id');
        $newsM = M('news') ;
        $detail = $newsM->where(['id'=>$id,'status'=>1])->find();
        if(empty($detail)) $this->error('404 Page Not Found');
        $path = M('picture')->where(['id'=>$detail['cover_id']])->getField('path');
        $detail['path'] = C('WWW').$path ;
        $detail['shareUrl'] = C('WWW').'/news/detail/id/'.$id.'.html' ;
//  var_dump($detail);
// exit;
        $map['status'] = 1;
        $map['publish_time']  = array('gt',$detail['publish_time']);
        $next=$newsM->where($map)->field('id,title')->find();
        $map['publish_time']  = array('lt',$detail['publish_time']);
        $previous  = $newsM->where($map)->field('id,title')->find();

        $this->assign('next',$next);
        $this->assign('previous',$previous);
        $this->assign('data',$detail);

        $this->display();
    }


}