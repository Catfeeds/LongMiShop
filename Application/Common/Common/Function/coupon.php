<?php



/**
 * 获取礼品券id
 * @param $code
 * @return int
 */
function getGiftCouponId( $code ){
    $condition = array(
        "gift_coupon_id"    =>  array('neq',0),
        "coupon_id"         =>  array('eq',0),
        "code"              =>  $code,
    );
    $data = findDataWithCondition( "coupon_code" , $condition , "gift_coupon_id");
    return intval($data['gift_coupon_id']);
}

/**
 * 获取优惠券id
 * @param $code
 * @return int
 */
function getCouponId( $code ){
    $condition = array(
        "code"     => $code,
    );
    $data = findDataWithCondition( "coupon_list" , $condition , "cid");
    return intval($data['cid']);
}


/**
 * 生成随机数
 * @param $number
 * @return array
 */
function getCouponCode( $number ){
    $codeArray = array();
//    $GiftCouponCount = M('gift_coupon')->count();
    $key = 0;
    for( $i = 1 ; $i <= $number; $i++){

//        $codeNumber = $GiftCouponCount + $i;
//        $tempString = md5( $codeNumber . "LONGMI");
        do{
            $code = get_rand_str(8,0,1);//获取随机8位字符串
            $check_exist = findDataWithCondition('coupon_list',array('code'=>$code),"code");
            if( empty( $check_exist ) ){
                $check_exist = findDataWithCondition('coupon_code',array('code'=>$code),"code");
            }
        }while($check_exist);

        $codeArray[$key] = generateCode(18);
        $key ++;
    }
    return $codeArray;
}

/**
 * 获取优惠券详情
 * @param $couponId
 * @return mixed
 */
function getCouponInfo( $couponId ){
    $couponId = intval( $couponId );
    $condition  = array(
        "id" => $couponId,
    );
    return findDataWithCondition( 'coupon' , $condition  );
}

/**
 * 通过兑换码获取优惠券详情
 * @param $code
 * @return mixed
 */
function getCouponInfoWithCode( $code ){
    $couponId = getCouponId( $code );
    return getCouponInfo( $couponId );
}


/**
 * 领取卡券
 * @param $code
 * @param $userId
 * @return bool
 */
function receiveCouponCode( $code , $userId  )
{
    $condition = array(
        "code"     => $code,
        "uid"      => array("eq", "0"),
        "order_id" => array("eq", "0"),
    );
    $data = array(
        "uid"           => $userId,
        "receive_time"  => time()
    );
    $result = saveData("coupon_list", $condition, $data);
    if ($result > 0 || $result === 0) {
        return true;
    }
    return false;
}


/**
 * 获取优惠券通过兑换码
 * @param $code
 * @param $userId
 * @return bool
 */
function gainCouponWithCode( $code , $userId ){

    $couponInfo = getCouponInfoWithCode( $code );
    if( empty( $couponInfo ) ){
        return false;
    }
    if( ! receiveCouponCode( $code , $userId ) ){
        return false;
    }
//    if( ! giveGift( $userId , $couponId , 1 ) ){
//        return false;
//    }
    return true;
}


/**
 * 卡券优惠金额计算
 * @param $couponId
 * @param $userId
 * @param $money
 * @param array $goodsData
 */
function cardDiscountAmountCalculation( $couponId , $userId , $money ,  $goodsData = array()){

    $where['id'] = $couponId;
    $where['uid'] = $userId;
    $goods_data = array();
    $couponListRes = M('coupon_list') -> where($where)->find();
    if(!empty($couponListRes)){
        $wheres['id'] = $couponListRes['cid'];
        $couRes =  M('coupon') -> where($wheres)->find();
        if(!empty($couRes)){
            if( empty($goodsData) ){
                $result = $this -> cartLogic -> cartList($this->user, $this->session_id,1,1); // 获取购物车商品
                $cartList = $result['cartList'];
                foreach( $cartList  as $key => $item){
                    if($item['selected'] == 1){
                        $goods_data[$key]['spec_key'] = $item['spec_key']; //商品规格
                        $goods_data[$key]['goods_id'] = $item['goods_id']; //商品id
                        $goods_data[$key]['goods_num'] = $item['goods_num']; //件数  重量
                        $goods_data[$key]['goods_name'] = $item['goods_name']; //商品名称
                        $goods_data[$key]['goods_price'] = $item['goods_price']; //商品价格
                    }
                }
            }else{
                $goods_data = $goodsData;
            }

            if($couRes['is_discount'] == 1){ //折扣券
                if($couRes['is_appoint'] == 1){
                    if( empty($goods_data)){
                        $moneyRes = $money ;
                        $privilege = 0;
                    }else{
                        $haveGoodsId = false;
                        $goodsSum = 0;
                        foreach ( $goods_data as $goods_data_item){
                            if( $goods_data_item["goods_id"] == $couRes['goods_id']){
                                $haveGoodsId = true;
                                $goodsSum += $goods_data_item['goods_price'];
                            }
                        }
                        if(!$haveGoodsId){
                            $moneyRes = $money ;
                            $privilege = 0;
                        }else{
                            $couResMoney = intval($goodsSum) / 100;
                            $moneyRes = $money * $couResMoney;
                            $privilege = $money - $moneyRes;
                        }
                    }
                }else{
                    $couResMoney = intval($couRes['money']) / 100;
                    $moneyRes = $money * $couResMoney;
                    $privilege = $money - $moneyRes;
                }
            }elseif($couRes['is_discount'] == 2){ //买一送一券
                if( empty($goods_data)){
                    $moneyRes = $money ;
                    $privilege = 0;
                }else{
                    $goodsPrice = 0;
                    $goodsSum = 0;
                    foreach ( $goods_data as $goods_data_item){
                        if( $goods_data_item["goods_id"] == $couRes['goods_id']){
                            $goodsSum += $goods_data_item['goods_num'];
                            if( $goodsPrice < $goods_data_item['goods_price']){
                                $goodsPrice = $goods_data_item['goods_price'];
                            }
                        }
                    }
                    if( $goodsSum <= 1){
                        $moneyRes = $money - intval($goodsPrice);
                        $privilege = $goodsPrice;
                    }else{
                        $moneyRes = $money ;
                        $privilege = 0;
                    }
                }
            }elseif($couRes['is_discount'] == 3){ //第三方展示券
                $moneyRes = $money ;
                $privilege = 0;
            }else{ //优惠券
                if($couRes['is_appoint'] == 1){
                    if( empty($goods_data)){
                        $moneyRes = $money ;
                        $privilege = 0;
                    }else{
                        $haveGoodsId = false;
                        $goodsSum = 0;
                        foreach ( $goods_data as $goods_data_item){
                            if( $goods_data_item["goods_id"] == $couRes['goods_id']){
                                $haveGoodsId = true;
                                $goodsSum += $goods_data_item['goods_price'];
                            }
                        }
                        if(!$haveGoodsId){
                            $moneyRes = $money ;
                            $privilege = 0;
                        }else{
                            $moneyRes = $money - intval($goodsSum);
                            $privilege = $money - $moneyRes;
                        }
                    }
                }else{
                    $moneyRes = $money - intval($couRes['money']);
                    $privilege = $money - $moneyRes;
                }
            }
        }else{
            $moneyRes = 0;
            $privilege = 0;
        }
        if( $moneyRes < 0){
            $moneyRes = 0;
            $privilege = $money;
        }
        exit(json_encode(callback(true,"计算成功",array('moneyRes'=>$moneyRes,'privilege'=>$privilege))));
    }else{
        exit(json_encode(callback(false,"没有此优惠券",array('couponListRes'=>$couponListRes))));
    }
}