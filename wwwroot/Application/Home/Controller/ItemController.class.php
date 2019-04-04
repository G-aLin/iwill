<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
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
                            $data[$key]['item'] = M('item')->where($item_map)->field('id,name,star,bulky_price,rollover_img,price,price_range')->order('sort desc,id desc')->limit(8)->select();
                            foreach ($data[$key]['item'] as $k => $v) {
                                $data[$key]['item'][$k]['path'] =M('item_pic b')->join('onethink_picture p ON p.id = b.picture_id')->where(['b.item_id'=>$v['id'],'b.type'=>2])->order('b.id asc')->getField('p.path');
                            }
                        }
                    }
     // var_dump($data[0]) ;exit;
                   $this->assign('data',$data);//
                   $this->assign('t',I('get.t'));//
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
            if(empty($detail)) $this->error('404 Page Not Found');
            $detail['item_pic'] = $item_picM->join('onethink_picture p ON p.id = b.picture_id')->where(['b.item_id'=>$id,'b.type'=>2])->field('b.id,p.path')->order('b.sort desc,b.id asc')->select();
            $detail['item_spec'] = $item_specM->where(['item_id'=>$id,'status'=>1])->order('sort desc,id asc')->select();
            // foreach ($detail['item_spec'] as $key => $value) {
            //         $detail['item_spec']['spec'] = explode(",", $value['spec']);
            // }
// var_dump($detail);exit;
            $detail['shareUrl'] = C('WWW').'/Item/detail/id/'.$id.'.html' ;
            $this->assign('data',$detail);

            if($detail['pid'] !=0){
                 $spec =$itemM->where(['pid'=>$detail['pid']])->field('id,spec,spec_name')->select();
                $specParent =$itemM->where(['id'=>$detail['pid']])->field('id,spec,spec_name')->find();
            }else{
                 $spec =$itemM->where(['pid'=>$detail['id']])->field('id,spec,spec_name')->select();
                $specParent =$itemM->where(['id'=>$detail['id']])->field('id,spec,spec_name')->find();
            }
            array_unshift($spec , $specParent);
             $this->assign('spec',$spec);

            $ids = array_column($spec, 'id');
            //评论
            $pMap['item_id']  = array('in',$ids);
            $pMap['status']  = 1;
            $count = M('comment')->where($pMap)->count();
            $page = new \Think\Page($count,10);
            $comment_list =M('comment')->where($pMap)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
            $page->setConfig('prev','Prev page');
            $page->setConfig('next','Next page');
            $page->setConfig('suffix','#Review');
            $pageStyle = $page->show();
            foreach ($comment_list as $key => $value) {
                $publish_time = strtotime($value['create_time']);
                $comment_list[$key]['year'] = date('Y',$publish_time);
                $comment_list[$key]['month'] =date('F',$publish_time);
                $comment_list[$key]['day'] = date('d',$publish_time);
            }
            $this->assign('_page',$pageStyle);
            $this->assign('comment_list',$comment_list);
            $this->assign('comment_count',$count);
     // var_dump($comment_list);exit;
             // 问答
            $pMap['type']  = 1;
            $question_count = M('item_question')->where($pMap)->count();
            $question_page = new \Think\Pagetwo($question_count,10);
            $question_list =M('item_question')->where($pMap)->order('id desc')->limit($question_page->firstRow.','.$question_page->listRows)->select();
            $question_page->setConfig('prev','Prev page');
            $question_page->setConfig('next','Next page');
            $question_page->setConfig('p','page');
            $question_page->setConfig('suffix','#FAQ');
            $question_pageStyle = $question_page->show();
            foreach ($question_list as $key => $value) {
                $question_list[$key]['username']    =   M('ucenter_member')->where(['id'=>$value['uid']])->getField('username');
                $publish_time = strtotime($value['create_time']);
                $question_list[$key]['year'] = date('Y',$publish_time);
                $question_list[$key]['month'] =date('F',$publish_time);
                $question_list[$key]['day'] = date('d',$publish_time);
                $question_list[$key]['reply']  = M('item_question')->where(['item_id'=>$id,'type'=>2,'reply_id'=>$value['id'],'status'=>1])->getField('content');
            }
            $this->assign('_question_page',$question_pageStyle);
            $this->assign('question_list',$question_list);
     // var_dump($question_list);exit;
            $uid = session('user_auth')['uid'] ;
            $already = 0;
            if($uid){
                $isCollection = M('collection')->where(['uid'=>$uid,'item_id'=>$id,'status'=>1])->count();
                $already  = $isCollection ? 1 :  0;
            }
            $this->assign('already',$already);

            $buyon = M('item_buyon')->where(['item_id'=>$id,'status'=>1])->order('sort desc,id desc')->select();
            $this->assign('buyon',$buyon);
            $this->display();
    }

         public function desc(){
            $id   =   I('get.id');
            $itemM = M('item') ;
            $detail = $itemM->where(['id'=>$id,'status'=>1])->find();
            $this->assign('data',$detail);
            $this->display();
    }

        /* order */
    public function order(){
                        $uid = session('user_auth')['uid'] ;
                        if(IS_POST){
                        $data = I('post.');
                        $orderInfo = S($data['orderno']);
                        if(empty($orderInfo)) $this->error('Network error');
                        if(empty($data['first_name'])) $this->error('Please input First Name');
                        if(empty($data['last_name'])) $this->error('Please input Last Name');
                        if(empty($data['country'])) $this->error('Please input Country');
                        if(empty($data['zip_code'])) $this->error('Please input Zip Code');
                        if(empty($data['city'])) $this->error('Please input City');
                        if(empty($data['state'])) $this->error('Please input State/Territory');
                        if(empty($data['address_line1'])) $this->error('Please input Address Line1');
                        if(empty($data['address_line2'])) $this->error('Please input Address Line2');
                        if(empty($data['phone'])) $this->error('Please input Phone Number');
                        if(empty($data['email'])) $this->error('Please input email');
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
                        $data['subtotal'] = $orderInfo['subtotal'] ;
                        $data['shipping'] = $orderInfo['shipping'] ;
                        $data['taxex'] = $orderInfo['taxex'] ;
                        $data['total'] = $orderInfo['total'] ;
                        $data['spec'] = '';
                        foreach ((array)$orderInfo['specList'] as $key => $value) {
                           $data['spec'] .= $value['spec_name'].":".$value['spec'].",";
                        }
                        $data['spec'] = rtrim($data['spec'],",");
                        $res = $memberM->data($data)->add();
                         if($res !== false){ //成功
                                 send_email_tpl($data['email'],2);
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

                                $orderNo =get_order_id();
                                $data  =M('item')->where(['id'=>$get['id']])->field('id,name,stock,price_range,price,bulky_price,sku,shipping,taxex')->find();
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
                               $data['shipping']  = round($data['shipping'] * $data['num'] ,2);
                               $data['subtotal'] =  round($data['num']  *  $data['unit_price'] ,2);
                               $taxex = ($data['shipping']+$data['subtotal']) * $data['taxex'] ;
                               $data['taxex'] =  round($taxex /100,2);
                               $data['total'] =  $data['subtotal']  + $data['shipping']  +$data['taxex'] ;
                                $spec = explode("|", trim($get['spec'],"|"));
                                $specList = [] ;
                                foreach ($spec as $key => $value) {
                                         $arr = explode("-",$value);
                                         $specList[$key]['spec_name'] = $arr['0'];
                                         $specList[$key]['spec'] = $arr['1'];
                                }
                                $data['specList'] = $specList;
                                $this->assign('data',$data);//
                                S($data['orderNo'],$data,86400);
                                $user  =M('member')->where(['uid'=>session('user_auth')['uid']])->find();
                                $this->assign('user',$user);//
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
            $item= M('item')->where($item_map)->field('id,name,star,bulky_price,price,rollover_img')->order('sort desc,id desc')->select();
            $more = 0;
            if($data['more'] == 0){
                    $more = count($item) >8 ? 1 : 0 ;
                    $item = array_slice($item,0,8);
            }
  // var_dump($item);exit;
            $tpl = "";
            foreach ($item as $k => $v) {
                $item[$k]['path'] =M('item_pic b')->join('onethink_picture p ON p.id = b.picture_id')->where(['b.item_id'=>$v['id'],'b.type'=>2])->order('b.id asc')->getField('p.path');
                $tpl .= '<li><a class="read" href="'.U('detail?id='.$v['id']).'"><div class="pic"><img src="'.$item[$k]['path'].'"><img src="'.($item[$k]['rollover_img'] ? get_picture($item[$k]['rollover_img']) : $item[$k]['path']).'"></div><div class="star-list clearfix">';
                for ($i=1; $i <= 5 ; $i++) {
                        if($i<=$v['star']){
                                 $tpl .= '<i class="star on"></i>';
                        }else{
                                 $tpl .= '<i class="star"></i>';
                        }
                }
                if(session('user_auth')['uid']){
                     $price = explode(",",$v['price']);
                      if(count($price) == 1){
                            $item_price =  $price[0];
                      }else{
                          $item_price =  end($price).'-'.$price[0];
                      }
                }else{
                    $item_price = $v['bulky_price'];
                }
              $tpl .= '</div><div class="name" title="'.$v['name'].'">'.$v['name'].'</div><div class="price"><span></span><span>$'. $item_price .'</span></a></div>
            </li>';
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
                           if(empty($data['name'])){
                            $this->error('Theme is empty！');
                        } ;
                           if(empty($data['email'])){
                            $this->error('Username is empty！');
                        } ;
                           if(empty($data['content'])){
                            $this->error('Content is empty！');
                        } ;
                        if(empty($data['code'])){
                            $this->error('Verification code input error！');
                        } ;
                        $data['uid'] = $uid ;
                        $data['item_id'] = $data['item_id'] ;
                        $data['name'] = $data['name'] ;
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

                    /* ask */
    public function ask(){
            $uid = session('user_auth')['uid'] ;
                    if(IS_POST){
                        $data = I('post.');
                        /* 检测验证码 TODO: */
                        $data['uid'] = $uid ;
                        $data['item_id'] = $data['item_id'] ;
                        $data['type'] = 1;
                        $data['content'] = $data['content'] ;
                        $data['status'] = 0 ;
                        $data['create_time'] = date('Y-m-d H:i:s',time());
                        $res = M("item_question")->data($data)->add();
                         if($res !== false){ //成功
                                $this->success('Submit successfully');
                            } else { //注册失败，显示错误信息
                                $this->error($this->showRegError($uid));
                        }
             }
    }


}