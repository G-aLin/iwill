<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		// $this->redirect('Index/index');
	}


    protected function _initialize(){
        if(is_mobile()){
              $url = C('WAP');
              header("Location: $url");
              exit;
        }
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置
        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
        $this->get_shop_nav();
        $this->get_contact_info();
        $this->get_unread();
    }

	/* 用户登录检测 */
	protected function login(){
                        // session('user_auth'); array uid username
		/* 用户登录检测 */
		is_login() || $this->error('您还没有登录，请先登录！', U('User/login'));
	}

    protected function check_login(){
        if(empty(session('user_auth')['uid'])){
              $url = U('User/login');
              header("Location: $url");
              exit;
        }
    }


    protected function get_page_info($id){
        $info =get_page_info($id);
        $this->assign('info',$info);//栏目
    }

       protected function get_contact_info(){
        $contact = M('contact')->find();
        $this->assign('contact',$contact);//
    }

         protected function get_unread(){
            if(!empty(session('user_auth')['uid'])){
                $unreadMsgCount = M('order_message')->where(['uid'=>session('user_auth')['uid'],'is_read'=>0])->count();
                $this->assign('unreadMsgCount',$unreadMsgCount);//
            }
    }

        protected function get_shop_nav(){
        $item_categoryM =   M('item_category') ;
        $shopNav = $item_categoryM->where(['status'=>1,'pid'=>0])->order('level desc,id asc')->select();
        foreach ($shopNav as $key => $value) {
           $shopNav[$key]['children'] = $item_categoryM->where(['status'=>1,'pid'=>$value['id']])->order('level desc,id asc')->select();
        }
        $this->assign('shopNav',$shopNav);//
    }

}
