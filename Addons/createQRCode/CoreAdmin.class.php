<?php
@include 'Addons/createQRCode/Function/base.php';

class createQRCodeAdminController
{

    const TB_QR = "addons_createqrcode_qr";
    const TB_LIST = "addons_createqrcode_list";

    public $assignData = array();

    public function __construct()
    {
    }


    //初始页面
    public function index()
    {
        $count = getCountWithCondition(self::TB_QR);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $list = M(self::TB_QR)->limit($Page->firstRow, $Page->listRows)->order(" create_time desc")->select();
        if( !empty($list)){
            foreach ($list as $key =>  $item){
                $condition = array("qr_id" => $item['id']);
                $number =M(self::TB_LIST)->where($condition)->group('openid')->count();
                $list[$key]['userCount'] = intval(count($number));
            }
        }
        $this->assignData['list'] = $list ;
        $this->assignData['page'] = $show;
        return $this->assignData;
    }

    //用户列表
    public function user()
    {
        $id = I("id");
        $condition = array("qr_id" => $id);
        $count =M(self::TB_LIST)->where($condition)->group('openid')->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $lists =M(self::TB_LIST)->where($condition)->limit($Page->firstRow, $Page->listRows)->order(" create_time desc")->group('openid')->select();
        if(!empty($lists)){
            foreach ($lists as $key=>$list){
                $user = findDataWithCondition("users",array('openid'=>$list['openid']),"nickname,user_id");
                $lists[$key]["nickname"] = $user["nickname"];
                $lists[$key]["user_id"] = $user["user_id"];
                $condition = "user_id ='". $user["user_id"]."' and add_time >= '".$list['create_time']."' and pay_status = 1";
                $count = getCountWithCondition("order", $condition);
                $lists[$key]["orderCount"] = intval($count);
            }
        }
        $this->assignData['list'] =$lists;
        $this->assignData['qr_id'] =$id;
        $this->assignData['page'] = $show;
        return $this->assignData;
    }

    //用户列表
    public function userOut()
    {
        $id = I("id");
        $condition = array("qr_id" => $id);
        $lists =M(self::TB_LIST)->where($condition)->order(" create_time desc")->group('openid')->select();
        if(!empty($lists)){
            $strTable ='<table width="500" border="1">';
            $strTable .= '<tr>';
            $strTable .= '<td style="text-align:center;font-size:12px;width:120px;">ID</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="100">昵称</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">类型</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">扫码时间</td>';
            $strTable .= '</tr>';
            foreach ($lists as $key=>$list){
                $user = findDataWithCondition("users",array('openid'=>$list['openid']),"nickname");
                $strTable .= '<tr>';
                $strTable .= '<td>'.$list['id'].'</td>';
                $strTable .= '<td>'.$user['nickname'].'</td>';
                $strTable .= '<td>'.$list['event'].'</td>';
                $strTable .= '<td>'.date('Y-m-d H:i',$list['create_time']).'</td>';
                $strTable .= '</tr>';

            }
            $strTable .='</table>';
            header("Content-type: application/vnd.ms-excel");
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=qrcode_".date('Y-m-d').".xls");
            header('Expires:0');
            header('Pragma:public');
            echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
            exit;
        }else{
            return addonsError("暂无数据！");
        }
    }

    //生成二维码
    public function create()
    {
        $id = I("id");
        $info = findDataWithCondition(self::TB_QR, array("id" => $id));
        if (IS_POST) {
            $time = time();
            $keyWord = I("key_word");
            $data = array(
                "key_word" => $keyWord,
            );
            if( !empty($info)){
                saveData(self::TB_QR,array('id'=>$id),$data);
                return addonsSuccess("修改成功",U("Admin/Addons/createQRCode"));
            }else{
                $code = "addons_qr_code_" . $time;
                $return = addons_create_qr_code($code);
                if (!empty($return['errcode'])) {
                    return addonsError($return['errmsg']);
                }
                $data["code"] = $code;
                $data["limit_time"] = 0;
                $data["type"] = 1;
                $data["ticket"] = $return['ticket'];
                $data["url"] = $return['url'];
                $data["create_time"] = $time;
                if (addData(self::TB_QR, $data)) {
                    return addonsSuccess("插入成功",U("Admin/Addons/createQRCode"));
                } else {
                    return addonsError("插入数据失败");
                }
            }
        }
        $this->assignData['info'] = $info;
        return $this->assignData;
    }


    public function order(){
        $id = I('id');
        $user_id = I('user_id');
        $info = findDataWithCondition(self::TB_LIST, array("id" => $id));
        $info = M(self::TB_LIST)->where(array('qr_id'=>$info['qr_id'],"openid"=>$info['openid']))->field("create_time") -> order('create_time')->find();
        $condition = "user_id ='".$user_id."' and add_time >= '".$info['create_time']."' and pay_status = 1";
        $count = getCountWithCondition("order", $condition);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $lists =M("order")->where($condition)->limit($Page->firstRow, $Page->listRows)->order(" add_time desc")->select();
        $orderLogic = new Admin\Logic\OrderLogic();
        $lists = $orderLogic -> getOrderListInfo( $lists );
        $this->assignData['list'] =$lists;
        $this->assignData['page'] = $show;
        return $this->assignData;
    }

    public function route(){
        $id = I('id');
        $user_id = I('user_id');
        $info = findDataWithCondition(self::TB_LIST, array("id" => $id));

        $condition = "user_id ='".$user_id."' and create_time >= '".$info['create_time']."' ";
        $count = getCountWithCondition("user_route", $condition);
        $Page = new \Think\Page($count, 50);
        $show = $Page->show();
        $lists =M("user_route")->where($condition)->limit($Page->firstRow, $Page->listRows)->order(" create_time desc")->select();
        $this->assignData['list'] =$lists;
        $this->assignData['page'] = $show;
        return $this->assignData;
    }


}