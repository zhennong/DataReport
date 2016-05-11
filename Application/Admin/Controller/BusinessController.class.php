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
        $this->getTotalData();
        $data_provice = $this->getProviceTotal();
        $data_city = $this->getCityTotal();
        $data_county = $this->getCountyTotal();
        $this->assign(['provice'=>$data_provice,'city'=>$data_city,'county'=>$data_county]);
        $this->display();
    }

    //缓存数据
    protected function getTotalData(){
        S('TotalData',null);
        if(!S('TotalData')){
            $sql = "SELECT SUM(trade.amount) AS tamount, address.areaid AS areaid, area.arrparentid
                FROM __MALL_finance_trade AS trade
                INNER JOIN __MALL_address AS address ON trade.addressid = address.itemid
                INNER JOIN __MALL_area AS area ON address.areaid = area.areaid
                WHERE trade.status IN(2,3,4)
                GROUP BY address.areaid";
            $data = $this->MallDb->list_query($sql);
            foreach($data as $k=>$v){
                $v['arrparentid'] = Tools::str2arr($v['arrparentid']);
                $data_new[$v['areaid']]['areaid'] = $v['areaid'];
                $data_new[$v['areaid']]['topid'] = $v['arrparentid'][1];
                $data_new[$v['areaid']]['parentid'] = $v['arrparentid'][2];
                $data_new[$v['areaid']]['tamount'] = $v['tamount'];
            }
            unset($data);
            S('TotalData',$data_new);
        }
    }

    //获取代理商数量
    private function getAgentList($areaid){
        $sql = "SELECT COUNT(*) AS count FROM __MALL_agent WHERE agareaid IN {$areaid}";
        $agent_list = $this->MallDb->list_query($sql);
        foreach($agent_list AS $k=>$v){
            if($v['count']){
                $count = $v['count'];
            }
        }
        return $count;
    }

    //获取合作商名字和和下线数
    private function getAgentNameAndDownLine($areaid){
        $sql = "SELECT a.*,m.username,m.truename,m.userid,at.isok,s.score FROM __MALL_area a ".
            "LEFT JOIN __MALL_agent at ON at.agareaid=a.areaid ".
            "LEFT JOIN __MALL_member m ON m.userid=at.aguid ".
            "LEFT JOIN __MALL_agent_score s ON s.aguid=m.userid ".
            "WHERE a.areaid=".$areaid;
        $data = $this->MallDb->list_query($sql);
        foreach($data as $k=>$v){
            if($v['userid'] > 0){
                $sql2 = "SELECT COUNT(*) AS count FROM __MALL_agent_downline WHERE agentuid=".$v['userid'];
                $data2 = $this->MallDb->list_query($sql2);
                foreach($data2 as $k2=>$v2){
                    $data[$k]['truename'] = $v['truename'];
                    $data[$k]['count'] = $v2['count'];
                }
            }
        }
        return $data;
    }

    //获取省数据
    private function getProviceTotal(){
        $area = D('area');
        $arealist = $area->where(['parentid' => 0])->select();
        $totalData = S('TotalData');
        foreach($arealist as $k=>$v){
            $agent_count = $this->getAgentList($v['arrchildid']);
            $total = 0;
            foreach($totalData as $areaid=>$v2){
                if($v2['topid']==$v['areaid']){
                    $tmp[$v['areaid']] = $v;
                    $tmp[$v['areaid']]['count'] = $agent_count;
                    $total  += $v2['tamount'];
                    $tmp[$v['areaid']]['tamount'] = $total;
                }
            }
        }
        return $tmp;
    }

    //获取市数据
    private function getCityTotal(){
        $id = I('get.pid');
        if(!empty($id)){
            if($id > 4){
                $area = D('area');
                $arealist = $area->where(['parentid' => $id])->select();
                $totalData = S('TotalData');
                $data = array();
                foreach($arealist as $k=>$v){
                    $agent_count = $this->getAgentList($v['arrchildid']);
                    $total = 0;
                    foreach($totalData as $areaid=>$v2){
                        if($v2['parentid']==$v['areaid']){
                            $data[$v['areaid']] = $v;
                            $data[$v['areaid']]['count'] = $agent_count;
                            $total += $v2['tamount'];
                            $data[$v['areaid']]['tamount'] = $total;
                        }
                    }
                }
            }else{
                $area = D('area');
                $arealist = $area->where(['parentid' => $id])->select();
                $totalData = S('TotalData');
                $data = array();
                foreach($arealist as $k=>$v){
                    foreach($totalData as $areaid=>$v2){
                        if($v2['areaid']==$v['areaid']){
                            $data[$v['areaid']] = $v;
                            $agent_data = $this->getAgentNameAndDownLine($v['areaid']);
                            foreach($agent_data as $k3=>$v3){
                                $data[$v['areaid']]['truename'] = $v3['truename'];
                                $data[$v['areaid']]['count'] = $v3['count'];
                            }
                            $data[$v['areaid']]['tamount'] += $v2['tamount'];
                        }
                    }
                }
            }
            return $data;
        }
    }

    //获取县数据
    private function getCountyTotal(){
        $id = I('get.cid');
        if(!empty($id)){
            $area = D('area');
            $arealist = $area->where(['parentid' => $id])->select();
            $totalData = S('TotalData');
            $data = array();
            foreach($arealist as $k=>$v){
                foreach($totalData as $areaid=>$v2){
                    if($v2['areaid']==$v['areaid']){
                        $data[$v['areaid']] = $v;
                        $agent_data = $this->getAgentNameAndDownLine($v['areaid']);
                        foreach($agent_data as $k3=>$v3){
                            $data[$v['areaid']]['truename'] = $v3['truename'];
                            $data[$v['areaid']]['count'] = $v3['count'];
                        }
                        $data[$v['areaid']]['tamount'] += $v2['tamount'];
                    }
                }
            }
            return $data;
        }
    }

    //各县合作商金额统计
    private function businessAgentTotal(){
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
    private function getAgentDetail(){
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
    private function getPidByCity(){
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
    /**
     * 企业入驻
     * @author Edwin <junqianhen@gmail.com>
     */
    public function enterpriseSettled()
    {
        $Enterprise = D('Enterprise');
        //查询数据
        $enterprise_data = $Enterprise->select();
        foreach($enterprise_data as $k => $v){
            $enterprise_data_list[$k] = $v;
        }
        //注入显示
        $this->assign(['data_list'=>$enterprise_data_list]);
        $this->display();
    }
}
