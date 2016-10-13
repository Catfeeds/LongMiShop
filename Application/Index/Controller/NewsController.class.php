<?php
namespace Index\Controller;

class NewsController extends IndexBaseController {

    function exceptAuthActions()
    {
        return array(
            "index",
            "newsDetail",
        );
    }

    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
        $where = "is_open = 1 AND  device_type != 2 ";
//        $where = "is_open = 1 AND  device_type != 1 ";
        $count = M('article')->where($where)->count();
        $limit = 10;
        $Page = new \Common\Common\Page($count,$limit);
        $list = M('article')->where($where)->order('publish_time DESC')->limit($Page->firstRow.','.$Page->listRows) -> select();

        $show = $Page->show();
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('count',$count);
        $this->assign('limit',$limit);
        $this->display();
    }


    public function newsDetail(){
        $id = I('id');
        $where = "is_open = 1 AND  article_id = '".$id."' ";
        $info = M('article')->where($where)->find();
        if( empty($info) ){
            $this -> error("找不到此文章！");
        }
        $this->assign('info',$info);
        $this->display();
    }
}