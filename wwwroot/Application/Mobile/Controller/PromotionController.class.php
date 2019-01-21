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
class PromotionController extends HomeController {

    private $_pageSize = 3 ;
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

        $pageSize = $this->_pageSize ;
        ++ $pageSize ;
        $list =  M('promotion')->where(['status'=>1])->order('level desc,id desc')->limit(0,$pageSize)->select();
        $more = 0 ;      
        if(count($list) == $pageSize){
            $more = 1;
            array_pop($list);
        } 
        $this->assign('list', $list);
        $this->assign('more', $more);
        $this->display();
    }

    public function more(){
        $data =  M('promotion n')->where(['n.status'=>1])->order('level desc,id desc')->select();
        $data =array_slice($data,$this->_pageSize);
        $html = '';
        foreach ($data as $key => $value) {
             $html .= '<div class="item">
              <i class="icon-switch"></i>
              <div class="name">'. $value['title'] .'</div>
              <div class="desc">'.  $value['content']  .'<a class="c-green underline" href="'.  $value['url']  .'">More information</a></div>
            </div>';
        }
        $this->ajaxReturn(['status'=>1,'data'=>$html]);
    }

}