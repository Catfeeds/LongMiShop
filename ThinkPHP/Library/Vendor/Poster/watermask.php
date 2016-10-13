<?php
/**
 * 加水印类，支持文字图片水印的透明度设置、水印图片背景透明。
 * 日期：2015-05-22
 * 作者：jamez
 * 使用：
 * $obj = new WaterMask($imgFileName); //实例化对象
 * $obj->waterType = 1; //类型：0为文字水印、1为图片水印
 * $obj->transparent = 45; //水印透明度
 * $obj->waterStr = 'www.jb51.net'; //水印文字
 * $obj->fontSize = 16; //文字字体大小
 * $obj->fontColor = array(255,0255); //水印文字颜色（RGB）
 * $obj->fontFile = = 'AHGBold.ttf'; //字体文件
 * $obj->output(); //输出水印图片文件覆盖到输入的图片文件
 */
class WaterMask {
    public $waterType = 1; //水印类型：0为文字水印、1为图片水印
    public $pos = 9; //水印位置
    public $transparent = 45; //水印透明度

    public $waterStr = '倚天盟'; //水印文字
    public $fontSize = 16; //文字字体大小
    public $fontColor = array(0,0,0); //水印文字颜色（RGB）
    public $fontFile = 'STSONG.TTF'; //字体文件

    public $waterImg = ''; //水印图片
    public $setting = ''; //水印图片

    public $srcImg = ''; //需要添加水印的图片
    private $im = ''; //图片句柄
    private $water_im = ''; //水印图片句柄
    private $srcImg_info = ''; //图片信息
    private $waterImg_info = ''; //水印图片信息
    private $str_w = ''; //水印文字宽度
    private $str_h = ''; //水印文字高度
    private $x = ''; //水印X坐标
    private $y = ''; //水印y坐标

    function __construct($img) { //析构函数
        $this->srcImg = $img;
        $this->fontFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/Public/font/MSYHBD.TTF'; //字体文件
    }
    private function imginfo() { //获取需要添加水印的图片的信息，并载入图片。
        $this->srcImg_info = getimagesize($this->srcImg);
        switch ($this->srcImg_info[2]) {
            case 3:
                $this->im = imagecreatefrompng($this->srcImg);
                break 1;
            case 2:
                $this->im = imagecreatefromjpeg($this->srcImg);
                break 1;
            case 1:
                $this->im = imagecreatefromgif($this->srcImg);
                break 1;
            default:
                return '原图片（'.$this->srcImg.'）格式不对('.json_encode($this->srcImg_info).')，只支持PNG、JPEG、GIF。';
        }

        return '';
    }
    private function waterimginfo() { //获取水印图片的信息，并载入图片。
        $this->waterImg_info = getimagesize($this->waterImg);
        switch ($this->waterImg_info[2]) {
            case 3:
                $this->water_im = imagecreatefrompng($this->waterImg);
                break 1;
            case 2:
                $this->water_im = imagecreatefromjpeg($this->waterImg);
                break 1;
            case 1:
                $this->water_im = imagecreatefromgif($this->waterImg);
                break 1;
            default:
                return '水印图片（'.$this->waterImg.'）格式不对('.json_encode($this->waterImg_info).')，只支持PNG、JPEG、GIF。';
        }

        return '';
    }
    private function waterpos() { //水印位置算法
        switch ($this->pos) {
            case 0: //随机位置
                $this->x = rand(0, $this->srcImg_info[0] - $this->waterImg_info[0]);
                $this->y = rand(0, $this->srcImg_info[1] - $this->waterImg_info[1]);
                break 1;
            case 1: //上左
                $this->x = 0;
                $this->y = 0;
                break 1;
            case 2: //上中
                $this->x = ($this->srcImg_info[0] - $this->waterImg_info[0]) / 2;
                $this->y = 0;
                break 1;
            case 3: //上右
                $this->x = $this->srcImg_info[0] - $this->waterImg_info[0];
                $this->y = 0;
                break 1;
            case 4: //中左
                $this->x = 0;
                $this->y = ($this->srcImg_info[1] - $this->waterImg_info[1]) / 2;
                break 1;
            case 5: //中中
                $this->x = ($this->srcImg_info[0] - $this->waterImg_info[0]) / 2;
                $this->y = ($this->srcImg_info[1] - $this->waterImg_info[1]) / 2;
                break 1;
            case 6: //中右
                $this->x = $this->srcImg_info[0] - $this->waterImg_info[0];
                $this->y = ($this->srcImg_info[1] - $this->waterImg_info[1]) / 2;
                break 1;
            case 7: //下左
                $this->x = 0;
                $this->y = $this->srcImg_info[1] - $this->waterImg_info[1];
                break 1;
            case 8: //下中
                $this->x = ($this->srcImg_info[0] - $this->waterImg_info[0]) / 2;
                $this->y = $this->srcImg_info[1] - $this->waterImg_info[1];
                break 1;
            case 9: //二维码下中中
                if($this->setting['qrcode']['left'])
                    $this->x = $this->setting['qrcode']['left'];
                else
                    $this->x = ($this->srcImg_info[0] - $this->waterImg_info[0]) / 2;
                if($this->setting['qrcode']['top'])
                    $this->y = $this->setting['qrcode']['top'];
                else
                    $this->y = $this->srcImg_info[1] - $this->waterImg_info[1] - 150;
                break 1;
            case 10: //头像上左
                if($this->setting['avatar']['left'])
                    $this->x = $this->setting['avatar']['left'];
                else
                    $this->x = 70;
                if($this->setting['avatar']['top'])
                    $this->y = $this->setting['avatar']['top'];
                else
                    $this->y = 40;
                break 1;
            case 11: //姓名
                $this->fontFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/Public/font/MSYHBD.TTF'; //字体文件
                if($this->setting['name']['left'])
                    $this->x = $this->setting['name']['left'];
                else
                    $this->x = 230;
                if($this->setting['name']['top'])
                    $this->y = $this->setting['name']['top'];
                else
                    $this->y = 60;
                break 1;
            case 12: //品牌名称
                $this->fontFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/Public/font/MSYHBD.TTF'; //字体文件
                if($this->setting['corp']['left'])
                    $this->x = $this->setting['corp']['left'];
                else
                    $this->x = 230;
                if($this->setting['corp']['top'])
                    $this->y = $this->setting['corp']['top'];
                else
                    $this->y = 120;
                break 1;
            case 13: //品牌LOGO
                if($this->setting['logo']['left'])
                    $this->x = $this->setting['logo']['left'];
                else
                    $this->x = 230;
                if($this->setting['logo']['top'])
                    $this->y = $this->setting['logo']['top'];
                else
                    $this->y = 200;
                break 1;
            case 14: //手机
                $this->fontFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/Public/font/MSYHBD.TTF'; //字体文件
                if($this->setting['phone']['left'])
                    $this->x = $this->setting['phone']['left'];
                else
                    $this->x = 230;
                if($this->setting['phone']['top'])
                    $this->y = $this->setting['phone']['top'];
                else
                    $this->y = 60;
                break 1;
            case 15: //万位
                $this->fontFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/Public/font/MSYHBD.TTF'; //字体文件
                if($this->setting['number1']['left'])
                    $this->x = $this->setting['number1']['left'];
                else
                    $this->x = 134;
                if($this->setting['number1']['top'])
                    $this->y = $this->setting['number1']['top'];
                else
                    $this->y = 965;
                break 1;
            case 16: //千位
                $this->fontFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/Public/font/MSYHBD.TTF'; //字体文件
                if($this->setting['number2']['left'])
                    $this->x = $this->setting['number2']['left'];
                else
                    $this->x = 221;
                if($this->setting['number2']['top'])
                    $this->y = $this->setting['number2']['top'];
                else
                    $this->y = 965;
                break 1;
            case 17: //百位
                $this->fontFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/Public/font/MSYHBD.TTF'; //字体文件
                if($this->setting['number3']['left'])
                    $this->x = $this->setting['number3']['left'];
                else
                    $this->x = 307;
                if($this->setting['number3']['top'])
                    $this->y = $this->setting['number3']['top'];
                else
                    $this->y = 965;
                break 1;

            case 18: //十位
                $this->fontFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/Public/font/MSYHBD.TTF'; //字体文件
                if($this->setting['number4']['left'])
                    $this->x = $this->setting['number4']['left'];
                else
                    $this->x = 389;
                if($this->setting['number4']['top'])
                    $this->y = $this->setting['number4']['top'];
                else
                    $this->y = 965;
                break 1;
            case 19: //个位
                $this->fontFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/Public/font/MSYHBD.TTF'; //字体文件
                if($this->setting['number5']['left'])
                    $this->x = $this->setting['number5']['left'];
                else
                    $this->x = 476;
                if($this->setting['number5']['top'])
                    $this->y = $this->setting['number5']['top'];
                else
                    $this->y = 965;
                break 1;
            default: //下右
                $this->x = $this->srcImg_info[0] - $this->waterImg_info[0];
                $this->y = $this->srcImg_info[1] - $this->waterImg_info[1];
                break 1;
        }
    }
    private function waterimg() {
//        if ($this->srcImg_info[0] <= $this->waterImg_info[0] || $this->srcImg_info[1] <= $this->waterImg_info[1]){
//            return '水印比原图大！';
//        }
        $this->waterpos();
        $cut = imagecreatetruecolor($this->waterImg_info[0],$this->waterImg_info[1]);
        imagecopy($cut,$this->im,0,0,$this->x,$this->y,$this->waterImg_info[0],$this->waterImg_info[1]);
        $pct = $this->transparent;
        imagecopy($cut,$this->water_im,0,0,0,0,$this->waterImg_info[0],$this->waterImg_info[1]);
        imagecopymerge($this->im,$cut,$this->x,$this->y,0,0,$this->waterImg_info[0],$this->waterImg_info[1],$pct);
        return '';
    }
    private function waterstr() {
        $rect = imagettfbbox($this->fontSize,0,$this->fontFile,$this->waterStr);
        $w = abs($rect[2]-$rect[6]);
        $h = abs($rect[3]-$rect[7]);
        $fontHeight = $this->fontSize;
        $this->water_im = imagecreatetruecolor($w, $h);
        imagealphablending($this->water_im,false);
        imagesavealpha($this->water_im,true);
        $white_alpha = imagecolorallocatealpha($this->water_im,255,255,255,127);
        imagefill($this->water_im,0,0,$white_alpha);
        $color = imagecolorallocate($this->water_im,$this->fontColor[0],$this->fontColor[1],$this->fontColor[2]);
        imagettftext($this->water_im,$this->fontSize,0,0,$this->fontSize,$color,$this->fontFile,$this->waterStr);
        $this->waterImg_info = array(0=>$w,1=>$h);
        return $this->waterimg();
    }
    function output() {
        if(!file_exists($this->srcImg)){

            return '"'.$this->srcImg.'" 源文件不存在！';
        }
        $msg = $this->imginfo();
        if($msg)
            return $msg;
        if ($this->waterType == 0) {
            $msg = $this->waterstr();
            if($msg)
                return $msg;
        }else {
            $msg = $this->waterimginfo();
            if($msg)
                return $msg;
            $msg = $this->waterimg();
            if($msg)
                return $msg;
        }
        switch ($this->srcImg_info[2]) {
            case 3:
                imagepng($this->im,$this->srcImg);
                break 1;
            case 2:
                imagejpeg($this->im,$this->srcImg);
                break 1;
            case 1:
                imagegif($this->im,$this->srcImg);
                break 1;
            default:
                return '添加水印失败！';
                break;
        }
        imagedestroy($this->im);
        imagedestroy($this->water_im);

        return '';
    }

    /**
     * 获取远程图片保存到本地
     * @param string $src 原图链接
     * @param string $filename 保存文件
     * @return bool
     */
    public function getImg($src ='', $filename ='')
    {
        if(!$src || !$filename)return false;
        if(strexists($src,'http://') || strexists($src,'https://')) {
            $data = file_get_contents($src);
            if (is_error($data)) {
                return false;
            }
            mkdirs(dirname($filename));
            file_put_contents($filename,$data);
        }
        else
            copy($src, $filename);
        return is_file($filename);


    }

    /**
     * @param $src_img 原图
     * @param $thumb 缩略图
     * @param int $dst_w
     * @param int $dst_h
     * @return mixed
     */
    public function resize($src_img, $thumb, $dst_w=250,$dst_h=250)
    {

        if(!$this->getImg($src_img,$thumb))return false;

        list($src_w,$src_h)=getimagesize($thumb); // 获取原图尺寸
        if($src_w <= $dst_w || $src_h <= $dst_h)
            return $thumb;
        $dst_scale = $dst_h/$dst_w; //目标图像长宽比
        $src_scale = $src_h/$src_w; // 原图长宽比
        if($src_scale>=$dst_scale)
        {
// 过高
            $w = intval($src_w);
            $h = intval($dst_scale*$w);
            $x = 0;
            $y = ($src_h - $h)/3;
        }
        else
        {
// 过宽
            $h = intval($src_h);
            $w = intval($h/$dst_scale);
            $x = ($src_w - $w)/2;
            $y = 0;
        }
// 剪裁
        $thumb_info = getimagesize($thumb);
        switch ($thumb_info[2]) {
            case 3:
                $source=imagecreatefrompng($thumb);
                break 1;
            case 2:
                $source=imagecreatefromjpeg($thumb);
                break 1;
            case 1:
                $source=imagecreatefromgif($thumb);
                break 1;
            default:
                return '原图片（'.$thumb.'）格式不对('.json_encode($thumb_info).')，只支持PNG、JPEG、GIF。';
        }
        $croped=imagecreatetruecolor($w, $h);
        imagecopy($croped,$source,0,0,$x,$y,$src_w,$src_h);
// 缩放
        $scale = $dst_w/$w;
        $target = imagecreatetruecolor($dst_w, $dst_h);
        $final_w = intval($w*$scale);
        $final_h = intval($h*$scale);
        imagecopyresampled($target,$croped,0,0,0,0,$final_w,$final_h,$w,$h);

// 保存
        switch ($thumb_info[2]) {
            case 3:
                imagepng($target, $thumb);
                break 1;
            case 2:
                imagejpeg($target, $thumb);
                break 1;
            case 1:
                imagegif($target, $thumb);
                break 1;
            default:
                return '原图片（'.$thumb.'）格式不对('.json_encode($thumb_info).')，只支持PNG、JPEG、GIF。';
                break;
        }
        imagedestroy($target);
        return $thumb;
    }

    /**
     * 生成缩略图
     * @author yangzhiguo0903@163.com
     * @param string     源图绝对完整地址{带文件名及后缀名}
     * @param string     目标图绝对完整地址{带文件名及后缀名}
     * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
     * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
     * @param int        是否裁切{宽,高必须非0}
     * @param int/float  缩放{0:不缩放, 0<this<1:缩放到相应比例(此时宽高限制和裁切均失效)}
     * @return boolean
     */
    function img2thumb($src_img, $dst_img, $width = 75, $height = 75, $cut = 0, $proportion = 0)
    {
        if(!is_file($src_img))
        {
            return false;
        }


        $ot = $this->fileext($dst_img);
        $otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
        $srcinfo = getimagesize($src_img);
        $src_w = $srcinfo[0];
        $src_h = $srcinfo[1];
        $type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
        $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);

        $dst_h = $height;
        $dst_w = $width;
        $x = $y = 0;

        /**
         * 缩略图不超过源图尺寸（前提是宽或高只有一个）
         */
        if(($width> $src_w && $height> $src_h) || ($height> $src_h && $width == 0) || ($width> $src_w && $height == 0))
        {
            $proportion = 1;
        }
        if($width> $src_w)
        {
            $dst_w = $width = $src_w;
        }
        if($height> $src_h)
        {
            $dst_h = $height = $src_h;
        }

        if(!$width && !$height && !$proportion)
        {
            return false;
        }
        if(!$proportion)
        {
            if($cut == 0)
            {
                if($dst_w && $dst_h)
                {
                    if($dst_w/$src_w> $dst_h/$src_h)
                    {
                        $dst_w = $src_w * ($dst_h / $src_h);
                        $x = 0 - ($dst_w - $width) / 2;
                    }
                    else
                    {
                        $dst_h = $src_h * ($dst_w / $src_w);
                        $y = 0 - ($dst_h - $height) / 2;
                    }
                }
                else if($dst_w xor $dst_h)
                {
                    if($dst_w && !$dst_h)  //有宽无高
                    {
                        $propor = $dst_w / $src_w;
                        $height = $dst_h  = $src_h * $propor;
                    }
                    else if(!$dst_w && $dst_h)  //有高无宽
                    {
                        $propor = $dst_h / $src_h;
                        $width  = $dst_w = $src_w * $propor;
                    }
                }
            }
            else
            {
                if(!$dst_h)  //裁剪时无高
                {
                    $height = $dst_h = $dst_w;
                }
                if(!$dst_w)  //裁剪时无宽
                {
                    $width = $dst_w = $dst_h;
                }
                $propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
                $dst_w = (int)round($src_w * $propor);
                $dst_h = (int)round($src_h * $propor);
                $x = ($width - $dst_w) / 2;
                $y = ($height - $dst_h) / 2;
            }
        }
        else
        {
            $proportion = min($proportion, 1);
            $height = $dst_h = $src_h * $proportion;
            $width  = $dst_w = $src_w * $proportion;
        }

        $src = $createfun($src_img);
        $dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);

        if(function_exists('imagecopyresampled'))
        {
            imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        else
        {
            imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        $otfunc($dst, $dst_img);
        imagedestroy($dst);
        imagedestroy($src);
        return true;
    }

    public function fileext($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }
}
