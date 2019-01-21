<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use Think\Page;

/**
 * 后台I商品列表控制器
 * @author huajie <banhuajie@163.com>
 */
class ItemController extends AdminController {

    /* 保存允许访问的公共方法 */
    static protected $allow = array( 'draftbox','mydocument');

       private $cate_id        =   null; //文档分类id
    /**
     * 显示左边菜单，进行权限控制
     * @author huajie <banhuajie@163.com>
     */
    protected function getMenu(){
        //获取动态分类
        $cate_auth  =   AuthGroupModel::getAuthCategories(UID); //获取当前用户所有的内容权限节点
        $cate_auth  =   $cate_auth == null ? array() : $cate_auth;
        $cate       =   M('Category')->where(array('status'=>1))->field('id,title,pid,allow_publish')->order('pid,sort')->select();

        //没有权限的分类则不显示
        if(!IS_ROOT){
            foreach ($cate as $key=>$value){
                if(!in_array($value['id'], $cate_auth)){
                    unset($cate[$key]);
                }
            }
        }

        $cate           =   list_to_tree($cate);    //生成分类树

        //获取分类id
        $cate_id        =   I('param.cate_id');
        $this->cate_id  =   $cate_id;

        //是否展开分类
        $hide_cate = false;
        if(ACTION_NAME != 'recycle' && ACTION_NAME != 'draftbox' && ACTION_NAME != 'mydocument'){
            $hide_cate  =   true;
        }

        //生成每个分类的url
        foreach ($cate as $key=>&$value){
            $value['url']   =   'Article/index?cate_id='.$value['id'];
            if($cate_id == $value['id'] && $hide_cate){
                $value['current'] = true;
            }else{
                $value['current'] = false;
            }
            if(!empty($value['_child'])){
                $is_child = false;
                foreach ($value['_child'] as $ka=>&$va){
                    $va['url']      =   'Article/index?cate_id='.$va['id'];
                    if(!empty($va['_child'])){
                        foreach ($va['_child'] as $k=>&$v){
                            $v['url']   =   'Article/index?cate_id='.$v['id'];
                            $v['pid']   =   $va['id'];
                            $is_child = $v['id'] == $cate_id ? true : false;
                        }
                    }
                    //展开子分类的父分类
                    if($va['id'] == $cate_id || $is_child){
                        $is_child = false;
                        if($hide_cate){
                            $value['current']   =   true;
                            $va['current']      =   true;
                        }else{
                            $value['current']   =   false;
                            $va['current']      =   false;
                        }
                    }else{
                        $va['current']      =   false;
                    }
                }
            }
        }
        $this->assign('nodes',      $cate);
        $this->assign('cate_id',    $this->cate_id);
        //获取面包屑信息
        $nav = get_parent_category($cate_id);
        $this->assign('rightNav',   $nav);

        //获取回收站权限
        $this->assign('show_recycle', IS_ROOT || $this->checkRule('Admin/article/recycle'));
        //获取草稿箱权限
        $this->assign('show_draftbox', C('OPEN_DRAFTBOX'));
        //获取审核列表权限
        $this->assign('show_examine', IS_ROOT || $this->checkRule('Admin/article/examine'));
    }

    /**
     * 列表页
     */
    public function index(){
         $this->getMenu();
        /* 查询条件初始化 */
        $map = array();
        if(isset($_GET['name'])){
            $map['name']    =   array('like', '%'.(string)I('name').'%');
        }
        if(!empty($_GET['type'])){
            $type = $_GET['type'] ;
            $map['_string']    =   "FIND_IN_SET($type, commend)";
        }
        $list = $this->lists('Item', $map,'id desc');
        $this->assign('list', $list);
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();
    }

    /**
     * 规格列表页
     */
    public function spec(){
         $this->getMenu();
        /* 查询条件初始化 */
        $map = array();
        $map['item_id']    =   I('get.id');
        $list = $this->lists('item_spec', $map,'sort,id');
        $this->assign('list', $list);
        $this->assign('id', I('get.id'));
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();
    }

    /**
     * 默认文档列表方法
     * @param integer $cate_id 分类id
     * @param integer $model_id 模型id
     * @param integer $position 推荐标志
     * @param mixed $field 字段列表
     * @param integer $group_id 分组id
     */
    protected function getDocumentList($cate_id=0,$model_id=null,$position=null,$field=true,$group_id=null){
        /* 查询条件初始化 */
        $map = array();
        if(isset($_GET['title'])){
            $map['title']  = array('like', '%'.(string)I('title').'%');
        }
        if(isset($_GET['status'])){
            $map['status'] = I('status');
            $status = $map['status'];
        }else{
            $status = null;
            $map['status'] = array('in', '0,1,2');
        }
        if ( isset($_GET['time-start']) ) {
            $map['update_time'][] = array('egt',strtotime(I('time-start')));
        }
        if ( isset($_GET['time-end']) ) {
            $map['update_time'][] = array('elt',24*60*60 + strtotime(I('time-end')));
        }
        if ( isset($_GET['nickname']) ) {
            $map['uid'] = M('Member')->where(array('nickname'=>I('nickname')))->getField('uid');
        }

        // 构建列表数据
        $Document = M('Document');

        if($cate_id){
            $map['category_id'] =   $cate_id;
        }
        $map['pid']         =   I('pid',0);
        if($map['pid']){ // 子文档列表忽略分类
            unset($map['category_id']);
        }
        $Document->alias('DOCUMENT');
        if(!is_null($model_id)){
            $map['model_id']    =   $model_id;
            if(is_array($field) && array_diff($Document->getDbFields(),$field)){
                $modelName  =   M('Model')->getFieldById($model_id,'name');
                $Document->join('__DOCUMENT_'.strtoupper($modelName).'__ '.$modelName.' ON DOCUMENT.id='.$modelName.'.id');
                $key = array_search('id',$field);
                if(false  !== $key){
                    unset($field[$key]);
                    $field[] = 'DOCUMENT.id';
                }
            }
        }
        if(!is_null($position)){
            $map[] = "position & {$position} = {$position}";
        }
		if(!is_null($group_id)){
			$map['group_id']	=	$group_id;
		}
        $list = $this->lists($Document,$map,'level DESC,DOCUMENT.id DESC',$field);

        if($map['pid']){
            // 获取上级文档
            $article    =   $Document->field('id,title,type')->find($map['pid']);
            $this->assign('article',$article);
        }
        //检查该分类是否允许发布内容
        $allow_publish  =   get_category($cate_id, 'allow_publish');

        $this->assign('status', $status);
        $this->assign('allow',  $allow_publish);
        $this->assign('pid',    $map['pid']);

        $this->meta_title = '文档列表';
        return $list;
    }

    public function show($model=''){
      switch (I('get.location')) {
        case '1':
          $data['a'] = I('get.a')  == 0  ? 1 : '';
          $data['b'] = I('get.b')  == 1  ? 2 : '';
          break;
         case '2':
          $data['a'] = I('get.a') == 1  ? 1 : '';
          $data['b'] = I('get.b') == 0  ? 1 : '';
          break;
        default:
          exit;
          break;
      }
      $commend = join(",",$data);
      $commend = trim($commend,",");
      $this->editRow('item', array('commend'=>$commend), array('id'=>I('get.id')));
    }


    /**
     * 设置一条或者多条数据的状态
     * @author huajie <banhuajie@163.com>
     */
    public function setStatus($model=''){
      $status = I('get.status');
      $status = $status == 1 ? 0 : 1;
      $this->editRow('item', array('status'=>$status), array('id'=>I('get.id')));
    }

    /**
     * 设置一条或者多条数据的状态
     * @author huajie <banhuajie@163.com>
     */
    public function setSpec_status($model=''){
      $status = I('get.status');
      $status = $status == 1 ? 0 : 1;
      $this->editRow('item_spec', array('status'=>$status), array('id'=>I('get.id')));
    }

     /**
     * 删除一条或者多条数据的状态
     * @author huajie <banhuajie@163.com>
     */
    public function delete(){
      $item = M('item');
      $item->delete(I('get.id'));
      $this->success('删除成功', Cookie('__forward__'),ture);
    }

        public function spec_delete(){
      $item = M('item_spec');
      $item->delete(I('get.id'));
      $this->success('删除成功', Cookie('__forward__'),ture);
    }

    /**
     * 文档新增页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function add(){
        //获取左边菜单
        $this->getMenu();
        $map['pid']  = array('gt',0);
        $map['status']  = 1;
        $type = M('item_category')->where($map)->select();
        $this->assign('type', $type);

        $this->display();
    }

        public function spec_add(){
        //获取左边菜单
        $this->getMenu();
        $this->assign('id', I('get.id'));
        $this->display();
    }

    /**
     * 文档编辑页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function edit(){
        //获取左边菜单
        $this->getMenu();
        $id     =   I('get.id','');
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        // 获取详细数据
        $data  =M('item')->where(['id'=>$id])->find();
        $data['commendArr'] = explode(',',$data['commend']);
        $Model = M('item_pic i');
        $picData = $Model->join('onethink_picture p ON p.id = i.picture_id')->where(['i.item_id'=>$id])->select();
        //获取当前分类的文档类型
        $this->assign('data', $data);
        $this->assign('picData', $picData);
        $map['pid']  = array('gt',0);
        $map['status']  = 1;
        $type = M('item_category')->where($map)->select();
        $this->assign('type', $type);
        $this->display();
    }

    /**
     */
    public function spec_edit(){
        //获取左边菜单
        $this->getMenu();
        $id     =   I('get.id','');
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        // 获取详细数据
        $data  =M('item_spec')->where(['id'=>$id])->find();
        $spec = explode(',',$data['spec']);
        $spec_img = explode(',',$data['spec_img']);
        $img = [];
         foreach ($spec_img as $key => $value) {
            $img[] = M('picture')->where(['id'=>$value ])->getField('path');
         }
        //获取当前分类的文档类型
        $this->assign('data', $data);
        $this->assign('spec', $spec);
        $this->assign('spec_img', $spec_img);
        $this->assign('img', $img);
        $this->display();
    }

    /**
     * 更新一条数据
     * @author huajie <banhuajie@163.com>
     */
    public function update(){
        $data = I('post.');
        if(empty($data['name'])){
             $this->error('请输入商品名称');
        }
        $Item = M("item");
        $ItemPic = M("item_pic");
        $commend = join(",",$data['position']);
        $Sqldata =[
                'name'=>$data['name'],
                'category_id'=>$data['category_id'],
                'stock'=>$data['stock'],
                'amazon'=>$data['amazon'],
                'jd'=>$data['jd'],
                'taobao'=>$data['taobao'],
                'star'=>$data['star'],
                'sku'=>$data['sku'],
                'status'=>$data['status'],
                'bulky_price'=>$data['bulky_price'],
                'desc'=>$data['content'],
                'description'=>$data['description'],
                'packaging_and_shipping'=>$data['packaging_and_shipping'],
                'price_range'=>$data['price_range'],
                'price'=>$data['price'],
                'sort'=>$data['sort'],
                'commend'=>$commend,
                'create_time'=>time()
            ];
        if($data['id']){
            $Sqldata['id'] = $data['id'];
            $Item->data($Sqldata)->save();
            $item_id = $data['id'] ;
        }else{
            $item_id = $Item->data($Sqldata)->add();
        }
       if(empty($item_id)) {
            $this->error('网络异常，提交失败');
       }

      $ItemPic->where(['item_id'=>$item_id])->delete();
       if($data['picture_1']){
            $ItemPic->data([ 'item_id'=>$item_id,'type'=>1,'picture_id'=>$data['picture_1'], 'create_time'=>time()])->add();
       }
    $dataList = [] ;
     if($data['picture_2']){
         $dataList[] = array('item_id'=>$item_id,'type'=>'2', 'picture_id'=>$data['picture_2'],'create_time'=>time());
     }
     if($data['picture_3']){
         $dataList[] = array('item_id'=>$item_id,'type'=>'2', 'picture_id'=>$data['picture_3'],'create_time'=>time());
     }
     if($data['picture_4']){
         $dataList[] = array('item_id'=>$item_id,'type'=>'2', 'picture_id'=>$data['picture_4'],'create_time'=>time());
     }
     if($data['picture_5']){
         $dataList[] = array('item_id'=>$item_id,'type'=>'2', 'picture_id'=>$data['picture_5'],'create_time'=>time());
     }
     if($data['picture_6']){
         $dataList[] = array('item_id'=>$item_id,'type'=>'2', 'picture_id'=>$data['picture_6'],'create_time'=>time());
     }
      if($data['picture_7']){
         $dataList[] = array('item_id'=>$item_id,'type'=>'2', 'picture_id'=>$data['picture_7'],'create_time'=>time());
     }
      if($dataList){
         $ItemPic->addAll($dataList);
     }
        $this->success('提交成功', Cookie('__forward__'));
    }

   public function spec_update(){
        $data = I('post.');
        if(empty($data['name'])){
             $this->error('请输入规格名称');
        }
      $Item = M("item_spec");
      $data['spec'] = array_filter($data['spec']);
      $spec_img = [];
       foreach ($data['spec']  as $key => $value) {
          $img_key = "picture_".$key;
          $spec_img[] =   $data[$img_key];
       }
        $data['spec'] = join(",",$data['spec']);
        $data['spec_img'] = join(",",$spec_img);
        $data['create_time'] = time();
        if($data['id']){
            $res = $Item->data($data)->save();
        }else{
            $res = $Item->data($data)->add();
        }
       if(empty($res)) {
            $this->error('网络异常，提交失败');
       }
        $this->success('提交成功', Cookie('__forward__'));
    }



}