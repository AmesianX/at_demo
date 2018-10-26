 <?php
    require_once("phpchartdir.php");
    require_once("function.php");
    require_once("page.class.php");
    $db = new MySQLi("localhost","root","123456","test");
    $sqlall = "select count(*) from xau_sample";
    $resultall = $db->query($sqlall);
    $arr1 = $resultall->fetch_row();//获取一个数组  只有一个值的数组
    $c = $arr1[0];//用一个变量获取这个数组的值
    $page = new page($c,100);//一共多少条 每页显示多少条
    $sql = "select *  from xau_sample  order by id desc " .$page->limit;
    $result = $db->query($sql);
    

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h1 align="center">lib svm predict</h1>
    <div>
  <?php
    echo "<div align='center'>{$page->fpage()}</div>";//显示分页信息
?>
    <table align="center" width="60%" style="text-align:center;" border="1">
        <tr>
            <td>org_class</td>
            <td>image</td>
            <td>predict_class</td>
            <td>result</td>
        </tr>
<?php

    if($result){
        $arr = $result->fetch_all();
        $sample_image=[];
        $model = new SVMModel();
        $model->load(dirname(__FILE__) . '/model.svm');
        $count_all=0;
        $count_predict_right=0;
        foreach($arr as $v){
            $id=$v[0];
            $orgin_id=$v[1];
            $sample=$v[2];
            $detail=$v[3];
            $content=$v[4];
            $sample_pos=$v[5];
            
            $sample_arr=explode('|',$sample);
            $line_arr= json_decode($content);
            $sample_pos_arr=explode('|',$sample_pos);
            for($i=0;$i<count($sample_pos_arr);$i++) {
                $pos=$sample_pos_arr[$i];
                $count_all++;
                $stype_arr = explode('+',$sample_arr[$i]);
                $type = end($stype_arr);
                $sample_image[$type][]=$line_arr[$pos-1];
                if(predict_line($model,$i,$type,$line_arr[$pos-1],$orgin_id)) ++$count_predict_right;
                //echo show_svm_simple_png($line_arr[$pos-1],$i,$sample_arr[$i]);
            }
            

        }
        
        echo "<tr><td>".round($count_predict_right/$count_all,2)."</td></tr>";
        

    }
?>
    </table>
    <br />
<?php
    echo "<div align='center'>{$page->fpage()}</div>";//显示分页信息
?>
    </div>
</body>
</html>

<?php

function predict_line($model,$sn,$key,$value,$orgin_id)
{
    //$class_arr=[''=>'-1','3f'=>'+1','3r'=>'-1','5r'=>'-1','5f'=>'-1','3p'=>'-1','3t'=>'-1','3rt'=>'-1','7r'=>'-1','7f'=>'-1','5t'=>'-1'];
    $class_arr=['','3f','3r','5r','5f','3p','3t','3rt','7r','7f','5t','5p'];
    echo"<tr>";
    echo"<td>".$key."</td>";
    echo"<td>";
    echo show_svm_simple_png($value,$sn,$key);
    echo"</td>";
    //归一化
    $td=array();
    $MinValue=min($value);
    $MaxValue=max($value);
    $lower=-1;
    $upper=1;
    foreach ($value as $kl => $vl) {
        $one = $lower+($upper-$lower)*($vl-$MinValue)/($MaxValue-$MinValue);
        array_push($td,$one);
    }            
    $result = $model->predict($td);
    $predict_cls=$class_arr[$result];
    echo"<td>".$predict_cls." - ".$orgin_id."</td>";
    if($key==$predict_cls) echo"<td>正确</td>";
    
    echo"</tr>";
    if($key==$predict_cls) return true;
    
    return false;
}


function predict_del()
{
    $model = new SVMModel();
    $model->load(dirname(__FILE__) . '/model.svm');
    //$class_arr=[''=>'-1','3f'=>'+1','3r'=>'-1','5r'=>'-1','5f'=>'-1','3p'=>'-1','3t'=>'-1','3rt'=>'-1','7r'=>'-1','7f'=>'-1','5t'=>'-1'];
    $class_arr=['','3f','3r','5r','5f','3p','3t','3rt','7r','7f','5t'];
    foreach ($sample_image as $key => $value) {
        echo"<tr>";
        echo"<td>".$key."</td>";

        foreach ($value as $k => $v) {
            echo"<td>";
            echo show_svm_simple_png($v,$k,$key);
            echo"</td>";
            //归一化
            $td=array();
            $MinValue=min($v);
            $MaxValue=max($v);
            $lower=-1;
            $upper=1;
            foreach ($v as $kl => $vl) {
                $one = $lower+($upper-$lower)*($vl-$MinValue)/($MaxValue-$MinValue);
                array_push($td,$one);
            }            
            $result = $model->predict($td);
            echo"<td>".$class_arr[$result]."</td>";
        }

        echo"</tr>";
    }
}

?>