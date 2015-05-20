<?php
$str = 'abcdefghijklmnopqrstuvwxyz0123456789';
$code = "";
$strLength = strlen($str);
for($i=0;$i<4;$i++){
    $code .= $str[rand(0,$strLength-1)];
}
session_start();
$_SESSION["code"] = $code;
//header("Content-type: image/jpeg");
$im = imagecreate(100,27);
$black = imageColorAllocate($im, 0,0,0);
$gray = imageColorAllocate($im, 200,200,200);
$bg = imageColorAllocate($im, rand(200,233), rand(200,233), rand(200,233));
imagefilledrectangle($im,0,27,100,0,$bg);  
for ($i=0;$i<6;$i++) {  
    $color = imagecolorallocate($im,rand(0,156),rand(0,156),rand(0,156));  
    imageline($im,rand(0,100),rand(0,27),rand(0,100),rand(0,27),$color);  
} 

for ($i=0;$i<100;$i++) {  
    $color = imagecolorallocate($im,rand(200,255),rand(200,255),rand(200,255));  
    imagestring($im,rand(1,5),rand(0,100),rand(0,27),'*',$color);  
}

$_x = 100/4;
for ($i=0;$i<4;$i++) {  
    $fontcolor = imagecolorallocate($im,rand(0,156),rand(0,156),rand(0,156));  
    imagettftext($im,20,rand(-30,30),$_x*$i+rand(1,5),27 / 1.4,$fontcolor,'Mono.ttf',$code[$i]); 
}
imagejpeg($im);
imageDestroy($im);
?>
