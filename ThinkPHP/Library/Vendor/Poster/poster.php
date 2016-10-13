<?php
/**
 * [MF System] Copyright (c) 2015 jianyebao.com
 * MF is NOT a free software, it under the license terms, visited http://www.jianyebao.com/ for more details.
 * 推广二维码海报生成类
 */

set_time_limit(0);
ini_set('memory_limit','1024M');

class Poster {

    private $_waterMask ;//水印类对象
    private $_fans; //当前粉丝
    private $_filename ;//文件名
    private $_srcFile ;//源文件路径
    private $_imgFile ;//生成文件路径
    private $_tempFile ;//临时文件路径
    private $_qrcodeFile ;//缩略图路径
    private $_avatarFile ;//头像路径
    private $_logoFile ;//LOGO路径

    private $_uid ;//海报模板
    private $_template ;//海报模板
    private $_poster; //选择的模板
    private $_setting ;//海报模板设置

    function __construct() { //析构函数
    }

    /**
     * 执行生成海报
     * @param $fans 粉丝数组
     * @return bool|int 0成功,>0失败
     */
    public static function run($fans)
    {
        if(!$fans)return array('ret'=>1001,'msg'=>'not exist fans');
        $s=time();
        $self = new self();
        $self->_fans = $fans;
        $self->_filename = $fans['file_name'];
        $self->_imgFile =  $fans['base_url'].$fans['file_url'].$fans['file_name'];
        $self->_imgFile_copy=  $fans['avatar_url'].$fans['file_url']."123.png";
        $self->_uid =$fans['uid'];
        if(file_exists($self->_imgFile))
        {
            return array('ret'=>0,'msg'=>'success','data'=>$self->_imgFile);
        }

        //复制原图
        copy($fans['base_url'].$fans['img_url']."one.jpg",$fans['base_url'].$fans['file_url'].$fans['file_name']);

//        $self->_tempFile    =   $fans['base_url'].$fans['img_url']."one.jpg";
        $self->_tempFile =  $fans['base_url'].$fans['file_url'].$fans['file_name'] ;

        $self->_qrcodeFile  =   $fans['base_url'].$fans['qrcode_url'].$fans['qrcode_name'];
        $self->_avatarFile  =   $fans['headimg'];
        $self->_logoFile    =   $fans['base_url'].$fans['img_url']."J6.png";


        $self->_setting = array(
            'qrcode'=>array(
                'flag'=>1,
                'left'=>161,
                'top'=>358,
                'width'=>500,
                'height'=>500
            ),
//            'logo'=>array(
//                'flag'=>1,
//                'left'=>270,
//                'top'=>180
//            ),
            'avatar'=>array(
                'flag'=>1,
                'left'=>32,
                'top'=>19,
                'width'=>120,
                'height'=>100
            ),
            'name'=>array(
                'flag'=>1,
                'length'=>200,
                'left'=>271,
                'top'=>31,
                'color'=>'#0190c3',
                'font-size'=>30
            ),
            'number'=>array(
                'color'=>'#0190c3',
                'font-size'=>45
            ),
            'number1'=>array(
                'flag'=>1,
                'left'=>134,
                'top'=>845,
            ),
            'number2'=>array(
                'flag'=>1,
                'left'=>221,
                'top'=>845,
            ),
            'number3'=>array(
                'flag'=>1,
                'left'=>307,
                'top'=>845,
            ),
            'number4'=>array(
                'flag'=>1,
                'left'=>389,
                'top'=>845,
            ),
            'number5'=>array(
                'flag'=>1,
                'left'=>476,
                'top'=>845,
            ),

        );

        vendor("Qrcode.watermask");
        $self->_waterMask = new WaterMask('');
        $self->_waterMask->setting = $self->_setting;


        //二维码
        if(!$self->qrcode())return array('ret'=>1007,'msg'=>'qrcode failed');
        $self->_waterMask->getImg($self->_tempFile,$self->_imgFile);

//
        //头像
        if(!$self->avatar())return array('ret'=>1008,'msg'=>'avatar failed');

        //品牌LOGO
        if(!$self->logo())return array('ret'=>1009,'msg'=>'logo failed');

        //昵称
        if(!$self->name())return array('ret'=>1010,'msg'=>'name failed');

        //累计总裁数量 万位数
        if(!$self->number1())return array('ret'=>1020,'msg'=>'name failed');
        //累计总裁数量 千位数
        if(!$self->number2())return array('ret'=>1020,'msg'=>'name failed');
        //累计总裁数量 百位数
        if(!$self->number3())return array('ret'=>1020,'msg'=>'name failed');
        //累计总裁数量 十位数
        if(!$self->number4())return array('ret'=>1020,'msg'=>'name failed');
        //累计总裁数量 个位数
        if(!$self->number5())return array('ret'=>1020,'msg'=>'name failed');


//
//        //品牌名称
//        if(!$self->corp())return array('ret'=>1011,'msg'=>'corp failed');
//
//        //手机
//        if(!$self->phone())return array('ret'=>1012,'msg'=>'phone failed');


        //主要的东西
//        $self->_waterMask->getImg($self->_tempFile,$self->_imgFile);


//        if(file_exists($self->_imgFile))
//        {
//            $media_id = $self->getMediaId();
//            if($media_id){
//                if((time()-$s) > 5){
//                    $msg = $self->sendMsg($media_id);
//                }
//
//                return array('ret'=>0,'msg'=>'success','data'=>$media_id);
//            }
//        }
        return array('ret'=>1012,'msg'=>'Poster end failed');
    }

    public function sendMsg($media_id){
        if($this->_fans['acid']) {
            $acc = WeAccount::create($this->_fans['acid']);
            $send['touser'] = trim($this->_fans['openid']);
            $send['msgtype'] = "image";
            $send['image'] = array('media_id' => $media_id);
            $data = $acc->sendCustomNotice($send);
            if(is_error($data)) {
                return $data['message'];
            } else {
                return '消息发送成功';
            }
        }
        return '子号不存在';
    }

    /**
     * 添加二维码水印
     */
    public function qrcode()
    {/*
        //二维码下中中
        $qrcode=pdo_fetch("SELECT * FROM ".tablename('qrcode')." WHERE fanid = :fanid", array(':fanid'=>$this->_fans['fanid']));

        if(!$qrcode)
            return false;

        if(!$this->_waterMask->getImg($this->_srcFile,$this->_tempFile))
        {
            return false;
        }

        if(!file_exists($this->_tempFile))
        {
            return false;
        }

//*/
        $this->_waterMask->srcImg = $this->_tempFile;
        $this->_waterMask->waterImg = $this->_qrcodeFile;
       // $this->_waterMask->waterImg = $this->_waterMask->resize($this->_waterMask->waterImg, $this->_qrcodeFile, 256, 256);

        //$this->_waterMask->waterImg = $this->_waterMask->resize( $this->_waterMask->waterImg,$this->_qrcodeFile, 400,400);

        if(empty($this->_waterMask->waterImg) || !file_exists($this->_waterMask->waterImg)){
            return false;
        }
        if($this->_waterMask->waterImg) {
            $this->_waterMask->waterType = 1; //类型：0为文字水印、1为图片水印
            $this->_waterMask->pos = 9;
            $this->_waterMask->transparent = 100; //水印透明度
            $msg = $this->_waterMask->output(); //输出水印图片文件覆盖到输入的图片文件
            if($msg){
                return false;
            }
        }

        return true;

    }


    /**
     * 添加头像水印
     */
    public function avatar()
    {
        if (!$this->_setting['avatar']['flag']) return true;
        //头像上左
//        if (!file_exists($this->_avatarFile)) {
//            return false;
//        } else {
            $this->_waterMask->waterImg = $this->_avatarFile;
        //echo $this->_avatarFile;
//        }

//        if(empty($this->_waterMask->waterImg) || !file_exists($this->_waterMask->waterImg)){
//            return false;
//        }

        $src_img = $this->_avatarFile;
        $dst_img = $this->_imgFile_copy;

        $img = file_get_contents($src_img);
        if($img == ""){
            $img = file_get_contents("http://".$_SERVER['HTTP_HOST']."/".$src_img);
        }
        file_put_contents('Public/avatar/'.$this->_uid.'.png',$img);



        $stat =  $this->_waterMask->img2thumb('Public/avatar/'.$this->_uid.'.png', 'Public/avatar/middle/'.$this->_uid.'.png', $width = 132, $height = 124, $cut = 0, $proportion = 0);
        $this->_waterMask->waterImg = 'Public/avatar/middle/'.$this->_uid.'.png';

        $this->_waterMask->pos = 10;
        $msg = $this->_waterMask->output(); //输出水印图片文件覆盖到输入的图片文件
        //print_r($msg);
        if($msg){
            return false;
        }

        return true;
    }
    /**
     * 添加厂家LOGO
     */
    public function logo()
    {
        if(!$this->_setting['logo']['flag'] ) return true;
        //品牌LOGO
        if(!file_exists($this->_logoFile)) {
            return false;
        }
        else
            $this->_waterMask->waterImg = $this->_logoFile;


        if(empty($this->_waterMask->waterImg) || !file_exists($this->_waterMask->waterImg)){
            return false;
        }


        if($this->_waterMask->waterImg) {
            $this->_waterMask->pos = 13;
            $msg = $this->_waterMask->output(); //输出水印图片文件覆盖到输入的图片文件
            if($msg){
                return false;
            }
        }

        return true;

    }

    /**
     * 添加粉丝姓名
     */
    public function name()
    {
        if(!$this->_setting['name']['flag'])return true;
        //姓名
        $this->_waterMask->waterType = 0; //类型：0为文字水印、1为图片水印
        $this->_waterMask->pos = 11;
        $this->_waterMask->waterStr = cutstr($this->_fans['user_name'],empty($this->_setting['name']['length'])?10:$this->_setting['name']['length']); //水印文字
        $this->_waterMask->fontSize = $this->_setting['name']['font-size']?$this->_setting['name']['font-size']:20; //文字字体大小

        if(!empty($this->_setting['name']['color']))
            $this->_waterMask->fontColor = $this->html2rgb($this->_setting['name']['color']);

        $msg = $this->_waterMask->output(); //输出水印图片文件覆盖到输入的图片文件

        if($msg){
            return false;
        }

        return true;
    }


    /**
     * 添加会员数量 万
     */
    public function number1()
    {
        if(!$this->_setting['number1']['flag'])return true;
        //姓名
        $this->_waterMask->waterType = 0; //类型：0为文字水印、1为图片水印
        $this->_waterMask->pos = 15;
        $this->_waterMask->waterStr = cutstr($this->_fans['number1'],empty($this->_setting['number']['length'])?10:$this->_setting['number']['length']); //水印文字
        $this->_waterMask->fontSize = $this->_setting['number']['font-size']?$this->_setting['number']['font-size']:20; //文字字体大小
        if(!empty($this->_setting['number']['color']))
            $this->_waterMask->fontColor = $this->html2rgb($this->_setting['number']['color']);
        $msg = $this->_waterMask->output(); //输出水印图片文件覆盖到输入的图片文件
        if($msg){
            return false;
        }
        return true;
    }

    /**
     * 添加会员数量 千
     */
    public function number2()
    {
        if(!$this->_setting['number2']['flag'])return true;
        //姓名
        $this->_waterMask->waterType = 0; //类型：0为文字水印、1为图片水印
        $this->_waterMask->pos = 16;
        $this->_waterMask->waterStr = cutstr($this->_fans['number2'],empty($this->_setting['number']['length'])?10:$this->_setting['number']['length']); //水印文字
        $this->_waterMask->fontSize = $this->_setting['number']['font-size']?$this->_setting['number']['font-size']:20; //文字字体大小
        if(!empty($this->_setting['number']['color']))
            $this->_waterMask->fontColor = $this->html2rgb($this->_setting['number']['color']);
        $msg = $this->_waterMask->output(); //输出水印图片文件覆盖到输入的图片文件
        if($msg){
            return false;
        }
        return true;
    }

    /**
     * 添加会员数量 百
     */
    public function number3()
    {
        if(!$this->_setting['number3']['flag'])return true;
        //姓名
        $this->_waterMask->waterType = 0; //类型：0为文字水印、1为图片水印
        $this->_waterMask->pos = 17;
        $this->_waterMask->waterStr = cutstr($this->_fans['number3'],empty($this->_setting['number']['length'])?10:$this->_setting['number']['length']); //水印文字
        $this->_waterMask->fontSize = $this->_setting['number']['font-size']?$this->_setting['number']['font-size']:20; //文字字体大小
        if(!empty($this->_setting['number']['color']))
            $this->_waterMask->fontColor = $this->html2rgb($this->_setting['number']['color']);
        $msg = $this->_waterMask->output(); //输出水印图片文件覆盖到输入的图片文件
        if($msg){
            return false;
        }
        return true;
    }

    /**
     * 添加会员数量 十
     */
    public function number4()
    {
        if(!$this->_setting['number4']['flag'])return true;
        //姓名
        $this->_waterMask->waterType = 0; //类型：0为文字水印、1为图片水印
        $this->_waterMask->pos = 18;
        $this->_waterMask->waterStr = cutstr($this->_fans['number4'],empty($this->_setting['number']['length'])?10:$this->_setting['number']['length']); //水印文字
        $this->_waterMask->fontSize = $this->_setting['number']['font-size']?$this->_setting['number']['font-size']:20; //文字字体大小
        if(!empty($this->_setting['number']['color']))
            $this->_waterMask->fontColor = $this->html2rgb($this->_setting['number']['color']);
        $msg = $this->_waterMask->output(); //输出水印图片文件覆盖到输入的图片文件
        if($msg){
            return false;
        }
        return true;
    }

    /**
     * 添加会员数量 万
     */
    public function number5()
    {
        if(!$this->_setting['number5']['flag'])return true;
        //姓名
        $this->_waterMask->waterType = 0; //类型：0为文字水印、1为图片水印
        $this->_waterMask->pos = 19;
        $this->_waterMask->waterStr = cutstr($this->_fans['number5'],empty($this->_setting['number']['length'])?10:$this->_setting['number']['length']); //水印文字
        $this->_waterMask->fontSize = $this->_setting['number']['font-size']?$this->_setting['number']['font-size']:20; //文字字体大小
        if(!empty($this->_setting['number']['color']))
            $this->_waterMask->fontColor = $this->html2rgb($this->_setting['number']['color']);
        $msg = $this->_waterMask->output(); //输出水印图片文件覆盖到输入的图片文件
        if($msg){
            return false;
        }
        return true;
    }


    /**
     * 添加手机号
     */
    public function phone()
    {
        if(!$this->_setting['phone']['flag']||!$this->_fans['phone'])return true;
        //姓名
        $this->_waterMask->waterType = 0; //类型：0为文字水印、1为图片水印
        $this->_waterMask->pos = 14;
        $this->_waterMask->waterStr = cutstr($this->_fans['phone'],empty($this->_setting['phone']['length'])?11:$this->_setting['phone']['length']); //水印文字
        $this->_waterMask->fontSize = $this->_setting['phone']['font-size']?$this->_setting['phone']['font-size']:20; //文字字体大小
        if(!empty($this->_setting['phone']['color']))
            $this->_waterMask->fontColor = $this->html2rgb($this->_setting['phone']['color']);
        $msg = $this->_waterMask->output(); //输出水印图片文件覆盖到输入的图片文件
        if($msg){
            return false;
        }

        return true;
    }
    /**
     * 添加厂家品牌名称
     */
    public function corp()
    {
        if(!$this->_setting['corp']['flag'])return true;
        //品牌名称
        $this->_waterMask->pos = 12;
        $this->_waterMask->waterStr = $this->_setting['corp']['name']?$this->_setting['corp']['name']:$this->_account['name']; //水印文字
        $this->_waterMask->fontSize = $this->_setting['corp']['font-size']?$this->_setting['corp']['font-size']:20; //文字字体大小
        $msg = $this->_waterMask->output(); //输出水印图片文件覆盖到输入的图片文件
        if($msg){
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function getMediaId()
    {
        //return $this->;
    }

	public function delImg($id)
	{
		pdo_delete('core_wechats_attachment',array('id'=>$id));

//		if(file_exists($this->_logoFile))
//			@unlink($this->_logoFile);
//		if(file_exists($this->_tempFile))
//			@unlink($this->_tempFile);
//		if(file_exists($this->_qrcodeFile))
//			@unlink($this->_qrcodeFile);
//		if(file_exists($this->_avatarFile))
//			@unlink($this->_avatarFile);
//		if(file_exists($this->_imgFile))
//			@unlink($this->_imgFile);

	}


    /**
     *RGB TO HEX
     *author cuitengwei
     *2014/1/16
     */
    public function rgb2html($r, $g=-1, $b=-1)
    {
        if (is_array($r) && sizeof($r) == 3)
            list($r, $g, $b) = $r;
        $r = intval($r); $g = intval($g);
        $b = intval($b);
        $r = dechex($r<0?0:($r>255?255:$r));
        $g = dechex($g<0?0:($g>255?255:$g));
        $b = dechex($b<0?0:($b>255?255:$b));
        $color = (strlen($r) < 2?'0':'').$r;
        $color .= (strlen($g) < 2?'0':'').$g;
        $color .= (strlen($b) < 2?'0':'').$b;
        return '#'.$color;
    }
    /**
     *HEX TO RGB
     *author cuitengwei
     *2014/1/16
     */
    public function html2rgb($color)
    {
        if ($color[0] == '#')
            $color = substr($color, 1);
        if (strlen($color) == 6)
            list($r, $g, $b) = array($color[0].$color[1],
                $color[2].$color[3],
                $color[4].$color[5]);
        elseif (strlen($color) == 3)
            list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
        else
            return false;
        $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
        return array($r, $g, $b);
    }
}
