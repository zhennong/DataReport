<?php
/*
 * @thinkphp3.2.3  招商管理   php5.4以上
 * @Created on 2016/01/15
 * @Author  iredbaby   1596229276@qq.com
 * @如果需要公共控制器，就不要继承AuthController，直接继承Controller
 */
namespace Admin\Controller;

use Common\Tools;

class BusinessController extends AdminController
{
    public function businessIndex()
    {
        $this->display();
    }

    /**
     * 合作商月趋势
     * @author iredbaby
     */
    public function businessTrend()
    {
        $Agent = D('Agent');
        $month_start=strtotime('January 2014');
        $mouth_solt = get_month_solt($month_start, $this->month_end);
        foreach ($mouth_solt as $k => $v) {
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_agent[$k]['mouth_sort'] = $v;
            $mouth_solt_agent[$k]['agent'] = $Agent->field('addtime')->where($map)->select();
            $mouth_solt_agent[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
            $mouth_solt_agent[$k]['agent_amount'] = count($mouth_solt_agent[$k]['agent']);
            unset($mouth_solt_agent[$k]['agent']);
        }
        $this->assign(['mouth_solt_agent' => $mouth_solt_agent]);
        $this->assign(['month_start'=>$month_start]);
        $this->display();
    }

    /**
     * 各县的交易额
     */
    public function businessTotal()
    {
//        $provice = R('Member/getProvice');
//        $provice_name = R('Member/getProvice', array(1, I('pid')));
//        $pid = I('get.pid');
//        if (empty($pid)) {
//            $pid = 17; //默认显示河南省
//        }
//
//        $area = D('area');
//        $where['parentid'] = $pid;
//        $data = $area->field('areaid,parentid,areaname')->where($where)->select();
//
//        if ($pid > 4 && $pid < 33) {
//            foreach ($data AS $k => $v) {
//                $sql = "select a.areaid as areaid,a.areaname as areaname,SUM(b.totalmoney) as total from `destoon_area` as a,`destoon_agent` as b where a.areaid = b.agareaid AND a.parentid='" . $v['areaid'] . "' group by areaid order by total desc";
//                $data[$k]['sub'] = queryMysql($sql);
//            }
//        } else { //特殊城市处理
//            foreach ($data AS $k => $v) {
//                if ($k > 0) {
//                    unset($data[$k]);
//                } else {
//                    $sql = "select da.areaid as areaid,a.areaname as areaname,SUM(b.totalmoney) as total from `destoon_area` as a,`destoon_agent` as b where a.areaid = b.agareaid AND a.parentid='" . $v['parentid'] . "' group by areaid order by total desc";
//                    $data[$k]['sub'] = queryMysql($sql);
//                }
//            }
//        }
//
//        $this->assign('provice', $provice);
//        $this->assign('provice_name', $provice_name[0]['areaname']);
//        $this->assign('data', $data);
//        $this->display();

        $pid = I('get.pid');
        $cid = I('get.cid');

        $this->assign('pid',$pid);
        $this->assign('cid',$cid);

        $this->assign('provice',$this->getProvice());
        $this->assign('city',$this->getCity());
        $this->assign('county',$this->getCounty());
        $this->display();
    }

    /**
     * 获取省
     * @return mixed
     */
    public function getProvice(){
        $area = D('Area');
        $agent = D('Agent');
        $where['parentid'] = array('eq',0);
        $data_area = $area->where($where)->select();
        foreach($data_area AS $key=>$value){
            //$map['agareaid'] = array('in',$value['arrchildid']);
            //$data = $agent->where($map)->field('COUNT(id) as count,SUM(totalmoney) as totalmoney')->select();

            $sql = "SELECT COUNT(a.id) AS count,SUM(b.amount) AS totalmoney FROM destoon_agent AS a,destoon_finance_trade AS b WHERE a.agareaid = b.addressid AND a.agareaid IN(".$value['arrchildid'].")";
            $data = queryMysql($sql);

            foreach($data As $k=>$v){
                if($v['count'] > 0) {
                    $data_area[$key]['count'] = '<font style="color: #2aabd2;">合作商：'.$v['count'].' 个</font>';
                    $data_area[$key]['totalmoney'] = '<font style="color: #f00;margin-left: 10px;">金额：'.$v['totalmoney'].'</font>';
                }
            }
        }
        return $data_area;
    }

    /**
     * 获取市
     * @return mixed
     */
    public function getCity()
    {
        $id = I('get.pid');
        if(!empty($id)){
            if($id > 4){
                $area = D('Area');
                $agent = D('Agent');
                $where['parentid'] = array('eq',$id);
                $data_area = $area->where($where)->select();
                foreach($data_area AS $key=>$value){
                    $map['agareaid'] = array('in',$value['arrchildid']);
                    $data = $agent->where($map)->field('COUNT(id) as count,SUM(totalmoney) as totalmoney')->select();
                    foreach($data As $k=>$v){
                        if($v['count'] > 0) {
                            $data_area[$key]['count'] = '<font style="color: #2aabd2;">合作商：'.$v['count'].' 个</font>';
                            $data_area[$key]['totalmoney'] = '<font style="color: #f00;margin-left: 10px;">金额：'.$v['totalmoney'].'</font>';
                        }
                    }
                }
            }else{
                $agent_downline = D('AgentDownLine');
                $sql = "SELECT a.*,m.username,m.truename,m.userid,at.totalmoney,at.isok FROM destoon_area a " .
                    "LEFT JOIN destoon_agent at ON at.agareaid=a.areaid " .
                    "LEFT JOIN destoon_member m ON m.userid=at.aguid " .
                    "WHERE a.parentid=" . $id;

                $data_area = queryMysql($sql);

                foreach ($data_area as $key => $value) {
                    $map['agentuid'] = array('eq', $value['userid']);
                    $data_info = $agent_downline->where($map)->count();

                    if ($value['truename']){
                        $data_area[$key]['truename'] = '<font style="color:#0c199c;margin-left: 50px;">姓名：' . $value['truename'] . '</font>';
                    }

                    if ($value['totalmoney']) {
                        $data_area[$key]['totalmoney'] = '<font style="color: #f00;">交易金额：' . $value['totalmoney'] . '</font>';
                    }

                    if ($data_info > 0) {
                        $data_area[$key]['count'] = '<font style="color: #2aabd2;">下线数：(' . $data_info . ')</font>';
                    }

                }
            }
            return $data_area;
        }
    }

    /**
     * 获取县
     * @return mixed
     */
    public function getCounty()
    {
        $id = I('get.cid');
        if(!empty($id)) {
            $agent_downline = D('AgentDownLine');
            $sql = "SELECT a.*,m.username,m.truename,m.userid,at.totalmoney,at.isok FROM destoon_area a " .
                "LEFT JOIN destoon_agent at ON at.agareaid=a.areaid " .
                "LEFT JOIN destoon_member m ON m.userid=at.aguid " .
                "WHERE parentid=" . $id;

            $data = queryMysql($sql);

            foreach ($data as $key => $value) {
                $map['agentuid'] = array('eq', $value['userid']);
                $data_info = $agent_downline->where($map)->count();

                if ($value['truename']){
                    $data[$key]['truename'] = '<font style="color:#0c199c;margin-left: 10px;">姓名：' . $value['truename'] . '</font>';
                }

                if ($value['totalmoney']) {
                    $data[$key]['totalmoney'] = '<font style="color: #f00;">交易金额：' . $value['totalmoney'] . '</font>';
                }

                if ($data_info > 0) {
                    $data[$key]['count'] = '<font style="color: #2aabd2;">下线数：(' . $data_info . ')</font>';
                }

            }

            return $data;
        }
    }


    //各县合作商
    public function businessAgentTotal(){
        $Agent = D('Agent');
        $areaid = I('get.areaid');
        if(!empty($areaid)){
            $where['agareaid'] = array('eq',$areaid);
            $data = $Agent->field('id,agusername,totalmoney')->where($where)->order('totalmoney DESC')->select();
        }else{
            $data = "暂无数据";
        }

        $this->assign('areaname',I('get.areaname'));
        $this->assign('data',$data);
        $this->display();
    }

    /**
     * 导出数据
     */
    public function businessExport()
    {
        if (I('get.type') == 'export') {

//          $fileName = "各县交易额统计";
//          $headArr = array('ID', '城市', '区县', '交易额');
//          exportExcel($fileName, $headArr, $data); //数据导出
        }
    }


    /**
     * 合作商热力图
     */
    public function businessHot()
    {
        $Agent = D('Agent');
        $Agent_area_id = $Agent->select();
        foreach ($Agent_area_id AS $k => $v) {
            $Province[$k] = getAreaFullNameFromAreaID($v['agareaid']);
        }

        $id = I('get.id');
        $count = array(50,100,150,200,500,800,1000,2000,5000,8000,10000);

        $this->assign('count_id',$id);
        $this->assign('count',$count);
        $this->assign('data', array_count_values($Province));
        $this->display();
    }

    /**
     * 合作商提成月走势图
     * @author Edwin <junqianhen@gmail.com>
     */
    public function partnerTrend()
    {
        $Agent = D('Partner');
        $map['status'] = ['in', '2,3,4'];
        //查询数据
        $month_start=strtotime('September 2013');
        $month_solt = get_month_solt($month_start, $this->month_end);
        foreach ($month_solt as $k => $v) {
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_partner[$k]['mouth_solt'] = $v;
            $x = $Agent->field('money')->where($map)->select();
            $mouth_solt_partner[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
            $mouth_solt_partner[$k]['partner_sum']=get_arr_k_amount($x,'money');
        }


        //重组数据_月资讯数据
        $xAxis_data = Tools::arr2str(Tools::getCols($mouth_solt_partner, 'mouth_name', true));
        $series_data = Tools::arr2str(Tools::getCols($mouth_solt_partner, 'partner_sum'));
        //注入显示
        $this->assign(['xAxis_data' => $xAxis_data,'series_data' => $series_data]);
        $this->assign(['month_start'=>$month_start]);
        $this->display();
    }
}
