<?php
namespace Admin\Controller;


class ProblemController extends BaseController {

    public $pro = null;

	function __construct(){
		parent::__construct();
		$this->pro = M('problem');
	}
    public function index(){
        $where = array();
        $p = I('_REQUEST.p',1);
        $size = I('_REQUEST.size',10);
        $count = $this->pro->where($where)->count();// 查询满足要求的总记录数
        if($count>0){
        	$list = $this->pro->order('pro_rank desc')->page("$p,$size")->select();
        	$pager = new \Think\Page($count,$size);// 实例化分页类 传入总记录数和每页显示的记录数
        	$page = $pager->show();//分页显示输出
        }
        $this -> assign('list',$list);
        $this -> assign('page',$page);// 赋值分页输出
        $this -> display();
    }

    //新增
    public function add(){
    	if(IS_GET){ //编辑
    		$where['pro_id'] = I('id');
    		$edit_list = $this->pro->where($where)->find();
    		$this -> assign('edit_list',$edit_list);
    	}
    	$this -> display();
    }

    public function save(){
    	if(IS_POST){
    		$id = I('pro_id');
    		$data = I('post.');
    		if($id==0){
    			$data['pro_time'] = time();
    			$res = $this->pro->add($data);
    			$res ? $this->success('添加成功',U('Admin/Problem/index')) : $this->error('添加失败');exit;
    		}else{
    			$res = $this->pro->save($data);
    			$res ? $this->success('修改成功',U('Admin/Problem/index')) : $this->error('修改失败');exit;
    		}
    	}
    }

    //删除
   	public function del(){
   		$id = rtrim(I('pro_id'),",");
   		$result = $this->pro->where(array('pro_id'=>array('in',$id)))->delete();
   		if($result){
			echo 1;exit;
		}else{
			echo 2;exit;
		}
   	}
   
    	    

}