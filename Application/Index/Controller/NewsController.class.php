<?php
namespace Index\Controller;

class NewsController extends IndexBaseController {

    function exceptAuthActions()
    {
        return array(
            "index",
            "newsDetail",
            "imagesList",
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
        $where = "is_open = 1 AND  device_type != 2 ";
//        $where = "is_open = 1 AND  device_type != 1 ";
        $count = M('article') -> where($where)->count();
        $limit = 10;
        $Page = new \Common\Common\Page($count,$limit);
        $list = M('article') -> where($where)->order('publish_time DESC')->limit($Page->firstRow.','.$Page->listRows) -> select();

        $show = $Page -> show();
        $this -> assign('list',$list);
        $this -> assign('page',$show);
        $this -> assign('count',$count);
        $this -> assign('limit',$limit);
        $this -> display();
    }


    public function newsDetail(){
        $id = I('id');
        $where = "is_open = 1 AND  article_id = '".$id."' ";
        $info = M('article') -> where($where)->find();
        if( empty($info) ){
            $this -> error("找不到此文章！");
        }
        $this -> assign('info',$info);
        $this -> display();
    }


    public function imagesList(){
exit;
        $sql = "SELECT o.address ,o.province ,o.consignee,o.city ,o.district, u.nickname,o.add_time,o.mobile,COUNT(o.pay_status ) as aa
FROM  `lm_order` o,  `lm_users` u
WHERE  u.user_id = o.user_id
AND o.pay_status = 1
AND o.add_time <= 1483200000
group by o.user_id HAVING COUNT(o.pay_status)=1  order by o.add_time desc";
        $data1= M()->query($sql);
//        dd($data1);
        $r = get_region_list();
////        dd($r);
        foreach ($data1 as $key =>  $data12){
            $data1[$key]['address'] = $r[$data12['province']]['name'].$r[$data12['city']]['name'].$r[$data12['district']]['name']. $data12['address'];
        }
//        $sql = "SELECT o.address , u.nickname,o.mobile
//FROM  `lm_order` o,  `lm_users` u
//WHERE  u.user_id = o.user_id
//AND u.oauth =  \"DAORU\"
//group by u.user_id order by o.add_time desc";
//        $data2 = M()->query($sql);
//        $data = array_merge($data1,$data2);
        $data=$data1;
//        dd(count($data));
        $strTable ='<table width="500" border="1">';
        $strTable .= '<tr>';
        $strTable .= '<td style="text-align:center;font-size:12px;width:120px;">地址</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="100">姓名</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">电话</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">次数</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">时间</td>';
        $strTable .= '</tr>';
        foreach($data as $k=>$val){
            $strTable .= '<tr>';
            $strTable .= '<td style="text-align:center;font-size:12px;width:120px;">'.$val['address'].'</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="100">'.$val['consignee'].'</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">'.$val['mobile'].'</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="100">'.$val['aa'].'</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">'.date('Y-m-d H:i:s',$val['add_time']).'</td>';
            $strTable .= '</tr>';
        }
        $strTable .='</table>';
        unset($data);
        header("Content-type: application/vnd.ms-excel");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=order_".date('Y-m-d').".xls");
        header('Expires:0');
        header('Pragma:public');
        echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
        exit;
        $this -> display();
    }
}