<?php
// Check we have the appropriate variable data
// (the variables are button-text and button-color)
// 確認我們有適當的變數資料
// (變數是button-text與button-color)
$button_text = $_POST['button_text'];
$button_color = $_POST['button_color'];

if (empty($button_text) || empty($button_color)) {
    echo '<p>Could not create image: form not filled out correctly.</p>';
    exit;
}
//  Create an image using the right color of button, and check the size
// 使用正確的按鈕顏色來產生圖像，並檢查大小
$im = imagecreatefrompng($button_color.'-button.png');

$width_image = imagesx($im);
$height_image = imagesy($im);

// Our images need an 18 pixel margin in from the edge of the image
// 我們的圖像需要18個像素的邊距
$width_image_wo_margins = $width_image - (2 * 18);
$height_image_wo_margins = $height_image - (2 * 18);

// Tell GD2 where the font you want to use resides
// 告訴GD2你想要使用的字型放在哪裡

// For Windows, use:
// 在Windows，使用：
putenv('GDFONTPATH=C:\WINDOWS\Fonts');

// For UNIX, use the full path to the font folder.
// In this example we're using the DejaVu font family:
// 在UNIX使用字型資料夾的完整路徑
// 在這個範例中，我們使用DejaVu字型家族
// putenv('GDFONTPATH=/usr/share/fonts/truetype/dejavu');

// $font_name = 'DejaVuSans';
$font_name = "c:/windows/fonts/arial.ttf";
// Work out if the font size will fit and make it smaller until it does
// Start out with the biggest size that will reasonably fit on our buttons
// 試試看字型的大小是否符合，必要時將它縮小，直到符合
// 一開始先用可能符合按鈕的最大大小
$font_size = 33;

do {
    $font_size--;

    // Find out the size of the text at that font size
    // 尋找哪一個字型大小的文字大小
    $bbox = imagettfbbox($font_size, 0, $font_name, $button_text);
             
    $right_text = $bbox[2]; // right co-ordinate 右座標
    $left_text = $bbox[0]; // left co-ordinate 左座標
    $width_text = $right_text - $left_text; // how wide is it? 它有多寬?
    $height_text = abs($bbox[7] - $bbox[1]); // how tall is it? 它有多高?

} while ($font_size > 8 &&
($height_text > $width_image_wo_margins ||
$width_text > $width_image_wo_margins)
);

if ($height_text > $width_image_wo_margins ||
$width_text > $width_image_wo_margins) {
    // no readable font size will fit on button
    // 沒有可讀取的字型大小可放入按鈕
    echo '<p>Text given will not fit no button.</p>';
} else {
    // We have found a font size that will fit.
    // Now work out where to put it.
    // 我們已經找出符合的字型大小了
    // 接著來找出要將它放在哪裡
    
    $text_x = $width_image / 2.0 - $width_text / 2.0;
    $text_y = $height_image / 2.0 - $height_text / 2.0;

    if ($left_text < 0) {
        $text_x += abs($left_text); // add factor for left overhang 加上調整左邊的因子
    }

    $above_line_text = abs($bbox[7]); // how far above the baseline? 在基準線上面離它多遠?
    $text_y += $above_line_text; // add baseline factor 加入基準線因子

    $text_y -= 2; // 調整因子，來調整模板形狀

    $white = imagecolorallocate ($im, 255, 255, 255);

    imagettftext ($im, $font_size, 0, $text_x, $text_y, $white, $font_name, $button_text);

    header('Content-type: image/png');
    imagepng($im);
}
?>