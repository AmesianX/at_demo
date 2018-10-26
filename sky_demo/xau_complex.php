<?php
error_reporting(E_ALL^E_NOTICE);
require_once("phpchartdir.php");
require_once("function.php");
require_once("page.class.php");

$url=$_SERVER["REQUEST_URI"];
$dopost="";
if(strpos($url,"dopost")!== false) $dopost=$_REQUEST["dopost"];
$db = new MySQLi("localhost","root","123456","test");
$sqlall = "select count(*) from xau_sample";
$resultall = $db->query($sqlall);
$arr1 = $resultall->fetch_row();
$c = $arr1[0];
$pageindex=$_GET["page"]==null?0:$_GET["page"];
$page = new page($c,1);
$sql = "select * from xau_sample ".$page->limit;
$result = $db->query($sql);
$arr = $result->fetch_all();
$line_list= json_decode($arr[0][4]);
$id=$arr[0][1];
$filename1="png/complex/sample_close_simple.png";
$filename2="png/complex/sample_close_complex.png";

$head = "<a href='xau_sample.php'>sample</a> - <a href='xau_complex.php'>complex</a> - <a href='xau_svm.php'>svm</a>";
echo "<div align='center'>".$head."<br>".$page->fpage()."</div>";//显示分页信息
$chart_width=$chart_height=300;
$line_count=count($line_list);
$offset=0;
$line=[];
$c = new XYChart($chart_width, $chart_height+10, 0xFF000000);
$c->setPlotArea(0, 0, $chart_width, $chart_height, 0xffffff, -1, 0xFF000000, 0xFF000000, -1);
$c->yAxis()->setWidth(0);
for($i=0;$i<count($line_list);$i++) {
    $tmp_line=$line_list[$i];
    $size=count($tmp_line);
    for($n=0;$n<$size;$n++) {
        $line[$offset]=$tmp_line[$n];
        $offset++;
    }
    $offset--;
    $c->addAreaLayer($line);
}
show_png($c,$filename1);



if($dopost=="ajaxdel") {
	
        if(empty($id))
        {
                echo "fail";exit;
        }
        
        
}

if($dopost=="ajaxsave" && !empty($_POST['id'])) {
    $id=$_POST['id'];
    $begin_arr=$_POST['begin'];
    $end_arr=$_POST['end'];
    $type_arr=$_POST['type'];

    $sample=$detail_json="";
    $detail=array();
    for($i=0;$i<count($begin_arr);$i++) {
        $name=$begin_arr[$i]."+".$end_arr[$i]."+".$type_arr[$i];
        $sample.=$name."|";
        $tmp=array();
        $checkstr="";
        for($n=1;$n<16;$n++) {
            if(empty($_POST['W'.$n][$i])) continue;
            $tmp['W'.$n]=$_POST['W'.$n][$i];
            $checkstr.=$_POST['W'.$n][$i].',';
        }
        $detail[$name]=$tmp;
        $checkstr = substr($checkstr,0,strlen($checkstr)-1);
        $checkarr= explode(",", $checkstr);
        $begin=(int)$begin_arr[$i];
        foreach ($checkarr as $vl) {
            if($begin!==(int)$vl) {
                echo $name." 顺序不对！";
            } 
            $begin++;
        }//end foreach
    }
    $detail_json=json_encode($detail);
    $sample = substr($sample,0,strlen($sample)-1); 
    $sql = "INSERT INTO `xau_complex`
            (`orgin_id`,
             `sample`,
             `detail`)
            VALUES ('".$id."',
                    '$sample',
                    '$detail_json');";
    $result = $db->query($sql);
    $output_html="";
    if ($result ) {
        $output_html=$result;
    } else {   
        $sql = "UPDATE `xau_complex`
                SET `sample` = '$sample', 
                    `detail` = '$detail_json', 
                    `content` = '$content' 
                WHERE `orgin_id` = ".$id;
        $db->query($sql);
        //echo $sql;
    }

    
        
}




?>

<script>
//根据table  ID添加相应的行
	function addrow(){
		
		var myTable = document.getElementById("tb1");
		var newRow = tb1.insertRow(tb1.rows.length);
		newRow.align="center";
		var newTd1 = newRow.insertCell(0);
		newTd1.innerHTML=""+(tb1.rows.length-1); 
		var newTd2 = newRow.insertCell(1);
		newTd2.innerHTML="<input type=\"text\"   value=\"\"     class=\"txt\"   width=\"50px\" name=\"begin[]\"  />";
		var newTd3 = newRow.insertCell(2);
		newTd3.innerHTML="<input name=\"end[]\"  type=\"text\" value=\"\"   class=\"txt\"  />";
		var newTd4 = newRow.insertCell(3);
		newTd4.innerHTML="<input name=\"type[]\"  type=\"text\" value=\"3\"   class=\"txt\"    />";
		var newTd5 = newRow.insertCell(4);
		newTd5.innerHTML="<input name=\"mark[]\" type=\"text\" value=\"\"    class=\"txt\"  />";
		var newTd6 = newRow.insertCell(5);
		newTd6.innerHTML="<a href=\"javascript:void();\" onclick='{if(confirm(\"确定要删除?\")) {deleteCurrentRow(this,0); }else {}}'>删除</a>";

	}
//删除选中行tr
	function deleteCurrentRow(obj,id){  
		if(id!=0){

			if(ajaxdel(id)){
			
				var tr=obj.parentNode.parentNode;    
				var tbody=tr.parentNode; 
				tbody.removeChild(tr);   
			
			}else{
			
			
				alert("网络问题，删除失败，请联检查网络！");
			
			}

		}else{
		
			var tr=obj.parentNode.parentNode;   
			 
			var tbody=tr.parentNode;
			 
			tbody.removeChild(tr);   
		}
		
		
	}
//异步删除
	function  ajaxdel(id){
	
		var bol=false;
		
		$.ajax({
				type:"POST",
				async:false,
				url:"xau_complex.php",
				data:"id="+id+"&dopost=ajaxdel",
				success:function(data){
					alert(data);
					if(data=='success'){
						bol=true;
					}
					
				},error:function(data){
						bol=false;
				}});
				
				
		return bol;
	
	}
        

</script>
<style>
    td input { width:50px;}
    #long{ width:100px;}
</style>
<html>
    <body align='center'>
        <form id="sampleForm" action="xau_complex.php?id=<?php echo $id;?>&dopost=ajaxsave&page=<?php echo $pageindex; ?>" method="post" >
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <input type="hidden" name="page" value="<?php echo $pageindex; ?>" />
        <button name="button1" type="button" onclick="addrow();">添加<?php echo $id." - ".$line_count; ?></button> <button type="submit" >提交</button>
		<div class="list-div" align='center'>
			<table id="tb1" >
				<tr > 
					<th  align="center" height="30"></th>
					<th  align="center" height="30">开始</th>
					<th  height="30">结束</th>
					<th  height="30">类型</th>
					<th  height="30">W1</th>
                                        <th  height="30">W2</th>
                                        <th  height="30">W3</th>
                                        <th  height="30">W4</th>
                                        <th  height="30">W5</th>
                                        <th  height="30">W6</th>
                                        <th  height="30">W7</th>
                                        <th  height="30">W8</th>
                                        <th  height="30">W9</th>
                                        <th  height="30">W10</th>
                                        <th  height="30">W11</th>
                                        <th  height="30">W12</th>
                                        <th  height="30">W13</th>
                                        <th  height="30">W14</th>
                                        <th  height="30">W15</th>
					 <th  height="30">操作</th> 
				</tr>
				<?php 
                                        $new_line_list=[];
                                        $sql = "select * from xau_complex where orgin_id=".$id;
                                        $result = $db->query($sql);
                                        if($result)
                                            $samples = $result->fetch_all();
                                        //1+3+3|3+8+5|8+10+3
                                        if(!empty($samples )) {
                                            $sample_arr= explode("|", $samples[0][2]);
                                            $detail_arr= (array)json_decode($samples[0][3]);
                                            $i=0;
                                            foreach($sample_arr as $v) { 
                                                $i++; 
                                                $sample= explode("+", $v);
                                                $begin=$sample[0];
                                                $end=$sample[1];
                                                $type=$sample[2];
                                                if($begin<count($line_list) && $end<=count($line_list) ) {
                                                    $offset=0;
                                                    for($n=$begin-1;$n<=$end-1;$n++) {
                                                        foreach($line_list[$n] as $val) {
                                                            $new_line_list[$begin-1][$offset++]=$val;
                                                        }
                                                        $offset--;
                                                        if($n!=$begin-1) $new_line_list[$n]=true;
                                                    }

                                                }
                                                //1+3+3:JSON
                                                //array=['W1'=>'2,3','W1D'=>'123.2,123.4','W2'=>'3,4','W2D'=>'124.4,125.6']
                                                $detail = (array)$detail_arr[$v];
                                                
                                                for($m=1;$m<16;$m++) {
                                                    ${'W'.$m}=empty($detail)?"":$detail['W'.$m];
                                                }
 
                                        ?> 
				<tr align="center" >
                                    <td ><?php  echo $i ; ?></td>
                                        
					<input type="hidden" name="ids[]" value="<?php echo $i ?>" />
                                        <td ><input name="begin[]"  type="text"   value="<?php echo $begin ?>"  /></td>
					<td ><input name="end[]" type="text" value="<?php echo $end ?>"  /></td>
					<td ><input name="type[]" type="text" value="<?php echo $type ?>"  /></td>
					<td ><input name="W1[]" type="text" value="<?php echo $W1 ?>" id="long"  /></td>
                                        <td ><input name="W2[]" type="text" value="<?php echo $W2 ?>" id="long"  /></td>
                                        <td ><input name="W3[]" type="text" value="<?php echo $W3 ?>" id="long"  /></td>
                                        <td ><input name="W4[]" type="text" value="<?php echo $W4 ?>" id="long"  /></td>
                                        <td ><input name="W5[]" type="text" value="<?php echo $W5 ?>" id="long"  /></td>
                                        <td ><input name="W6[]" type="text" value="<?php echo $W6 ?>" id="long"  /></td>
                                        <td ><input name="W7[]" type="text" value="<?php echo $W7 ?>" id="long"  /></td>
                                        <td ><input name="W8[]" type="text" value="<?php echo $W8 ?>"  /></td>
                                        <td ><input name="W9[]" type="text" value="<?php echo $W9 ?>"  /></td>
                                        <td ><input name="W10[]" type="text" value="<?php echo $W10 ?>"  /></td>
                                        <td ><input name="W11[]" type="text" value="<?php echo $W11 ?>"  /></td>
                                        <td ><input name="W12[]" type="text" value="<?php echo $W12 ?>"  /></td>
                                        <td ><input name="W13[]" type="text" value="<?php echo $W13 ?>"  /></td>
                                        <td ><input name="W14[]" type="text" value="<?php echo $W14 ?>"  /></td>
                                        <td ><input name="W15[]" type="text" value="<?php echo $W15 ?>"  /></td>
                                        
					<td ><a href="javascript:void();" onclick='{if(confirm("确定要删除?")) {deleteCurrentRow(this,0 ); }else {}}'>删除</a> </td>
				</tr>
                                        <?php } }  ?>				
			</table>
		</div>	 
        
        <?php
        
            $offset=0;
            $c = new XYChart($chart_width, $chart_height+10, 0xFF000000);
            //$c->setPlotArea(55, 65, 350, 300, 0xffffff, -1, 0xc0c0c0, 0xc0c0c0, -1);
            $c->setPlotArea(0, 0, $chart_width, $chart_height, 0xffffff, -1, 0xFF000000, 0xFF000000, -1);
            $c->yAxis()->setWidth(0);
            $line=[];
            $new_line_set=[];
            $sn=1;
            $sample_pos="";
            for($i=0;$i<count($line_list);$i++) {
                if($new_line_list[$i]===true) continue;
                $tmp_line=$new_line_list[$i];
                $size=count($tmp_line);
                if($size===0) {
                    $size=count($line_list[$i]);
                    $tmp_line=$line_list[$i];
                } else {
                    echo '<a href="xau_complex_chart.php?id='.$id.'&sn='.$sn.'">'.$sn.'</a> - ';
                    $sample_pos.=$sn."|";
                }
                $sn++;
                for($n=0;$n<$size;$n++) {
                    $line[$offset]=$tmp_line[$n];
                    $offset++;
                }
                $offset--;
                array_push($new_line_set, $tmp_line);
                $c->addAreaLayer($line);
            }
            $sample_pos = substr($sample_pos,0,strlen($sample_pos)-1); 
            show_png($c,$filename2);
            $content = json_encode($new_line_set);
            $sql = "UPDATE `xau_complex`
                SET `content` = '$content', 
                    `sample_pos` = '$sample_pos' 
                WHERE `orgin_id` = ".$id;
            $db->query($sql);
        
        ?>
        
        </form>
        
        
    </body>
</html>
