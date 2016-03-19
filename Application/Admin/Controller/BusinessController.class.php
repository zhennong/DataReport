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
        $this->display();
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

    //获取各地合作商交易额
    private function getTotalMoney($agareaid = 0){
        $Trade = D('Trade');
        $map['a.status'] = array('in','2,3,4');
        $map['b.areaid'] = array('eq',$agareaid);
        $total = $Trade->cache(true)->alias('a')->field('SUM(a.amount) AS totalmoney')->join('destoon_address b on a.addressid = b.itemid')->where($map)->select();
        $x = get_arr_k_amount($total,'totalmoney');
        $data = $x;
        return $data;
    }

    //获取合作商详细信息
    public function getAgentDetail(){
        $area = D('Area');
        $agdl = D('AgentDownLine');
        $data = $area->cache(true)->alias('a')->field('a.*,c.truename,c.userid')->join('LEFT JOIN destoon_agent b on b.agareaid = a.areaid LEFT JOIN destoon_member c on c.userid = b.aguid')->select();
        foreach($data as $key=>$value){
            if ($value['truename']){
                $tmp[$key] = $value;

                //获取合作商金额
                $tmp[$key]['totalmoney'] = $this->getTotalMoney($value['areaid']);

                //获取合作商下线
                $map['agentuid'] = array('eq',$value['userid']);
                $tmp[$key]['count'] = $agdl->where($map)->count();
            }
        }
        return $tmp;
    }

    //通过县areaid获取所属市
    public function getPidByCity(){
        $data = $this->getAgentDetail();
        $area = D('Area');
        foreach($data as $k=>$v){
            $where['areaid'] = array('eq',$v['parentid']);
            $data_area = $area->cache(true)->field('parentid,areaname')->where($where)->select();
            foreach($data_area as $k2=>$v2){
                $tmp[$k]['id'] = $k;
                $tmp[$k]['parentid'] = $v2['parentid'];
                $tmp[$k]['city'] = $v2['areaname'];
                $tmp[$k]['county'] = $v['areaname'];
                $tmp[$k]['totalmoney'] = $v['totalmoney'];
                $tmp[$k]['truename'] = $v['truename'];
                $tmp[$k]['count'] = $v['count'];
                if($v2['parentid'] == 0){ //判断父ID是否为0 如果为0则为省级栏目
                    $tmp[$k]['provice'] = $v2['areaname'];
                    $tmp[$k]['city'] = $v['areaname'];
                }
            }
        }
        return $tmp;
    }

    /**
     * 各县交易额数据导出
     * 返回excel表
     */
    public function businessExport() {
        $area = D('Area');
        if (I('get.type') == 'export') {
            $data = $this->getIdByCity();
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
                $tmp[$k]['count'] = count($data);
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
