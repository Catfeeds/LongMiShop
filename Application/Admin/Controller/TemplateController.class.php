<?php
namespace Admin\Controller;
use Admin\Model;
use Think\AjaxPage;
use Think\Page;

class TemplateController extends BaseController {
    
    
    /**
     *  模板列表
     */
    public function templateList(){     
         $t = I('t','pc'); // pc or  mobile        
         $m = ($t == 'pc') ? 'Home' : 'Mobile';
         $arr = scandir("./Template/$t/");
         foreach($arr as $key => $val)
         {
                if($val == '.' || $val == '..' )
                    continue;                 
                 $template_config[$val] = include "./Template/$t/$val/config.php";
         }
        
        $this->assign('t',$t);        
        // $default_theme =  tpCache("hidden.{$t}_default_theme"); // //$default_theme = M('Config')->where("name='{$t}_default_theme'")->getField('value');
        $template_arr = include("./Application/$m/Conf/html.php");        
        $this->assign('default_theme',$template_arr['DEFAULT_THEME']);
        $this->assign('template_config',$template_config);
        $this->display();
    }



    /**
     * 自定义模板
     */
    public function listDiyTemplate(){
        $list = M('diy_page') -> select();
        $this->assign('list',$list);
        $this->display();
    }


    public function addDiyTemplate(){
        $model_diy_page = M('diy_page');

        $multiid = intval(I('multiid'));
        $id = intval(I('id'));
        $wapeditor = I('wapeditor');
        if (!empty($wapeditor)) {
            $params =$wapeditor['params'];
            if (empty($params)) {
                $this->error("请您先设计手机端页面(错误编码：10001).",U('Admin/Template/listDiyTemplate'));
            }
            $params = json_decode(html_entity_decode(urldecode($params),ENT_QUOTES), true);
            if (empty($params)) {
                $this->error("请您先设计手机端页面(错误编码：10002).",U('Admin/Template/listDiyTemplate'));
            }
            $page = $params[0];
            $html = htmlspecialchars_decode($wapeditor['html'], ENT_QUOTES);
            $data = array(
//                'uniacid' => $_W['uniacid'],
                'multiid' => '0',
                'title' => $page['params']['title'],
                'description' => $page['params']['description'],
                'type' => 1,
                'status' => 1,
                'params' => json_encode($params),
                'html' => $html,
                'createtime' => TIMESTAMP,
            );
            if (empty($id)) {
                $id = $model_diy_page -> add($data);
            } else {
                $model_diy_page -> where(array('id' => $id)) -> save($data);
            }
//            if (!empty($page['params']['keyword'])) {
//                $cover = array(
////                    'uniacid' => $_W['uniacid'],
//                    'title' => $page['params']['title'],
//                    'keyword' => $page['params']['keyword'],
//                    'url' => murl('home/page', array('id' => $id), true, true),
//                    'description' => $page['params']['description'],
//                    'thumb' => $page['params']['thumb'],
//                    'module' => 'page',
//                    'multiid' => $id,
//                );
//                site_cover($cover);
//            }
            $this->success("页面保存成功!!!",U('Admin/Template/listDiyTemplate'));
        } else {
            $page = $model_diy_page -> where(array(':id' => $id)) ->find();
            if(empty($page)&&!empty($id)){
                $this->error("非法访问",U('Admin/Template/listDiyTemplate'));
            }
//            $this->assign('page',$page);
        }
        $this->display();


    }

    
    /**
     * 魔板选择
     */
    public function changeTemplate(){        
        
        $t = I('t','pc'); // pc or  mobile        
        $m = ($t == 'pc') ? 'Home' : 'Mobile';
        //$default_theme = tpCache("hidden.{$t}_default_theme"); // 获取原来的配置                
        //tpCache("hidden.{$t}_default_theme",$_GET['key']);
        //tpCache('hidden',array("{$t}_default_theme"=>$_GET['key']));                         
        // 修改文件配置  
         if(!is_writeable("./Application/$m/Conf/html.php"))
            return "文件/Application/$m/Conf/html.php不可写,不能启用模板,请修改权限!!!";
         
		$config_html ="<?php
		return array(
			'VIEW_PATH'       =>'./Template/$t/', // 改变某个模块的模板文件目录
			'DEFAULT_THEME'    =>'{$_GET['key']}', // 模板名称
			'TMPL_PARSE_STRING'  =>array(
		//                '__PUBLIC__' => '/Common', // 更改默认的/Public 替换规则
					'__STATIC__'     => '/Template/$t/{$_GET['key']}/Static', // 增加新的image  css js  访问路径  后面给 php 改了
			   ),
			   //'DATA_CACHE_TIME'=>60, // 查询缓存时间
			);
		?>";
         file_put_contents("./Application/$m/Conf/html.php", $config_html);       
        $this->success("操作成功!!!",U('Admin/Template/templateList',array('t'=>$t)));      
    }





}