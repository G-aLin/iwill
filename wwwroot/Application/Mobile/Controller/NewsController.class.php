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
class NewsController extends HomeController {

    private $_pageSize = 8 ;
   //
    public function lists(){
        $this->get_page_info(6);
        $this->assign('abs',1);//列表
        $this->assign('nav',3);//

        $pageSize = $this->_pageSize ;
        ++ $pageSize ;
        $list =M('news n')->join('onethink_picture p ON p.id = n.cover_id')->where(['n.status'=>1])->field('n.*,p.path')->order('n.level desc,n.publish_time desc')->limit(0,$pageSize)->select();
        $more = 0 ;
        if(count($list) == $pageSize){
            $more = 1;
            array_pop($list);
        }
        foreach ($list as $key => $value) {
            $publish_time = strtotime($value['publish_time']);
            $list[$key]['year'] = date('Y',$publish_time);
            $list[$key]['month'] =date('F',$publish_time);
            $list[$key]['day'] = date('d',$publish_time);
        }
        $this->assign('list', $list);
        $this->assign('more', $more);
// var_dump($list);
// exit;
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
        if(empty($detail)) $this->error('页面未找到');
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

        public function more(){
        $data =M('news n')->join('onethink_picture p ON p.id = n.cover_id')->where(['n.status'=>1])->field('n.*,p.path')->order('n.level desc,n.publish_time desc')->limit(0,$pageSize)->select();
        $data =array_slice($data,$this->_pageSize);
        foreach ($data as $key => $value) {
            $publish_time = strtotime($value['publish_time']);
            $data[$key]['year'] = date('Y',$publish_time);
            $data[$key]['month'] =date('F',$publish_time);
            $data[$key]['day'] = date('d',$publish_time);
        }
        $html = '';
        foreach ($data as $key => $value) {
             $html .= ' <div class="item">
              <div class="pic"><img src="'.$value['path'] .'"></div>
              <div class="info">
                <div class="time"><i class="icon-global bg-11"></i> '. $value['month'] . $value['day'] .','. $value['year'] .'</div>
                <div class="name">'. $value['title'] .'</div>
                <div class="desc">'. $value['digest'] .' [....]</div>
                <a class="more" href="'.U('News/detail?id='.$value['id']).'">Read More</a>
              </div>
            </div>';
        }
        $this->ajaxReturn(['status'=>1,'data'=>$html]);
    }


}