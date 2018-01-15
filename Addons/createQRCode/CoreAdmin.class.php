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
                $number =M(self::TB_LIST)->where($condition)->group('openid')->select();
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
        $condition = array();
        if( !empty($id)){
            $condition = array("qr_id" => $id);
        }
        $count =M(self::TB_LIST)->where($condition)->group('openid')->select();
        $count = count($count);
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
                $condition = array("id"=>$list['qr_id']);
                $lists[$key]["qrInfo"] = findDataWithCondition(self::TB_QR, $condition);
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
        $condition = array();
        if( !empty($id)){
            $condition = array("qr_id" => $id);
        }
        $lists =M(self::TB_LIST)->where($condition)->order(" create_time desc")->group('openid')->select();
        if(!empty($lists)){
            $strTable ='<table width="500" border="1">';
            $strTable .= '<tr>';
            $strTable .= '<td style="text-align:center;font-size:12px;width:120px;">ID</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="100">昵称</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">类型</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">关键字</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">扫码时间</td>';
            $strTable .= '</tr>';
            foreach ($lists as $key=>$list){
                $user = findDataWithCondition("users",array('openid'=>$list['openid']),"nickname");
                $condition = array("id"=>$list['qr_id']);
                $qrInfo = findDataWithCondition(self::TB_QR, $condition);
                $strTable .= '<tr>';
                $strTable .= '<td>'.$list['id'].'</td>';
                $strTable .= '<td>'.$user['nickname'].'</td>';
                $strTable .= '<td>'.$list['event'].'</td>';
                $strTable .= '<td>'.$qrInfo['key_word'].'</td>';
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
            $title = I("title");
            $keyWord = I("key_word");
            $data = array(
                "title" => $title,
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

    public function allOrderOut(){
        $qrList = selectDataWithCondition(self::TB_QR);
        if(!empty($qrList)){
            $dataList = array();
//            $orderIdList = array();
            foreach ($qrList as $qrItem) {
                $openidList = selectDataWithCondition(self::TB_LIST, array("qr_id" => $qrItem["id"]), array("openid","create_time"));
                if (!empty($openidList)) {
                    foreach ($openidList as $openidItem) {
                        $info = findDataWithCondition("users", array("openid" => $openidItem["openid"]), array("user_id"));
                        if (!empty($info)) {
                            $condition = "user_id ='".$info["user_id"]."' and add_time >= '".$openidItem['create_time']."' and pay_status = 1";
                            $lists =M("order")->where($condition)->order(" add_time desc")->select();
                            if(!empty($lists)){
                                foreach ($lists as $item){
//                                    if(isset($orderIdList[$item["order_id"]])){
//
//                                    }
                                    $dataList[$qrItem["id"]."_".$item["order_id"]] = $item;
                                    $dataList[$qrItem["id"]."_".$item["order_id"]]["qr_id"] = $qrItem["id"];
                                    $dataList[$qrItem["id"]."_".$item["order_id"]]["qr_key_word"] = $qrItem["key_word"];
                                    $dataList[$qrItem["id"]."_".$item["order_id"]]["qr_name"] = $qrItem["title"];
                                }
                            }
                        }
                    }
                }

            }

            $region	= M('region')->getField('id,name');

            $strTable ='<table width="500" border="1">';
            $strTable .= '<tr>';
            $strTable .= '<td style="text-align:center;font-size:12px;width:120px;">二维码名称</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;width:120px;">订单编号</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="100">日期</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">下单时间</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货人</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货地址</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">手机</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">订单金额</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">实际支付</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付方式</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付状态</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">发货状态</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">物流信息</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品信息</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品数量</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">用户备注</td>';
            $strTable .= '</tr>';
            foreach($dataList as $k=>$val){
                $orderGoods = D('order_goods') -> where('order_id='.$val['order_id'])->select();
                $lineNumber = count($orderGoods);

                $tempString = "";
                $tempString .= '<tr>';
                $tempString .= '<td style="text-align:center;font-size:12px;" rowspan="'.$lineNumber.'">&nbsp;'.$val['qr_id'].'/&nbsp;'.$val['qr_name'].'/&nbsp;'.$val['qr_key_word'].'</td>';
                $tempString .= '<td style="text-align:center;font-size:12px;" rowspan="'.$lineNumber.'">&nbsp;'.$val['order_sn'].'</td>';
                $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$val['create_time'].' </td>';
                $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.date("H:i:d",$val['add_time']).' </td>';
                $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'."{$val['consignee']}".' </td>';
                $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'."{$region[$val['province']]},{$region[$val['city']]},{$region[$val['district']]},{$region[$val['twon']]}".$val['address'].'</td>';
                $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$val['mobile'].'</td>';
                $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$val['goods_price'].'</td>';
                $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$val['order_amount'].'</td>';
                $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$val['pay_name'].'</td>';
                $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$this->pay_status[$val['pay_status']].'</td>';
                $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$this->shipping_status[$val['shipping_status']].'</td>';

                $strGoods=array();
                $shippingArray=array();
                foreach($orderGoods as $key =>  $goods){
                    $returnRes = M('return_goods') -> where(array('order_id'=>$goods['order_id'],'goods_id'=>$goods['goods_id'],'spec_key'=>$goods['spec_key']))->find();
                    if(!empty($returnRes) && $returnRes['result'] == 0){ //有未处理订单 不能导出该订单
                        continue 2;
                    }
                    if( $returnRes['result'] == 1 ){ //同意退款 跳出该商品
                        continue;
                    }
                    $strGoods[$key]['string']= "商品编号：".$goods['goods_sn']." 商品名称：".$goods['goods_name']." ";
                    if ($goods['spec_key_name'] != '') $strGoods[$key]['string'] .= " 规格：".$goods['spec_key_name'];
                    $strGoods[$key]['number']=$goods['goods_num'];
                    $shipping_name = M('delivery_doc')->field('shipping_name,invoice_no')->where(array('id'=>$goods['delivery_id']))->find();
                    if( !empty($shipping_name) && !empty($shipping_name['shipping_name']) && !empty($shipping_name['invoice_no']) ){
                        $shippingArray[$key]['shipping_name'] = $shipping_name['shipping_name'];
                        $shippingArray[$key]['invoice_no'] = $shipping_name['invoice_no'];
                    }
                }
                unset($orderGoods);
                $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">';
                if(!empty($shippingArray)){
                    foreach ($shippingArray as $shippingItem){
                        $tempString .= $shippingItem['shipping_name'] .":[".$shippingItem['invoice_no']."]<br>";
                    }
                }
                $tempString .= '</td>';
                $tempString .= '<td style="text-align:left;font-size:12px;">'.$strGoods[0]['string'].' </td>';
                $tempString .= '<td style="text-align:left;font-size:12px;"><b style="color:#f00;">'.$strGoods[0]['number'].'</b> </td>';
                $tempString .= '<td style="text-align:left;font-size:12px;" rowspan="'.$lineNumber.'">'.$val['user_note'].'</td>';
                $tempString .= '</tr>';

                if( $lineNumber > 1){
                    for( $myI=1;$myI < $lineNumber;$myI++ ){
                        $tempString .= '<tr>';
                        $tempString .= '<td style="text-align:left;font-size:12px;">'.$strGoods[$myI]['string'].' </td>';
                        $tempString .= '<td style="text-align:left;font-size:12px;"><b style="color:#f00;">'.$strGoods[$myI]['number'].'</b> </td>';
                        $tempString .= '</tr>';
                    }
                }

                $strTable .= $tempString;
            }
            $strTable .='</table>';
            unset($orderList);
            header("Content-type: application/vnd.ms-excel");
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=qrcode_order_".date('Y-m-d').".xls");
            header('Expires:0');
            header('Pragma:public');
            echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
            exit;
        }

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
