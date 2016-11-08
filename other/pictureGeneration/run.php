<?php
header("content-type:image/jpeg");
$name = $_GET['name']?$_GET['name']:"马大哈";
$id = $_GET['id']?$_GET['id']:"我喜欢你";
$im = imagecreatetruecolor(640, 889);
if($id == '2'){ 
$bg = imagecreatefromjpeg('toutu2.jpg');
}
else if($id == '1'){ 
$bg = imagecreatefromjpeg('toutu1.jpg');
}
else{ 
 echo '条件不满足'; 
}
imagecopy($im,$bg,0,0,0,0,640,889);
imagedestroy($bg);
$black = imagecolorallocate($im, 85, 85, 85);
$text = $name;
$font = '../../zb/font/xjl.ttf';

if($id == '2'){ 
imagettftext($im, 23, 2, 100, 310, $black, $font, $text);
imagettftext($im, 22, -2, 400, 300, $black, $font, $text);
imagettftext($im, 24, 0, 343, 668, $black, $font, $text);
imagettftext($im, 24, 0, 106, 750, $black, $font, $text);
}
else if($id == '1'){ 
imagettftext($im, 28, 52.5, 210, 347, $black, $font, $text);
imagettftext($im, 28, 21, 480, 200, $black, $font, $text);
imagettftext($im, 28, -20, 458, 478, $black, $font, $text);
imagettftext($im, 28, 5, 30, 720, $black, $font, $text);
imagettftext($im, 28, 5, 495, 785, $black, $font, $text);
}
else{ 
 echo '条件不满足'; 
}



imagejpeg($im);
imagedestroy($im);
?>