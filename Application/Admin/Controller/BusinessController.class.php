<?php
/*
 * @thinkphp3.2.3  招商管理   php5.4以上
 * @Created on 2016/01/15
 * @Author  iredbaby   1596229276@qq.com
 * @如果需要公共控制器，就不要继承AuthController，直接继承Controller
 */
namespace Admin\Controller;

class BusinessController extends AdminController{
    public function businessIndex(){
        $this->display();
    }
    
    /**
     * 合作商月趋势
     * @author iredbaby
     */
    public function businessTrend(){
        $Agent = D('Agent');
        $mouth_solt = get_mouth_solt($this->date_start, $this->date_end);
        foreach ($mouth_solt as $k => $v) {
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_agent[$k]['mouth_sort'] = $v;
            $mouth_solt_agent[$k]['agent'] = $Agent->field('addtime')->where($map)->select();
            $mouth_solt_agent[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
            $mouth_solt_agent[$k]['agent_amount'] = count($mouth_solt_agent[$k]['agent']);
            unset($mouth_solt_agent[$k]['agent']);
        }
        $this->assign(['mouth_solt_agent' => $mouth_solt_agent]);
        $this->display();
    }
    
    /**
     * 各县的交易额
     */
    public function businessTotal(){	
	$Area = D('Area');	
	$provice_id = I('pid');	
	$provice = R('Member/getProvice');	
	$provice_name = R('Member/getProvice',array(1,$provice_id));	
	if($provice_id == ""){$provice_id = 17; } //默认河南省
	$data = $Area->where('parentid =' .$provice_id)->select();
	foreach ($data as $k=>$v){    
	   $sql = "select a.areaid as areaid,a.areaname as areaname,b.areaid as areaids,SUM(b.money) as total from `destoon_area` as a,`destoon_member` as b where a.areaid = b.areaid AND a.parentid='".$v['areaid']."' group by b.areaid order by total desc";		   
	   $data[$k]['sub'] = queryMysql($sql);	
	}	
	//特殊城市处理 1、北京 2、上海 3、天津 4、重庆
	if($provice_id == '1' |$provice_id == '2' |$provice_id == '3' |$provice_id == '4'){
	    foreach ($data as $k=>$v){    
	       $sql = "select a.areaid as areaid,a.areaname as areaname,b.areaid as areaids,SUM(b.money) as total from `destoon_area` as a,`destoon_member` as b where a.areaid = b.areaid AND a.areaid='".$v['areaid']."' group by b.areaid order by total desc";		   
	       $data[$k]['sub'] = queryMysql($sql);	
	    }
	}			
	$this->assign('data',$data);
	$this->assign('provice',$provice);
	$this->assign('provice_name',$provice_name[0]['areaname']);
        $this->display();
    }
    
    /**
     * 导出数据
     */
    public function businessExport() {	
	//
    } 
    
    /**
     * 合作商热力图
     */
    public function businessHot() {
	$Area = D('Area');	
	$Agent = D('Agent');
	
	$data = $Area->field($field)->where('parentid = 0')->select();
	foreach ($data as $k=>$v){
	    $data[$k]['sub'] = $Area->field($field)->where('parentid = '.$v['areaid'])->select();
	}	
	$this->display();
    }
}
