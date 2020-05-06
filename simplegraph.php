<?php
// set up image canvas
// 設定畫布圖像
$height = 200;
$width = 200;
$im = imagecreatetruecolor($width, $height);
$white = imagecolorallocate ($im, 255, 255, 255);
$blue = imagecolorallocate ($im, 0, 0, 255);

// draw on image
// 繪製圖像
imagefill($im, 0, 0, $blue);
imageline($im, 0, 0, $width, $height, $white);
imagestring($im, 4, 50, 150, 'Sales', $white);

// output image
// 輸出圖像
header('Content-type: image/png');
imagepng ($im);

// clean up
// 清理
imagedestroy($im);
?>