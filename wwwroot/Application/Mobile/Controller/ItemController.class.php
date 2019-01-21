<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Mobile\Controller;
use User\Api\UserApi;

/**
 * 控制器
 * 包括用户中心，用户登录及注册
 */
class ItemController extends HomeController {

	/* 首页 */
	public function lists(){
                    $this->get_page_info(4);
                    $this->assign('fixed',1);//
                    $this->assign('nav',2);//

                    $map['pid']  =0;
                    $map['status']  = 1;
                    $data  =M('item_category')->where($map)->order('level desc')->select();
                    $pictureM = M('picture') ;
                    $category_id = [];
                    foreach ($data as $key => $value) {
                        $data[$key]['banner_path'] = $pictureM->where(['id'=>$value['banner_id']])->getField('path');
                        $map['pid']  = $value['id'];
                        $data[$key]['list'] = M('item_category')->where($map)->field('id,name')->order('level desc')->select();
                        foreach ($data[$key]['list']  as $k=> $v) {
                            $data[$key]['category'][] =$v['id'];
                        }
                        $data[$key]['item'] = [];
                        if($data[$key]['category']){
                            $item_map['category_id']  = array('in',$data[$key]['category']);
                            $item_map['status']  = 1;
                            $data[$key]['isMore'] = M('item')->where($item_map)->count();
                            $data[$key]['item'] = M('item')->where($item_map)->field('id,name,star,bulky_price')->order('sort desc,id desc')->limit(4)->select();
                            foreach ($data[$key]['item'] as $k => $v) {
                                $data[$key]['item'][$k]['path'] =M('item_pic b')->join('onethink_picture p ON p.id = b.picture_id')->where(['b.item_id'=>$v['id'],'b.type'=>2])->order('b.id asc')->getField('p.path');
                            }
                        }
                    }
     // var_dump($data[0]) ;exit;
                   $this->assign('data',$data);//
                   $this->display();
	}

        public function detail(){
            $this->get_page_info(5);
            $this->assign('nav',2);//

            $id   =   I('get.id');
            $ViewHistory = (array)cookie('ViewHistory');
            //浏览历史
            $ViewHistoryList = [] ;
            if ($ViewHistory) {
                $map['id']  = array('in',$ViewHistory);
                $ViewHistoryList = M('item')->where($map)->field('id,name,bulky_price,price')->select();
                foreach ($ViewHistoryList as $key => $value) {
                   $ViewHistoryList[$key]['path'] = M('item_pic i')->join('onethink_picture p ON p.id = i.picture_id')->where(['i.item_id'=>$value['id'],'i.type'=>2])->order('i.id asc')->getField('p.path');
                }
            }
            $this->assign('ViewHistoryList',$ViewHistoryList);

            array_unshift($ViewHistory,$id);
            $ViewHistory =array_unique($ViewHistory);
            if(count($ViewHistory) >= 10){
                $ViewHistory = array_slice($ViewHistory,0,10);
            }
            cookie('ViewHistory',$ViewHistory);

            $itemM = M('item') ;
            $item_picM = M('item_pic b') ;
            $item_specM = M('item_spec') ;
            $detail = $itemM->where(['id'=>$id,'status'=>1])->find();
            if(empty($detail)) $this->error('页面未找到');
            $detail['item_pic'] = $item_picM->join('onethink_picture p ON p.id = b.picture_id')->where(['b.item_id'=>$id,'b.type'=>2])->field('b.id,p.path')->order('b.sort desc,b.id asc')->select();
            $detail['item_spec'] = $item_specM->where(['item_id'=>$id,'status'=>1])->order('sort desc,id asc')->select();
            // foreach ($detail['item_spec'] as $key => $value) {
            //         $detail['item_spec']['spec'] = explode(",", $value['spec']);
            // }
// var_dump($detail);exit;
            $detail['shareUrl'] = C('WWW').'/Item/detail/id/'.$id.'.html' ;
            $this->assign('data',$detail);


            $count = M('comment')->where(['item_id'=>$id,'status'=>1])->count();
            $page = new \Think\Page($count,10);
            $comment_list =M('comment')->where(['item_id'=>$id,'status'=>1])->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
            $page->setConfig('prev','Prev page');
            $page->setConfig('next','Next page');
            $page->setConfig('suffix','#Review');
            $pageStyle = $page->show();
            $this->assign('_page',$pageStyle);
            $this->assign('comment_list',$comment_list);
     // var_dump($comment_list);exit;

            $this->display();
    }

        /* order */
    public function order(){
                        $uid = session('user_auth')['uid'] ;
                        if(IS_POST){
                        $data = I('post.');
                        $orderInfo = S($data['orderno']);
                        if(empty($orderInfo)) $this->error('Network error');
                        $memberM = M("order");
                        $data['uid'] = $uid ;
                        $data['status'] = 1 ;
                        $data['create_time'] = date('Y-m-d H:i:s',time());
                        $data['item_id'] = $orderInfo['id'] ;
                        $data['name'] = $orderInfo['name'] ;
                        $data['sku'] = $orderInfo['sku'] ;
                        $data['path'] = $orderInfo['path'] ;
                        $data['num'] = $orderInfo['num'] ;
                        $data['unit_price'] = $orderInfo['unit_price'] ;
                        $data['total'] = $orderInfo['total'] ;
                        $data['spec'] = '';
                        foreach ((array)$orderInfo['specList'] as $key => $value) {
                           $data['spec'] .= $value['spec_name'].":".$value['spec'].",";
                        }
                        $data['spec'] = rtrim($data['spec'],",");
                        $res = $memberM->data($data)->add();
                         if($res !== false){ //成功
                                $this->success('Submit successfully！',U('User/inquiry'));
                            } else { //注册失败，显示错误信息
                                $this->error($this->showRegError($uid));
                        }
                        }else{
                                $this->get_page_info(1);
                                $this->assign('fixed',1);//

                                $get = I('get.action');
                                $get =  \Think\Crypt::decrypt($get,'iwill');
                                $get = json_decode($get,ture);
               // var_dump($get);
                                $orderNo =get_order_id();
                                $data  =M('item')->where(['id'=>$get['id']])->field('id,name,stock,price_range,price,bulky_price,sku')->find();
                                if(empty($data)) throw new \Think\Exception('Page not find');
                                $data['path'] = M('item_pic i')->join('onethink_picture p ON p.id = i.picture_id')->where(['i.item_id'=>$get['id'],'i.type'=>2])->order('i.id asc')->getField('p.path');
                                $data['orderNo'] =get_order_id();
                                $data['num'] =(int)$get['num'] ;

                                if(!empty($data['price_range']) && !empty($data['price'])){
                                        $price_range = explode(",", $data['price_range']);
                                        $price = explode(",", $data['price']);
                                        foreach ($price_range as $key => $value) {
                                                $range = explode("-", $value);
                                                if( $data['num'] >= $range[0] && $data['num'] <= $range[1] ){
                                                    $data['unit_price'] =  $price[$key] ;
                                                    break;
                                                }
                                        }
                                }
                               $data['total'] =  $data['num']  *  $data['unit_price']  ;

                                $spec = explode("|", trim($get['spec'],"|"));
                                $specList = [] ;
                                foreach ($spec as $key => $value) {
                                         $arr = explode("-",$value);
                                         $item_spec=  M('item_spec')->where(['id'=>$arr['0'],'item_id'=>$get['id'],'status'=>1])->find();
                                         $specList[$key]['spec_name'] = $item_spec['name'];
                                         $spec = explode(",", $item_spec['spec']);
                                         $specList[$key]['spec'] = $spec[$arr[1]];
                                }
                                $data['specList'] = $specList;
// var_dump($orderNo);exit;
                                $this->assign('data',$data);//
                                S($data['orderNo'],$data,86400);
                                $this->display();
                        }

    }

    public function orderhanlde(){
               $get = I('get.');
               $data =  \Think\Crypt::encrypt(json_encode($get),'iwill');
               $this->redirect('Item/order', array('action' => $data));
    }


        /* more */
    public function more(){
        if(IS_POST){
            $data = I('post.');
            $map['pid']  = $data['id'];
            $map['status']  = 1;
            $result = M('item_category')->where($map)->field('id,name')->select();
            if($result){
                $category = [];
                foreach ($result  as $k=> $v) {
                    $category[] =$v['id'];
                }
            }else{
                $category[] =$data['id'] ;
            }

            $item= [];
            $item_map['category_id']  = array('in',$category);
            $item_map['status']  = 1;
            $item= M('item')->where($item_map)->field('id,name,star,bulky_price')->order('sort desc,id desc')->select();
            $more = 0;
            if($data['more'] == 0){
                    $more = count($item) >4 ? 1 : 0 ;
                    $item = array_slice($item,0,4);
            }
  // var_dump($item);exit;
            $tpl = "";
            foreach ($item as $k => $v) {
                $item[$k]['path'] =M('item_pic b')->join('onethink_picture p ON p.id = b.picture_id')->where(['b.item_id'=>$v['id'],'b.type'=>2])->order('b.id asc')->getField('p.path');
                $tpl .= '<div class="item"><a class="read" href="'.U('detail?id='.$v['id']).'"><div class="pic"><img src="'.$item[$k]['path'].'"></div><div class="icon-star-list clearfix">';
                for ($i=1; $i <= 5 ; $i++) {
                        if($i<=$v['star']){
                                 $tpl .= '<div class="icon-star on"></div>';
                        }else{
                                 $tpl .= '<div class="icon-star"></div>';
                        }
                }
              $tpl .= '</div><div class="name">'.$v['name'].'</div><div class="price"><span></span><span>$'. $v['bulky_price'] .'</span></a></div></div>';
            }
            $this->ajaxReturn([
                'data'=>$tpl,
                'more'=>$more
            ]);
         }
    }

            /* collection */
    public function collection(){
            $uid = session('user_auth')['uid'] ;
                        if(IS_POST){
                        $item_id = I('post.id');
                        $data['uid'] = $uid ;
                        $data['item_id'] = $item_id ;
                        $isCollection = M("collection")->where($data)->count();
                        if($isCollection){ //成功
                                $this->error('You have already collected it');
                        }
                        $data['status'] = 1 ;
                        $data['create_time'] = date('Y-m-d H:i:s',time());
                        $res = M("collection")->data($data)->add();
                         if($res !== false){ //成功
                                $this->success('Collection successfully');
                            } else { //注册失败，显示错误信息
                                $this->error($this->showRegError($uid));
                        }
             }
    }

                /* comment */
    public function comment(){
            $uid = session('user_auth')['uid'] ;
                    if(IS_POST){
                        $data = I('post.');
                        /* 检测验证码 TODO: */
                        if(!check_verify($data['code'])){
                            $this->error('Verification code input error！');
                        } ;
                        $data['uid'] = $uid ;
                        $data['item_id'] = $data['item_id'] ;
                        $data['email'] = $data['email'] ;
                        $data['content'] = $data['content'] ;
                        $data['star'] =$data['star'] ;
                        $data['status'] = 0 ;
                        $data['create_time'] = date('Y-m-d H:i:s',time());
                        $res = M("comment")->data($data)->add();
                         if($res !== false){ //成功
                                $this->success('Comment successfully');
                            } else { //注册失败，显示错误信息
                                $this->error($this->showRegError($uid));
                        }
             }
    }


}