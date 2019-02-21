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
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class PublicController extends \Think\Controller {

    /**
     * 后台用户登录
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function login($username = null, $password = null, $verify = null){
        if(IS_POST){
            /* 检测验证码 TODO: */
            if(!check_verify($verify)){
                $this->error('验证码输入错误！');
            }

            /* 调用UC登录接口登录 */
            $User = new UserApi;
            $uid = $User->login($username, $password);
            if(0 < $uid){ //UC登录成功
                /* 登录用户 */
                $Member = D('Member');
                if($Member->login($uid)){ //登录用户
                    //TODO:跳转到登录前页面
                    $this->success('登录成功！', U('Index/index'));
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
        } else {
            if(is_login()){
                $this->redirect('Index/index');
            }else{
                /* 读取数据库中的配置 */
                $config	=	S('DB_CONFIG_DATA');
                if(!$config){
                    $config	=	D('Config')->lists();
                    S('DB_CONFIG_DATA',$config);
                }
                C($config); //添加配置

                $this->display();
            }
        }
    }



    public function verify(){
        $config =    array(
                'codeSet'    =>    '123456789',
                'length'      =>    4 ,    // 验证码位数
                'useCurve'    =>    false, // 关闭验证码杂点
         );
        $verify = new \Think\Verify($config );
        $verify->entry(1);
    }

        public function img()
    {
        if (IS_POST) {
            if ($_FILES['file']['error'] === 0) {
                //获得文件扩展名
                $temp_arr = explode(".", $_FILES['file']['name']);
                $file_ext = array_pop($temp_arr);
                $file_ext = strtolower(trim($file_ext));
                //检查扩展名
                if (in_array($file_ext, [ 'jpg', 'jpeg', 'png']) && getimagesize($_FILES['file']['tmp_name'])) {
                    //检查文件大小
                    if( $_FILES['imgFile']['size'] < 2097152 ){
                        $file_path = './'.'Uploads/User/'.date('Y-m-d') ;
                        if ( !is_dir($file_path) ) {
                            mkdir($file_path );
                        }
                        $file_name = uniqid().'.'.$file_ext ;
                        if(move_uploaded_file($_FILES['file']['tmp_name'], $file_path.DIRECTORY_SEPARATOR.$file_name ) === false){
                               $data['msg'] = 'Upload failure';
                               $data['status'] = 0 ;
                        }else{
                              $data['msg'] = '';
                              $data['status'] = 1 ;
                              $data['data'] = '/Uploads/User/'.date('Y-m-d').'/'.$file_name ;
                        }
                    }else{
                         $data['msg'] = 'Please upload the internal picture within 2M';
                         $data['status'] = 0 ;
                    }
                }else{
                     $data['msg'] = 'Please upload pictures in jpg, jpeg, png format';
                     $data['status'] = 0 ;
                }
             }else{
                 $data['msg'] = 'Upload failure';
                 $data['status'] = 0 ;
            }
        }else{
                $data['msg'] = 'False request';
                $data['status'] = 0 ;
        }
        $this->ajaxReturn($data);
    }


}
