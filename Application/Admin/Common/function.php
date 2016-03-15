<?php
/**
 * 公用方法
 * User: iredbaby
 * Date: 1/14/16
 * Time: 09:01 AM
 */

//处理方法
function rmdirr($dirname) {
    if (!file_exists($dirname)) {
        return false;
    }
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
    $dir = dir($dirname);
    if ($dir) {
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            //递归
            rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
        }
    }
}

//获取文件修改时间
function getfiletime($file, $DataDir) {
    $a = filemtime($DataDir . $file);
    $time = date("Y-m-d H:i:s", $a);
    return $time;
}

//获取文件的大小
function getfilesize($file, $DataDir) {
    $perms = stat($DataDir . $file);
    $size = $perms['size'];
    // 单位自动转换函数
    $kb = 1024;         // Kilobyte
    $mb = 1024 * $kb;   // Megabyte
    $gb = 1024 * $mb;   // Gigabyte
    $tb = 1024 * $gb;   // Terabyte

    if ($size < $kb) {
        return $size . " B";
    } else if ($size < $mb) {
        return round($size / $kb, 2) . " KB";
    } else if ($size < $gb) {
        return round($size / $mb, 2) . " MB";
    } else if ($size < $tb) {
        return round($size / $gb, 2) . " GB";
    } else {
        return round($size / $tb, 2) . " TB";
    }
}

//字符串格式化
function this_text_show($s) {
	$s = str_replace(" ", "&nbsp;", $s);
	$s = str_replace("\r", "", $s);
	$s = str_replace("\n", "<br>", $s);
	for ($i=0; $i<5; $i++) {
		$s = str_replace("<br><br>", "<br>", $s);
	}
	$s = "<br>".$s;
	$s = preg_replace("/<br>([^>]*?\d{2}:\d{2}:\d{2})/", "<br><br><font color=blue>[\\1]</font>", $s);
	while (substr($s, 0, 4) == "<br>") {
		$s = substr($s, 4);
	}
	return $s;
}

//特殊字符过滤		
function strFilter($str){
	$str = str_replace("'","", $str); //英文单音
	$str = str_replace('‘',"", $str); //中文单音
	$str = str_replace('"',"", $str); //英文双音
	$str = str_replace('“',"", $str); //中文单音
	$str = str_replace('-',"", $str);//英文减号
	$str = str_replace('；',"", $str);//中文分号
	$str = str_replace(';',"", $str);//英文分号
	return trim($str);
}

/**
 * 获取两个日期的时间所有的月份
 * @param $date1 Y-m-d
 * @param $date2 Y-m-d
 * @return [[]]
 */
function diffdate($date1, $date2){
    if (strtotime ( $date1 ) > strtotime ( $date2 )) 
    {
        $ymd = $date2;
        $date2 = $date1;
        $date1 = $ymd;
    }
    list ( $y1, $m1, $d1 ) = explode ( '-', $date1 );
    list ( $y2, $m2, $d2 ) = explode ( '-', $date2 );
    $math = ($y2 - $y1) * 12 + $m2 - $m1;
    $my_arr = array ();
    if ($y1 == $y2 && $m1 == $m2) 
    {
        if ($m1 < 10) 
        {
            $m1 = intval ( $m1 );
            $m1 = '0' . $m1;
        }
        if ($m2 < 10) 
        {
            $m2 = intval ( $m2 );
            $m2 = '0' . $m2;
        }
        $my_arr [] = $y1 . '_' . $m1;
        $my_arr [] = $y2 . '_' . $m2;
        return $my_arr;
    }     
    $p = $m1;
    $x = $y1;     
    for($i = 0; $i <= $math; $i ++) 
    {
        if ($p > 12) 
        {
            $x = $x + 1;
            $p = $p - 12;
            if ($p < 10) 
            {
                $p = intval ( $p );
                $p = '0' . $p;
            }
            $my_arr [] = $x . '-' . $p;
        } 
        else 
        {
            if ($p < 10) 
            {
                $p = intval ( $p );
                $p = '0' . $p;
            }
            $my_arr [] = $x . '-' . $p;
        }
        $p = $p + 1;
    }
    return $my_arr;
}

/**
 * 获取每月时间段
 * @param $start_ts
 * @param $end_ts
 * @author wodrow
 * @return [
 *      ['k'=>[
 *          'start'=>['ts'=>timestrap,'date'=>'Y-m'],
 *          'end'=>['ts'=>timestrap,'date'=>'Y-m'],
 *      ]]
 * ]
 */
function get_month_solt($start_ts,$end_ts){
    $mouth_solt = [];
    $x = $start_ts;
    $i = 1;
    while($x < $end_ts){
        $mouth_solt[$i]['start']['ts'] = $x;
        $mouth_solt[$i]['start']['date'] = date("Y-m", $x);
        $x = strtotime("+{$i} Month", $start_ts);
        $mouth_solt[$i]['end']['ts'] = $x;
        $mouth_solt[$i]['end']['date'] = date("Y-m", $x);
        $i++;
    }
    return $mouth_solt;
}

/**
 * 获取年时间段
 */
function get_year_solt($start_ts,$end_ts){
    $year_solt = [];
    $x = $start_ts;
    $i = 1;
    while($x < $end_ts){
        $year_solt[$i]['start']['ts'] = $x;
        $year_solt[$i]['start']['year'] = date("Y", $x);
        $x = strtotime("+{$i} Year", $start_ts);
        $year_solt[$i]['end']['ts'] = $x;
        $year_solt[$i]['end']['year'] = date("Y", $x);
        $i++;
    }
    return $year_solt;
}


/**
 * 时间戳转格式化年
 * @param type $start
 * @param type $end
 * @return type
 */
function time2year($start , $end){   
    $year_array['year']['start'] = date("Y", $start);	    
    $year_array['year']['end'] = date("Y", $end);
    return $year_array;    
}

/**
 * 时间格式化
 * @param type Y
 * @param type Y-m
 * @param type Y-m-d
 */
function format_date($id){
    $data_date = "";
    switch ($id){
	case 1:
	    $date = date('Y',time());
	    $data_date = strtotime($date . '-01-01 00:00:00');
	    break;
	case 2:
	    $date = date('Y-m',time());
	    $data_date = strtotime($date . '-01 00:00:00');
	    break;
	case 3:
	    $date = date('Y-m-d',time());
	    $data_date = strtotime($date . ' 00:00:00');
	    break;
	default:
	    $date = date('Y-m-d',time());
	    $data_date = strtotime($date . ' 00:00:00');
	    break;	    
    }
    return $data_date;
}

/**
 * 数据导出Excel表
 */
function exportExcel($fileName,$headArr,$data){
    //引入类库
    require VENDOR_PATH."/phpoffice/phpexcel/Classes/PHPExcel.php";
    require VENDOR_PATH."/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php";
    require VENDOR_PATH."/phpoffice/phpexcel/Classes/PHPExcel/Writer/CSV.php";
    
    if(empty($data) || !is_array($data)){die("data must be a array");}
    if(empty($fileName)){exit;}
    $date = date("Y_m_d",time());
    $fileName .= "_{$date}.csv";
    $objPHPExcel = new PHPExcel(); 
    //设置表头
    $kk = ord("A");
    foreach($headArr as $v){
        $colum = chr($kk);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum.'1',$v);
        $kk += 1;
    } 
    $column = 2;
    $objActSheet = $objPHPExcel->getActiveSheet();
    foreach($data as $key => $rows){ //行写入
        $span = ord("A");
        foreach($rows as $keyName=>$value){// 列写入
            $j = chr($span);
            $objActSheet->setCellValue($j.$column,$value);
            $objActSheet->getCell('F'.$column)->getHyperlink()->setUrl('http://www.baidu.com');
            $span++;
        }
        $column++;
    } 
    $objPHPExcel->setActiveSheetIndex(0);        
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=\"$fileName\"");
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'csv');
    $objWriter->save('php://output'); //文件通过浏览器下载     
    exit; 
}

/**
 * D方法自定义空模型 / 读取nongyao001数据库
 */
function queryMysql($sql){
    $CountData = D();
    $CountData->db(1,C('BUSINESS_DB'));
    $data = $CountData->query($sql);
    return $data;
}

//通过ID获取省份
function getAreaFullNameFromAreaID($areaid,$x){
    $y = getAreaInfoFromAreaID($areaid,$x);
    $province = array_reverse($y);
    return $province[0];
}

function getAreaInfoFromAreaID($areaid,&$areaInfo){
    $Area = D('Area');
    $data = $Area->where('areaid='.$areaid)->select();
    foreach($data AS $k=>$v){
        if($v['parentid'] == 0){
            $areaInfo[] = $v['areaname'];
        }else{
            $areaInfo[] = $v['areaname'];
            getAreaInfoFromAreaID($v['parentid'],$areaInfo);
        }
    }
    return $areaInfo;
}