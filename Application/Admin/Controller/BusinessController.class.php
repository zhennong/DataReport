<?php
/*
 * @thinkphp3.2.3  招商管理   php5.4以上
 * @Created on 2016/01/15
 * @Author  iredbaby   1596229276@qq.com
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
    public function businessTotal() {
        $x = $this->getBusinessTotal(25); //0 25 316 2749
        Tools::_vp($x);
        /*$this->assign('pid',I('get.pid'));
        $this->assign('cid',I('get.cid'));
        $this->assign('provice',$this->getProvice()); //省
        $this->assign('city',$this->getCity());       //市
        $this->assign('county',$this->getCounty());   //县
        $this->display();*/
    }

    public function getBusinessTotal($areaid)
    {
        $areas = D('Area')->field(["arrchildid"=>'ids'])->where(["areaid"=>$areaid])->find();
        $areas = Tools::str2arr($areas['ids']);
        $this->agentTradeCache();
        foreach($areas as $k => $v){
            if(count($this->agent_info[$v])>0) {
                $agent_trade[$v]['agent_info'] = $this->agent_info[$v];
                $agent_trade[$v]['info'] = $this->area_trade[$v];
            }
        }
        return $agent_trade;
    }


    /**
     * 缓存
     */
    public function agentTradeCache()
    {
        if(!S('agent_info')){
            $sql = "SELECT area.areaid, member.truename FROM __MALL_area AS area
                INNER JOIN __MALL_agent AS agent ON area.areaid = agent.agareaid
                INNER JOIN __MALL_member AS member On agent.aguid = member.userid";
            $x = $this->MallDb->list_query($sql);
            foreach($x as $k => $v){
                $y[$v['areaid']] = $v;
            }
            $this->agent_info = $y;
            S('agent_info',$this->agent_info);
        }else{
            $this->agent_info = S('agent_info');
        }
        if(!S('area_trade')){
            $sql = "SELECT area.areaid, area.areaname, SUM(trade.amount) AS amount FROM __MALL_address AS address
                    LEFT JOIN __MALL_area AS area ON address.areaid = area.areaid
                    LEFT JOIN __MALL_finance_trade AS trade ON address.itemid = trade.addressid
                    WHERE trade.status IN(2,3,4)
                    GROUP BY area.areaid";
            $x = $this->MallDb->list_query($sql);
            foreach($x as $k => $v){
                $y[$v['areaid']] = $v;
            }
            $this->area_trade = $y;
            S('area_trade',$this->area_trade);
        }else{
            $this->area_trade = S('area_trade');
        }
    }

    public function ajaxGetBusiness($parent_area_id, &$agent_trade=[])
    {
        // 是否是区县
        $_areaTree = $this->getAreaTree($parent_area_id);

        $this->agentTradeCache();

        if(count($_areaTree)>0){
            foreach($_areaTree as $k => $v){
                $agent_trade = $this->ajaxGetBusiness($v['id'],$agent_trade);
            }
        }else{
            $area_id = $parent_area_id;
            if(count($this->agent_info[$area_id])>0){
                $agent_trade[$area_id]['agent_info'] = $this->agent_info[$area_id];
                $agent_trade[$area_id]['info'] = $this->area_trade[$area_id];
            }
        }
        return $agent_trade;
    }


    /**
     * 获取省
     * @return mixed
     */
    private function getProvice(){
        $area = D('Area');
        $agent = D('Agent');
        $where['parentid'] = array('eq',0);
        //$where['areaid'] = array('lt',7);

        $data_area = $area->cache(true)->where($where)->select();

        foreach($data_area AS $key=>$value){
            $map['agareaid'] = array('in',$value['arrchildid']);
            $data = $agent->where($map)->field('agareaid')->select();
            $total = 0;
            foreach($data As $k=>$v){
                if(count($data) > 0) {
                    $data_area[$key]['count'] = '<font style="color: #2aabd2;">合作商：'.count($data).' 个</font>';

//                    $total = $this->getTotalMoney($value['areaid'],count($data));
//                    $totallist[$k] = $total;



                }



            }

//            foreach($totallist as $v){
//                $s += $v;
//            }
//            $data_area[$key]['totalmoney'] = '<font style="color: #f00;">金额：'.$s.'</font>';

        }
        return $data_area;
    }

    /**
     * 获取市
     * @return mixed
     */
    private function getCity() {
        $id = I('get.pid');
        if(!empty($id)){
            if($id > 4){
                $area = D('Area');
                $agent = D('Agent');
                $where['parentid'] = array('eq',$id);
                $data_area = $area->where($where)->select();
                foreach($data_area AS $key=>$value){
                    $map['agareaid'] = array('in',$value['arrchildid']);
                    $data = $agent->where($map)->field('agareaid')->group('agareaid')->select();

                    $total = 0;
                    foreach($data As $k=>$v) {
                        if (count($data) > 0) {
                            $data_area[$key]['count'] = '<font style="color: #2aabd2;">合作商：'.count($data).' 个</font>';

//                            $total = $this->getTotalMoney($v['agareaid']);
//                            $totallist[$k] = $total;
                        }
                    }

//                    foreach($totallist as $v){
//                        $s += $v;
//                    }
//                    $data_area[$key]['totalmoney'] = '<font style="color: #f00;">金额：'.$s.'</font>';
                }
            }else{ //特殊城市处理
                if(!empty($id)) {
                    //
                }
            }
            return $data_area;
        }
    }

    /**
     * 获取县
     * @return mixed
     */
    private function getCounty() {
        $id = I('get.cid');
        if(!empty($id)) {
            $agent_downline = D('AgentDownLine');
            $Area = D('Area');
            $where['parentid'] = array('eq',$id);
            $data = $Area->cache(true)->alias('a')->field('a.areaid,a.areaname,c.truename,b.agareaid,b.isok')->join('LEFT JOIN destoon_agent b on b.agareaid = a.areaid LEFT JOIN destoon_member c on c.userid = b.aguid')->where($where)->select();
            foreach ($data as $key => $value) {
                $map['agentuid'] = array('eq', $value['userid']);
                $data_info = $agent_downline->where($map)->count();
                if ($value['truename']){
                    $data[$key]['truename'] = '<font style="color:#0c199c;margin-left: 10px;">姓名：' . $value['truename'] . '</font>';

                    $data[$key]['totalmoney'] = '<font style="color: #f00;margin-left: 10px;">金额：'.$this->getTotalMoney($value['areaid']).'</font>';
                }

                if ($data_info > 0) {
                    $data[$key]['count'] = '<font style="color: #2aabd2;">下线数：(' . $data_info . ')</font>';
                }
            }
        }
        return $data;
    }

    /**
     * 获取各地合作商交易额
     * @param int $agareaid
     * @return mixed
     */
    private function getTotalMoney($agareaid = 0){
        $Trade = D('Trade');
        $map['a.status'] = array('in','2,3,4');
        $map['b.areaid'] = array('eq',$agareaid);
        $total = $Trade->cache(true)->alias('a')->field('SUM(a.amount) AS totalmoney')->join('destoon_address b on a.addressid = b.itemid')->where($map)->select();
        $x = get_arr_k_amount($total,'totalmoney');
        $data = $x;
        return $data;
    }


    /**
     * 各县合作商
     */
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

    //获取全部数额
    public function getAllArea($id){
        $area = D('Area');
        $map['parentid'] = array('eq',$id);
        $data = $area->cache(true)->alias('a')->field('a.*,c.truename')->join('LEFT JOIN destoon_agent b on b.agareaid = a.areaid LEFT JOIN destoon_member c on c.userid = b.aguid')->where()->select();

        foreach($data as $key=>$value){
            if ($value['truename']){
                $total[$key]['areaid'] = $value['areaid'];
                $total[$key]['areaname'] = $value['areaname'];
                $total[$key]['parentid'] = $value['parentid'];
                $total[$key]['truename'] = $value['truename'];
                $total[$key]['totalmoney'] = $this->getTotalMoney($value['areaid']);
            }
            //$data[$key]['totalmoney'] = $this->getTotalMoney($value['areaid']);
        }
        return $total;
    }


    /**
     * 导出数据
     */
    public function businessExport()
    {
        if (I('get.type') == 'export') {
            $data = $this->getAllArea();
            $area = D('Area');
            foreach($data as $k=>$v){
                $where['areaid'] = array('eq',$v['parentid']);
                $data_area = $area->field('parentid,areaname')->where($where)->select();
                foreach($data_area as $k2=>$v2){
                    $tmp[$k]['id'] = $k;
                    $tmp[$k]['city'] = $v2['areaname'];
                    $tmp[$k]['county'] = $v['areaname'];
                    $tmp[$k]['totalmoney'] = $v['totalmoney'];
                }
            }

            dump($tmp);


//            $all_data = Tools::list2tree($data,'areaid','parentid','_child',0);
//            foreach($all_data as $key=>$value){
//                foreach($value['_child'] as $k2=>$v2){
//
//                    $total[$key]['_child'] = $v2;
//
//                    foreach($v2['_child'] as $k3=>$v3){
//                        $total[]['id'] = $key+1;
//                        $total[]['provice'] = $value['areaname'];
//                        $total[]['city'] = $v2['areaname'];
//                        $total[]['counry'] = $v3['areaname'];
//                        $total[]['totalmoney'] = $v3['totalmoney']!=NULL?$v3['totalmoney']:0;
//
//                    }
//                }
//            }

            //dump($data);

//            $fileName = "各县交易额统计";
//            $headArr = array('ID','省','城市', '区县', '交易额');
//            exportExcel($fileName, $headArr, $data); //数据导出
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
