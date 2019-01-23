<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
     'WWW' => 'http://www.iwill.com',
    'DEFAULT_GROUP'      =>'Home',
     'APP_SUB_DOMAIN_DEPLOY'=>1,
     'APP_SUB_DOMAIN_RULES'=>array(
        'm'=>array('Mobile/'),  // m域名指向Mobile分组
      ),

    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
    'DEFAULT_MODULE'     => 'Home',
    'MODULE_DENY_LIST'   => array('Common','User','Admin','Mobile'),
    'MODULE_ALLOW_LIST'  => array('Home','Admin'),

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => 'P(_qC02Xh<N5liH;fyvm+`r?@QauORe:s>M[~1Y&', //默认数据加密KEY
    'DATA_CACHE_KEY'=> 'P(_qC02Xh<N5liH;fyvm+`r?@QauORe:s>M[~1Y&',

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 2, //URL模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数

    'LANG_SWITCH_ON' => true,   // 开启语言包功能
    'LANG_AUTO_DETECT' => fasel, // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST'        => 'zh-cn,en-us', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE'     => 'lang', // 默认语言切换变量
    'DEFAULT_LANG' => 'en-us', // 默认语言

    /* 数据库配置 */
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '192.168.1.120', // 服务器地址
    'DB_NAME'   => 'onethink', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => '',  // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'onethink_', // 数据库表前缀

    /* 文档模型配置 (文档模型核心配置，请勿更改) */
    'DOCUMENT_MODEL_TYPE' => array(2 => '主题', 1 => '目录', 3 => '段落'),

     /* 发邮件配置 */
        'MAIL_ADDRESS'=>'q128402131@126.com', // 邮箱地址
        'MAIL_SMTP'=>'smtp.126.com', // 邮箱SMTP服务器
        'MAIL_LOGINNAME'=>'', // 邮箱登录帐号
        'MAIL_PASSWORD'=>'', // 邮箱密码
        'MAIL_CHARSET'=>'UTF-8',//编码
        'MAIL_AUTH'=>true,//邮箱认证
        'MAIL_HTML'=>true,//true HTML格式 false TXT格式


);
