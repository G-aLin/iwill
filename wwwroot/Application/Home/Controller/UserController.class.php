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
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserController extends HomeController {

	/* 用户中心首页 */
	public function index(){
                        $this->check_login();
                        $uid = session('user_auth')['uid'] ;
                        if(IS_POST){
                        $data = I('post.');
                        $memberM = M("member");
                        $Sqldata =[
                                'uid'=>$uid,
                                'sex'=>$data['sex'],
                                'phone'=>$data['phone'],
                                'birthday'=>$data['birthday'],
                                'country'=>$data['country'],
                                'head_icon'=>$data['head_icon'],
                                'first_name'=>$data['first_name'],
                                'last_name'=>$data['last_name'],
                                'zip_code'=>$data['zip_code'],
                                'city'=>$data['city'],
                                'state'=>$data['state'],
                                'address_line1'=>$data['address_line1'],
                                'address_line2'=>$data['address_line2'],
                            ];
                        $res = $memberM->data($Sqldata)->save();
                            if($res !== false){ //成功
                                $this->success('Submit successfully！',U('index'));
                            } else { //注册失败，显示错误信息
                                $this->error($this->showRegError($uid));
                            }
                        }else{
                                $this->get_page_info(1);
                                $this->assign('fixed',1);//
                                $data  =M('member')->where(['uid'=>$uid])->find();
                                $this->assign('data',$data);//
                                $country  =M('country')->where(['status'=>1])->order('level desc')->select();
                                $this->assign('country',$country);//
                                $this->display();
                        }

	}

	/* 注册页面 */
	public function register($username = '', $password = '', $repassword = '', $email = '', $verify = ''){
                            if( session('user_auth')['uid']){
                                  $url = U('User/index');
                                  header("Location: $url");
                                  exit;
                            }
		if(IS_POST){ //注册用户
                                     /* 检测 */
                                        if(empty($username)){
                                            $this->error('Account name is empty');
                                        }
                                         $mode = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
                                          if(!preg_match($mode,$username)){
                                            $this->error('Please enter the correct email address');
                                        }
                                          if(empty($password)){
                                            $this->error('password is empty');
                                        }
			/* 调用注册接口注册用户 */
                                       $User = new UserApi;
                                       $email = '' ;
			$uid = $User->register($username, $password, $email);
			if(0 < $uid){ //注册成功
				//TODO: 发送验证邮件
                                        $user = M('member')->where(['uid'=>$uid])->find();
                                        if(!$user){
                                            $mdata['uid'] = $uid ;
                                            $mdata['nickname'] = $username ;
                                            $mdata['status'] = 1 ;
                                            $mdata['login'] = 1 ;
                                            $mdata['last_login_time'] = time() ;
                                            $mdata['last_login_ip'] = get_client_ip(1) ;
                                            M('member')->data($mdata)->add();
                                        }
                                        send_email_tpl($username,1,['username'=>$username,'password'=>$password]);
				$this->success('successfully！',U('index'));
			} else { //注册失败，显示错误信息
				$this->error($this->showRegError($uid));
			}

		} else { //显示注册表单
                                       $this->assign('register',1);//列表
			$this->display('login');
		}
	}

	/* 登录页面 */
	public function login($username = '', $password = '', $verify = ''){
                              if( session('user_auth')['uid']){
                                  $url = U('User/index');
                                  header("Location: $url");
                                  exit;
                            }
		if(IS_POST){ //登录验证
			/* 检测验证码 */
			  if(empty($username)){
                                            $this->error('Account name is empty');
                                        }
                                           $mode = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
                                          if(!preg_match($mode,$username)){
                                            $this->error('Please enter the correct email address');
                                        }
                                          if(empty($password)){
                                            $this->error('password is empty');
                                        }
			/* 调用UC登录接口登录 */
			$user = new UserApi;
			$uid = $user->login($username, $password);
			if(0 < $uid){ //UC登录成功
				/* 登录用户 */
				$Member = D('Member');
				if($Member->login($uid)){ //登录用户
					//TODO:跳转到登录前页面
					$this->success('successfully！',U('Home/Index/index'));
				} else {
					$this->error($Member->getError());
				}

			} else { //登录失败
				switch($uid) {
					case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
					case -2: $error = '密码错误！'; break;
					default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
				}
                                                    $error ="Incorrect user or password";
				$this->error($error);
			}

		} else { //显示登录表单
			$this->display();
		}
	}

    /* 登录页面 */
    public function login2($username = '', $password = '', $verify = ''){
        echo 22;exit;
        if(IS_POST){ //登录验证
            /* 检测验证码 */
            if(!check_verify($verify)){
                $this->error('验证码输入错误！');
            }

            /* 调用UC登录接口登录 */
            $user = new UserApi;
            $uid = $user->login($username, $password);
            if(0 < $uid){ //UC登录成功
                /* 登录用户 */
                $Member = D('Member');
                if($Member->login($uid)){ //登录用户
                    //TODO:跳转到登录前页面
                    $this->success('登录成功！',U('Home/Index/index'));
                } else {
                    $this->error($Member->getError());
                }

            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }

        } else { //显示登录表单
            $this->display();
        }
    }

	/* 退出登录 */
	public function logout(){
		if(is_login()){
		D('Member')->logout();
                                   $this->redirect('User/login');
		} else {
			$this->redirect('User/login');
		}
	}

	/* 验证码，用于登录和注册 */
	public function verify(){
		$verify = new \Think\Verify();
		$verify->entry(1);
	}

	/**
	 * 获取用户注册错误信息
	 * @param  integer $code 错误编码
	 * @return string        错误信息
	 */
	private function showRegError($code = 0){
		switch ($code) {
// case -1:  $error = '用户名长度必须在16个字符以内！'; break;
// case -2:  $error = '用户名被禁止注册！'; break;
// case -3:  $error = '用户名被占用！'; break;
// case -4:  $error = 'Password length must be between 6 and 30 characters！'; break;
// case -5:  $error = '邮箱格式不正确！'; break;
// case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
// case -7:  $error = '邮箱被禁止注册！'; break;
// case -8:  $error = '邮箱被占用！'; break;
// case -9:  $error = '手机格式不正确！'; break;
// case -10: $error = '手机被禁止注册！'; break;
// case -11: $error = '手机号被占用！'; break;
// default:  $error = '未知错误';
                                     case -1: $error = 'User name length must be less than 16 characters'; break;
			case -2:  $error = 'User name is prohibited from registration！'; break;
			case -3:  $error = 'User name occupied！'; break;
			case -4:  $error = 'Password length must be between 6 and 30 characters！'; break;
			default:  $error = 'Network anomaly';
		}
		return $error;
	}


    /**
     * 修改密码提交
     * @author huajie <banhuajie@163.com>
     */
    public function Changepassword(){
        if ( !is_login() ) {
		$this->error( '您还没有登陆',U('User/login') );
}
        if ( IS_POST ) {
            //获取参数
            $uid        =   is_login();
            $password   =   I('post.old');
            $repassword = I('post.repassword');
            $data['password'] = I('post.password');
            empty($password) && $this->error('Please enter the original password');
            empty($data['password']) && $this->error('Please enter the new password');
            empty($repassword) && $this->error('Please enter the confirm password');

            if($data['password'] !== $repassword){
                $this->error(' new password does not match the confirmation password ');
            }

            $Api = new UserApi();
            $res = $Api->updateInfo($uid, $password, $data);
            if($res['status']){
                $this->success('修改密码成功！');
            }else{
                $this->error($res['info']);
            }
        }else{
            $this->display();
        }
    }

        /* 用户 */
    public function collection(){
         $this->check_login();
                        $uid = session('user_auth')['uid'] ;
                        if(IS_POST){
                        $data = I('post.');
                        $memberM = M("member");
                        $Sqldata =[
                                'uid'=>$uid,
                                'sex'=>$data['sex'],
                                'phone'=>$data['phone'],
                                'birthday'=>$data['birthday'],
                                'country'=>$data['country'],
                            ];
                        $res = $memberM->data($Sqldata)->save();
                            if($res !== false){ //成功
                                $this->success('Submit successfully！',U('index'));
                            } else { //注册失败，显示错误信息
                                $this->error($this->showRegError($uid));
                            }
                        }else{
                                $this->get_page_info(1);
                                $this->assign('fixed',1);//

                                $count = M('collection')->where(['uid'=>$uid,'status'=>1])->count();
                                $page = new \Think\Page($count,12);
                                $data =M('collection')->where(['uid'=>$uid,'status'=>1])->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
                                $page->setConfig('prev','Prev page');
                                $page->setConfig('next','Next page');
                                $pageStyle = $page->show();
                                $this->assign('_page',$pageStyle);
                                $this->assign('data',$data);

                                $this->display();
                        }

    }

            /* 用户中心首页 */
    public function comment(){
                $uid = session('user_auth')['uid'] ;
                $this->get_page_info(1);
                $this->assign('fixed',1);//

                $count = M('comment')->where(['uid'=>$uid])->count();
                $page = new \Think\Page($count,8);
                $data =M('comment')->where(['uid'=>$uid])->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
                $page->setConfig('prev','Prev page');
                $page->setConfig('next','Next page');
                $pageStyle = $page->show();
 // var_dump($data);exit;
                $this->assign('_page',$pageStyle);
                $this->assign('data',$data);
                $this->display();
    }

                /* 用户 */
    public function inquiry(){
                    $uid = session('user_auth')['uid'] ;
                    $this->get_page_info(1);
                    $this->assign('fixed',1);//

                    $count = M('order')->where(['uid'=>$uid,'status'=>1])->count();
                    $page = new \Think\Page($count,8);
                    $data =M('order')->where(['uid'=>$uid,'status'=>1])->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
                    $page->setConfig('prev','Prev page');
                    $page->setConfig('next','Next page');
                    $pageStyle = $page->show();
                    // var_dump($data);exit;
                    $this->assign('_page',$pageStyle);
                    $this->assign('data',$data);
                    $this->display();
    }

                    /* 用户 */
    public function inquirydetail(){
            $uid = session('user_auth')['uid'] ;
            $this->get_page_info(1);
            $this->assign('fixed',1);
            $id   =   I('get.id');
            $data  =M('order')->where(['id'=>$id,'status'=>1])->find();
            M('order_message')->where(['uid'=>$uid,'order_id'=>$id])->save(['is_read'=>1]);
            $this->assign('data',$data);//
            $list = M('order_message')->where(['uid'=>$uid,'order_id'=>$id])->order('id desc')->select();
            $this->assign('list', $list);
            $this->display();
    }

                /* 用户cancelcollection页 */
    public function cancelcollection(){
                        $uid = session('user_auth')['uid'] ;
                        if($uid && IS_POST){
                        $ids = trim(I('post.ids'),",");
                        $map['uid']  = $uid;
                        $map['id']  = array('in',$ids);
                        $res = M('collection')->where($map)->delete();
                            if($res !== false){ //成功
                                $this->success('Submit successfully！',U('index'));
                            } else { //注册失败，显示错误信息
                                $this->error($this->showRegError($uid));
                            }
                        }
    }

}
