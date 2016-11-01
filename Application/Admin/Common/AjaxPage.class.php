<?php
namespace Admin\Common;

class AjaxPage{
    public $firstRow; // 起始行数
    public $listRows; // 列表每页显示行数
    public $parameter; // 分页跳转时要带的参数
    public $totalRows; // 总行数
    public $totalPages; // 分页总页面数
    public $rollPage   = 11;// 分页栏每页显示的页数
	public $lastSuffix = true; // 最后一页是否显示总页数

    private $p       = 'p'; //分页参数名
    private $url     = ''; //当前链接URL
    private $nowPage = 1;

	// 分页显示定制
    private $config  = array(
        'header' => '<span class="rows">共 %TOTAL_ROW% 条记录</span>',
        /*
        'prev'   => '<<',
        'next'   => '>>',
        'first'  => '1...',
        'last'   => '...%TOTAL_PAGE%',
        */
        'prev'   => '上一页',
        'next'   => '下一页',
        'first'  => '首页',
        'last'   => '尾页',
        'theme'  => '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%',
    );

    /**
     * 架构函数
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows, $listRows=20, $parameter = array()) {
        C('VAR_PAGE') && $this->p = C('VAR_PAGE'); //设置分页参数名称
        /* 基础设置 */
        $this->totalRows  = $totalRows; //设置总记录数
        $this->listRows   = $listRows;  //设置每页显示行数
        $this->parameter  = empty($parameter) ? $_GET : $parameter;
        $this->nowPage    = empty($_GET[$this->p]) ? 1 : intval($_GET[$this->p]);
        $this->nowPage    = $this->nowPage>0 ? $this->nowPage : 1;
        $this->firstRow   = $this->listRows * ($this->nowPage - 1);

    }

    /**
     * 定制分页链接设置
     * @param string $name  设置名称
     * @param string $value 设置值
     */
    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 生成链接URL
     * @param  integer $page 页码
     * @return string
     */
    private function url($page){
        return str_replace(urlencode('[PAGE]'), $page, $this->url);
    }

    /**
     * 组装分页链接
     * @return string
     */
    public function show() {
        if(0 == $this->totalRows) return '';

        /* 生成URL */
        $this->parameter[$this->p] = '[PAGE]';
        $this->url = U(ACTION_NAME, $this->parameter);
        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数
        if(!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }

        /* 计算分页临时变量 */
        $now_cool_page      = $this->rollPage/2;
		$now_cool_page_ceil = ceil($now_cool_page);
		$this->lastSuffix && $this->config['last'] = $this->totalPages;
        $this->config['last'] = '尾页';


        $up_page = '<span class="pagination">';
        //上一页
        $up_row  = $this->nowPage - 1;
        $up_page .= $up_row > 0 ? '<a data-p="'.$up_row.'" href="javascript:void(0)" class="prev1" ><i></i>' . $this->config['prev'] . '</a>' : '<a data-p="1" href="javascript:void(0)" class="prev1" ><i></i>' . $this->config['prev'] . '</a>';


        //下一页
        $down_row  = $this->nowPage + 1;
       $up_page .= ($down_row <= $this->totalPages) ? '<a class="next1" data-p="'.$down_row.'" href="javascript:void(0)">' . $this->config['next'] . '<i></i></a>' : '<a class="next1" data-p="'.$this->totalPages.'" href="javascript:void(0)">' . $this->config['next'] . '<i></i></a>';

        $up_page .= '</span>';

        $link_page = '<span class="searchPage">';
        $link_page .= '<span class="page-sum">共<strong class="allPage"><span class="color-c">'.$this->nowPage.'</span>/'.$this->totalPages.'</strong>页 每页'.$this->listRows.'条</span>';
        $link_page .= '<span class="page-go">跳转<input type="text" name="page">页</span><a href="javascript:void(0);" onclick="skipPage(this);" class="page-btn">GO</a>';
        $link_page .= '</span>';


        $page_strs = $up_page.$link_page;
        if($this->totalRows < $this->listRows ){
            return false;
        }
        return "<div class='p5'><div class='pages'><div class='Pagination'>{$page_strs}</div></div></div>";
    }
}
