<?php

// Check we have the appropriate variable data
// 確認我們有適當的變數資料
$vote = $_POST['vote'];

if (empty($vote)) {
    echo '<p>You have not voted for a politician.</p>';
    exit;
}

/*******************************************
  Database query to get poll info
  取得投票資訊的資料庫指令
*******************************************/

// Log in to database
$db = new mysqli('localhost', 'poll', 'poll', 'poll');
// $db = new mysqli('tester.cynw5brug1nx.us-east-1.rds.amazonaws.com', 'tester_admin', 'pekoemini!!!!!', 'poll');
if (mysqli_connect_errno()) {
    echo '<p>Error: Could not connect to database.<br/>
    Please try again later.</p>';
    exit;
}

// 加入使用者投的票
// Add the user's vote
$v_query = "UPDATE poll_results
            SET num_votes = num_votes + 1
            WHERE candidate = ?";
$v_stmt = $db->prepare($v_query);
$v_stmt->bind_param('s', $vote);
$v_stmt->execute();
$v_stmt->free_result();

// Get current results of poll
// 取得目前的投票結果
$r_query = "SELECT candidate, num_votes FROM poll_results";
$r_stmt = $db->prepare($r_query);
$r_stmt->execute();
$r_stmt->store_result();
$r_stmt->bind_result($candidate, $num_votes);
$num_candidates = $r_stmt->num_rows;

// Calculate total number of votes so far
// 計算到目前為止的總票數
$total_votes = 0;

while ($r_stmt->fetch())
{
    $total_votes += $num_votes;
}

$r_stmt->data_seek(0);

/*******************************************
  Initial calculations for graph
  開始計算繪圖數據
*******************************************/
// Set up constants
// 設定常數
putenv('GDFONTPATH=/usr/share/fonts/truetype/dejavu'); 

$width = 500;       // width of image in pixels 圖像的像素寬度
$left_margin = 50;  // space to leave on left of graph 在圖表左邊預留的空間
$right_margin = 50; // space to leave on right of graph 在圖表右邊預留的空間
$bar_height = 40;
$bar_spacing = $bar_height/2;
$font_name = 'c:/windows/fonts/arial.ttf';
// $font_name = 'DejaVuSans';
$title_size = 16;   // in points 單位為點數
$main_size = 12;    // in points 單位為點數
$smail_size = 12;   // in points 單位為點數
$text_indent = 10;  // position for text labels from edge of image 文字標籤距離圖像邊緣的位置

// Set up initial point to draw from 
// 設定繪製的初始點
$x = $left_margin + 60; // place to draw baseline of the graph 繪製圖表基準線的地方
$y = 50;                // ditto 同上
$bar_unit = ($width-($x+$right_margin)) / 100;    // one "point" on the graph 圖表中的一個"點"

// Calculate height of graph - bars plus gaps plus some margin
$height = $num_candidates * ($bar_height + $bar_spacing) + 50;

/*******************************************
  Set up base image
  設定基本圖像
*******************************************/
// Create a blank canvas
// 建立空畫布
$im = imagecreatetruecolor($width, $height);

// Allocate colors
// 配置顏色
$white = imagecolorallocate($im,255,255,255);
$blue = imagecolorallocate($im,0,64,128);
$black = imagecolorallocate($im,0,0,0);
$pink = imagecolorallocate($im,255,78,243);

$text_color = $black;
$percent_color = $black;
$bg_color = $white;
$line_color = $black;
$bar_color = $black;
$number_color = $pink;

// Create "canvas" to draw on
// 建立要用來繪製的"畫布"
imagefilledrectangle($im, 0, 0, $width, $height, $bg_color);

// Draw outline around canvas
// 繪製畫布周圍的輪廓
imagerectangle($im, 0, 0, $width-1, $height-1, $line_color);

// Add title
// 添加標題
$title = 'Poll Results';
$title_dimensions = imagettfbbox($title_size, 0, $font_name, $title);
$title_length = $title_dimensions[2] - $title_dimensions[0];
$title_height = abs($title_dimensions[7] - $title_dimensions[1]);
$title_above_line = abs($title_dimensions[7]);
$title_x = ($width-$title_length)/2; // center it in x 在x上將它置中
$title_y = ($y - $title_height)/2 + $title_above_line; // center in y gap 在y的間距中將它置中

imagettftext($im, $title_size, 0, $title_x, $title_y,
            $text_color, $font_name, $title);

// Draw a base line from a little above first bar location
// to a little below last
// 從第一個長條的位置上面一點點的地方繪製基準線
// 到最後一個長條下面一點點的地方
imageline($im, $x, $y-5, $x, $height-15, $line_color);

/*******************************************
  Draw data into graph
  在圖表中繪製資料
*******************************************/
// Get each line of DB data and draw corresponding bars
// 取出各個DB資料，並畫出對應的長條

while ($r_stmt->fetch()) {
    if ($total_votes > 0) {
        $percent = intval(($num_votes/$total_votes)*100);
    } else {
        $percent = 0;
    }


// Display percent for this value
// 顯示這個值的百分比
$percent_dimensions = imagettfbbox($main_size, 0, $font_name, $percent.'%');

$percent_length = $percent_dimensions[2] = $percent_dimensions[0];

imagettftext($im, $main_size, 0, $width-$percent_length-$text_indent,
            $y+($bar_height/2), $percent_color, $font_name, $percent.'%');

// Length of bar for this value
// 這個值的長條的長度
$bar_length = $x + ($percent * $bar_unit);

// Draw bar for this value
// 畫出這個值的長條
imagefilledrectangle($im, $x, $y-2, $bar_length, $y+$bar_height, $bar_color);

// Draw title for this value
// 畫出這個值的標題
imagettftext($im, $main_size, 0, $text_indent, $y+($bar_height/2),
            $text_color, $font_name, $candidate);

// Draw outline showing 100%
// 畫出顯示100%的輪廓
imagerectangle($im, $bar_length+1, $y-2,
            ($x+(100*$bar_unit)), $y+$bar_height, $line_color);

// Display numbers
// 顯示數字
imagettftext($im, $smail_size, 0, $x+(100*$bar_unit)-50, $y+($bar_height/2),
            $number_color, $font_name, $num_votes.'/'.$total_votes);

// Move down to next bar
// 下移到下一個長條
$y=$y+($bar_height+$bar_spacing);

}
/*******************************************
  Display image
  顯示圖像
*******************************************/
header('Content-type: image/png');
imagepng($im);

/*******************************************
  Clean up
*******************************************/
$r_stmt->free_result();
$db->close();
imagedestroy($im);
?>