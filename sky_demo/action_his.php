<?php
//持续时长1秒以上的记录
//SELECT *  FROM 
//(
//SELECT TIMESTAMPDIFF(SECOND, open_time, close_time) AS co,id,open_time,close_time,open_y,close_y,act,act_exe,reward FROM action_his 
//) AS t
//WHERE t.co>1 ORDER BY open_time;
//
//最近1分钟内奖励正负对比
//SELECT a.*,b.* FROM 
//(
//SELECT COUNT(*) AS 'reward>0' FROM action_his WHERE reward>0 AND open_time>=DATE_SUB(NOW(),INTERVAL 1 MINUTE)
//) AS a,
//(
//SELECT COUNT(*) AS 'reward<0' FROM action_his WHERE reward<0 AND open_time>=DATE_SUB(NOW(),INTERVAL 1 MINUTE)
//) AS b
//累计奖励
//SELECT SUM(reward) FROM action_his



$act = $_POST["act"];
$act_exe = $_POST["act_exe"];
$open_x = $_POST["open_x"];
$open_y = $_POST["open_y"];
$close_x = $_POST["close_x"];
$close_y = $_POST["close_y"];
$reward = $_POST["reward"];
$close_time = $_POST["close_time"];
$open_time = $_POST["open_time"];

$link = @mysqli_connect('192.168.0.10','root','123456');
if (!$link) {
    exit('error('.mysqli_connect_errno().'):'.mysqli_connect_error());
    //die
}
if (!mysqli_select_db($link,'autotrade')) {
    echo 'error('.mysqli_errno($link).'):'.mysqli_error($link);
    mysqli_close($link);
    die;
}
mysqli_set_charset($link,'utf8');

$sql = "INSERT INTO `autotrade`.`action_his`
            (`act`,
             `act_exe`,
             `open_x`,
             `open_y`,
             `close_x`,
             `close_y`,
             `reward`,
             `close_time`,
             `open_time`)
            VALUES ('$act',
                    '$act_exe',
                    $open_x,
                    $open_y,
                    $close_x,
                    $close_y,
                    $reward,
                    '$close_time',
                    '$open_time');";
$result = mysqli_query($link,$sql);
$output_html="";
if ($result ) {
$output_html=mysqli_insert_id($link);
$output_html=$sql;
} else {
$output_html="error!".$sql;
}
echo $output_html;
mysqli_close($link);
