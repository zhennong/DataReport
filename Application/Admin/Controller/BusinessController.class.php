<?php
/*
 * @thinkphp3.2.3  招商管理   php5.4以上
 * @Created on 2016/01/15
 * @Author  iredbaby   1596229276@qq.com
 * @如果需要公共控制器，就不要继承AuthController，直接继承Controller
 */
namespace Admin\Controller;

use Common\Tools;

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
        $mouth_solt = get_month_solt($this->month_start, $this->month_end);
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
        $provice = R('Member/getProvice');
        $provice_name = R('Member/getProvice',array(1,I('pid')));
        $pid = I('get.pid');
        if(empty($pid)){
            $pid = 17; //默认显示河南省
        }

        $area = D('area');
        $where['parentid'] = $pid;
        $data  = $area->field('areaid,parentid,areaname')->where($where)->select();
        if($pid > 4 && $pid < 33){
            foreach($data AS $k=>$v){
                $sql = "select a.areaid as areaid,a.areaname as areaname,SUM(b.money) as total from `destoon_area` as a,`destoon_member` as b where a.areaid = b.areaid AND a.parentid='".$v['areaid']."' group by areaid order by total desc";
               $data[$k]['sub'] = queryMysql($sql);
            }
        }else{ //特殊城市处理
            foreach($data AS $k=>$v){
                if($k > 0){
                    unset($data[$k]);
                }else{
                    $sql = "select a.areaid as areaid,a.areaname as areaname,SUM(b.money) as total from `destoon_area` as a,`destoon_member` as b where a.areaid = b.areaid AND a.parentid='".$v['parentid']."' group by areaid order by total desc";
                    $data[$k]['sub'] = queryMysql($sql);
                }
            }
        }

        $this->assign('provice',$provice);
        $this->assign('provice_id',$pid);
        $this->assign('provice_name',$provice_name[0]['areaname']);
        $this->assign('data',$data);
        $this->display();
    }
    
    /**
     * 导出数据
     */
    public function businessExport() {
        if (I('get.type') == 'export') {
            $pid = I('get.pid');

            $area = D('area');
            $where['parentid'] = $pid;
            $data  = $area->field('areaid,parentid,areaname')->where($where)->select();
            if($pid > 4 && $pid < 33){
                foreach($data AS $k=>$v){
                    $sql = "select a.areaid as areaid,a.areaname as areaname,SUM(b.money) as total from `destoon_area` as a,`destoon_member` as b where a.areaid = b.areaid AND a.parentid='".$v['areaid']."' group by areaid order by total desc";
                    $data[$k]['sub'] = queryMysql($sql);
                }
            }else{ //特殊城市处理
                foreach($data AS $k=>$v){
                    if($k > 0){
                        unset($data[$k]);
                    }else{
                        $sql = "select a.areaid as areaid,a.areaname as areaname,SUM(b.money) as total from `destoon_area` as a,`destoon_member` as b where a.areaid = b.areaid AND a.parentid='".$v['parentid']."' group by areaid order by total desc";
                        $data[$k]['sub'] = queryMysql($sql);
                    }
                }
            }

            dump($data);


//            $fileName = "各县交易额统计";
//            $headArr = array('ID', '城市', '区县', '交易额');
//            exportExcel($fileName, $headArr, $data); //数据导出
        }
    }


	/**
     * 合作商热力图
     */
    public function businessHot() {
		$Agent = D('Agent');
		$Agent_area_id = $Agent->select();
		foreach($Agent_area_id AS $k=>$v){
			$Province[$k] = getAreaFullNameFromAreaID($v['agareaid']);
		}

        $id = I('get.id');
        if(empty($id)){
           $id = 50;
        }
        $count = array(50,100,150,200,300,400,500,1000,2000,5000,10000);

        $this->assign('count',$count);
        $this->assign('count_id',$id);
		$this->assign('data',array_count_values($Province));
		$this->display();
    }
}












