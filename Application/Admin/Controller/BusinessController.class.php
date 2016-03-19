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
        $x = $this->getBusinessTotal(5); //0 25 316 2749
        Tools::_vp($x);
        $this->display();
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

    //各县合作商金额统计
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

    //获取合作商详细信息
    protected function getAgentDetail(){
        $area = D('Area');
        $agdl = D('AgentDownLine');
        $data = $area->cache(true)->alias('a')->field('a.*,c.truename,c.userid')->join('LEFT JOIN '.C('BUSINESS_DB_TABLE_PREFIX').'agent b ON b.agareaid = a.areaid LEFT JOIN '.C('BUSINESS_DB_TABLE_PREFIX').'member c ON c.userid = b.aguid')->select();
        foreach($data as $key=>$value){
            if ($value['truename']){
                $tmp[$key] = $value;
                $tmp[$key]['totalmoney'] = getTotalMoney($value['areaid']); //获取合作商金额
                $map['agentuid'] = array('eq',$value['userid']); //获取合作商下线
                $tmp[$key]['count'] = $agdl->where($map)->count();
            }
        }
        return $tmp;
    }

    //通过县areaid获取所属市
    protected function getPidByCity(){
        $data = $this->getAgentDetail();
        $area = D('Area');
        foreach($data as $k=>$v){
            $where['areaid'] = array('eq',$v['parentid']);
            $data_area = $area->cache(true)->field('parentid,areaname')->where($where)->select();
            foreach($data_area as $k2=>$v2){
                $data[$k]['parentid'] = $v2['parentid'];
                $data[$k]['city'] = $v2['areaname'];
                $data[$k]['county'] = $v['areaname'];
                if($v2['parentid'] == 0){ //判断父ID是否为0 如果为0则为省级栏目
                    $data[$k]['provice'] = $v2['areaname'];
                    $data[$k]['city'] = $v['areaname'];
                }
            }
        }
        return $data;
    }

    /**
     * 各县交易额数据导出
     * 返回excel表
     */
    public function businessExport() {
        $area = D('Area');
        if (I('get.type') == 'export') {
            $data = $this->getPidByCity();
            foreach($data as $k=>$v){
                if($v['parentid']!=0){
                    $where['areaid'] = array('eq',$v['parentid']);
                    $data_area = $area->field('parentid,areaname')->where($where)->select();
                    foreach($data_area as $k2=>$v2){
                        $data[$k]['provice'] = $v2['areaname']; //获取省名
                    }
                }
            }

            $x = 1;
            //数据重组
            foreach($data as $key=>$val){
                $tmp[$key]['id'] = $x++;
                $tmp[$key]['provice'] = $val['provice'];
                $tmp[$key]['city'] = $val['city'];
                $tmp[$key]['county'] = $val['county'];
                $tmp[$key]['totalmoney'] = $val['totalmoney'];
                $tmp[$key]['truename'] = $val['truename'];
                $tmp[$key]['count'] = $val['count'];
            }

            $fileName = "合作商交易额及下线客户统计";
            $headArr = array('ID','省','城市', '区县', '交易额','合作商','线下客户');
            exportExcel($fileName, $headArr, $tmp); //数据导出
        }
    }


    /**
     * 合作商热力图
     * @author Iredbaby
     */
    public function businessHot(){
        $Area = D('Area');
        $Agent = D('Agent');
        $map['parentid'] = array('eq',0);
        $Area_data = $Area->where($map)->select();
        foreach($Area_data AS $k=>$v){
            $where['agareaid'] = array('in',$v['arrchildid']);
            $data = $Agent_count = $Agent->where($where)->select();
            foreach($data AS $k2=>$v2){
                $tmp[$k]['count'] = count($data)!=0?count($data):0;
                $tmp[$k]['name'] = $v['areaname'];
            }
        }
        $id = I('get.id');
        $count = array(50,100,150,200,500,800,1000,2000,5000,8000,10000);
        $this->assign(['count_id'=>$id,'count'=>$count,'data'=>$tmp]);
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
