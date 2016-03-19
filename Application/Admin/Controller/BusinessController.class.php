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

        $area = D('Area');

        $data = $area->cache(true)->alias('a')->field('a.areaid,a.areaname,c.truename')->join('LEFT JOIN destoon_agent b on b.agareaid = a.areaid LEFT JOIN destoon_member c on c.userid = b.aguid')->select();

        foreach($data as $key=>$value){
            if ($value['truename']){
                //dump($value['areaid']);

                $data[$key]['totalmoney'] = $this->getTotalMoney($value['areaid']);
            }
        }

        dump($data);



        $this->assign('pid',I('get.pid'));
        $this->assign('cid',I('get.cid'));
        $this->assign('provice',$this->getProvice()); //省
        $this->assign('city',$this->getCity());       //市
        $this->assign('county',$this->getCounty());   //县
        $this->display();
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
