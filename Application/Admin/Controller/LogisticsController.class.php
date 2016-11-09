<?php
namespace Admin\Controller;


class LogisticsController extends BaseController {



	function __construct(){
		parent::__construct();
		$this->logi = M('logistics');
        $this->region = M('region');
        $province_list = $this->region->where('level = 1')->select();
	}

    //物流配送方式
    public function index(){
        $where = array();
        if( is_supplier() ) {
            $where['admin_id'] = session("admin_id");
        }
        $list = $this->logi -> where($where)->order('log_rank DESC ')->select();
        $this -> assign('list',$list);
        $this -> display();
    }

    //新增
    public function add(){
        $id = I('get.id',0,'int');
        $region_list = include_once 'Application/Common/Conf/express.php'; //快递名称
        if(!empty($id)){
            $where['log_id'] = $id;
            if( is_supplier() ) {
                $where['admin_id'] = session("admin_id");
            }
            $edit_list = $this->logi->where($where)->find();
            $wheres['name'] = $edit_list['log_province'];
            $pro = $this->region->where($wheres)->find();
            $city_list = $this->region->where('parent_id = '.$pro['id'])->select();
            $edit_list['condition'] = unserialize($edit_list['log_condition']);
            // dump($edit_list['condition']);exit;
            $this -> assign('edit_list',$edit_list);
            $this -> assign('city_list',$city_list);
        }
        //省
        $province_list = $this->region->where('level = 1')->select();
        $this -> assign('province_list',$province_list);
        $this -> assign('region_list',$region_list);
    	$this -> display();
    }

    //ajax获取市
    public function ajax_city(){
        $id = I('post.pro_id',0,'int');
        $where['parent_id'] = $id;
        $city_list = $this->region->where($where)->select();
        echo json_encode($city_list);exit;

    }
    public function save(){
        if(IS_POST){
            $id = I('log_id',0,'int');
            $data = I('post.');
            //指定区域邮费
            if(!is_null($data['area'])){
                for($i=0;$i<count($data['area']);$i++){
                    $condition['no_pinkage'][$i]['area'] = $data['area'][$i];
                    $condition['no_pinkage'][$i]['base'] = $data['base'][$i];
                    $condition['no_pinkage'][$i]['money'] = $data['money'][$i];
                    $condition['no_pinkage'][$i]['add_base'] = $data['add_base'][$i];
                    $condition['no_pinkage'][$i]['add_money'] = $data['add_money'][$i];
                }   
            }

            //指定区域包邮
            if(!empty($data['pinkage_area'])){
                for($y=0;$y<count($data['pinkage_area']);$y++){
                    $condition['pinkage'][$y]['pinkage_area'] = $data['pinkage_area'][$y];
                    $condition['pinkage'][$y]['pinkage_mode'] = $data['pinkage_mode'][$y];
                    $condition['pinkage'][$y]['pinkage_bound'] = $data['pinkage_bound'][$y];
                }
            }
            $data['log_condition'] = !empty($condition) ? serialize($condition) : ''; //地区
            $province_list = $this->region->where('level = 1')->select();
            foreach($province_list as $item){
                if($item['id']==$data['log_province']){
                    $data['log_province'] = $item['name'];
                }
            }
            if($id==0){ //新增
                $data['log_time'] = time();
                if( is_supplier() ) {
                    $data['admin_id'] = session("admin_id");
                }
                $res = $this->logi->add($data);
                $res ? $this->success('新增成功',U('Admin/Logistics/index')) : $this->error('新增失败');exit;
            }else{
                $res = $this->logi->save($data);
                $res ? $this->success('修改成功',U('Admin/Logistics/index')) : $this->error('修改失败');exit;
            }
        }
    }

    //删除
    public function del(){
        $id = rtrim(I('log_id'),",");
        $where = array();
        $where['delivery_way'] = array('in',$id);
        if( is_supplier() ) {
            $data['admin_id'] = session("admin_id");
        }
        $isUse = M('goods_id') -> where( $where ) ->count();
        if( $isUse ){
            echo 2;exit;
        }
        unset($where['delivery_way']);
        $where['log_id'] = array('in',$id);
        $result = $this->logi->where($where)->delete();
        if($result){
            echo 1;exit;
        }else{
            echo 2;exit;
        }
    }

    

   
    	    

}