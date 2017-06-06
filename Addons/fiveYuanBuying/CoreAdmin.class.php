<?php
@include 'Addons/fiveYuanBuying/Function/base.php';
class fiveYuanBuyingAdminController
{

    const TB_ORDER = "addons_fiveyuanbuying_order";

    public $assignData = array();


    public function __construct()
    {

    }


    public function index()
    {
        $count = getCountWithCondition(self::TB_ORDER );
        $Page  = new \Think\Page( $count , 10 );
        $show = $Page -> show();
        $this->assignData['list'] = M( self::TB_ORDER )->limit($Page->firstRow,$Page->listRows)->order("id desc") -> select();
        $this->assignData['page'] = $show;
        return $this->assignData;

    }

}