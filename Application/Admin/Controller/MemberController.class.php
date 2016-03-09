<?php
/*
 * @Controller 会员模块
 * @Date 2016/01/18
 * @Author  iredbaby   1596229276@qq.com
 */
namespace Admin\Controller;

Use Common\Controller\AuthController;
use Common\Tools;
Use Think\Auth;

class MemberController extends AuthController
{
    /**
     * 勿删权限判断
     */
    public function memberIndex()
    {
        $this->display();
    }

    /**
     * 会员月注册趋势
     * @author iredbaby
     */
    public function memberReg()
    {
        $Member = D('Member');
        $mouth_solt = get_mouth_solt($this->month_start, $this->month_end);
        foreach ($mouth_solt as $k => $v) {
            $map['regtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_member[$k]['mouth_sort'] = $v;
            $mouth_solt_member[$k]['member'] = $Member->field('regtime')->where($map)->select();
            $mouth_solt_member[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
            $mouth_solt_member[$k]['member_amount'] = count($mouth_solt_member[$k]['member']);
            unset($mouth_solt_member[$k]['member']);
        }
        $this->assign(['mouth_solt_member' => $mouth_solt_member]);
        $this->display();
    }

    /**
     * 会员信息 带分页
     */
    public function memberInfos()
    {
        $Trade = D('Trade');
        $map['status'] = array('in', '2,3,4');
        $count = $Trade->field('buyer')->where($map)->count(('DISTINCT buyer'));
        $Page = new \Think\Page($count, 20);
        $show = $Page->show();
        $data = $Trade
            ->field('buyer,buyer_name,SUM(total) as a,SUM(amount) as b')
            ->where($map)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->group('buyer')
            ->order('b desc')
            ->select();
        $all_amount_count = $Trade->where($map)->field('amount')->sum('amount');
        foreach ($data as $k => $v) {
            $member_info[$k]['buyer'] = $v['buyer'];
            $member_info[$k]['buyer_name'] = $v['buyer_name'];
            $member_info[$k]['total'] = $v['a'];
            $member_info[$k]['amount'] = $v['b'];
            $member_info[$k]['rate'] = round($v['b'] / $all_amount_count * 100, 5);
        }
        $this->assign(['member_info' => $member_info, 'day_s' => $this->month_start, 'day_e' => $this->month_end, 'show' => $show, 'count' => $count]);
        $this->display();
    }

    //导出Excel表
    public function exportExcel()
    {
        $Trade = D('Trade');
        $map['status'] = array('in', '2,3,4');
        $map['status'] = array('neq', '');
        if (I('get.type') == 'export') {
            $data = $Trade->field('DISTINCT buyer,buyer_name,SUM(total) as a,SUM(amount) as b')->where($map)->group('buyer')->order('b desc')->select();
            $all_amount_count = $Trade->where($map)->field('amount')->sum('amount');
            foreach ($data as $k => $v) {
                $member_info[$k]['buyer'] = $v['buyer'];
                $member_info[$k]['buyer_name'] = $v['buyer_name'];
                $member_info[$k]['total'] = $v['a'];
                $member_info[$k]['amount'] = $v['b'];
                $member_info[$k]['rate'] = round($v['b'] / $all_amount_count * 100, 5) . '%';
            }
            $fileName = "会员信息";
            $headArr = array('账号', '姓名', '购买数量', '交易额', '购买率');
            exportExcel($fileName, $headArr, $member_info); //数据导出
        }
    }

    /**
     * 获取新注册会员
     * @author iredbaby
     */
    private function get_new_member($date_id)
    {
        $Member = D('Member');
        $map_new['regtime'] = [['gt', $date_id], ['lt', $this->now]];
        $reg_member_data = $Member->where($map_new)->field('username,regtime')->select();
        foreach ($reg_member_data as $key => $value) {
            $reg_member_count[] = $value['username'];
        }
        $reg_member_str = implode(',', $reg_member_count);
        return $reg_member_str;
    }

    /**
     * 会员付款统计
     * @param format_date ($i)  1 年  2 月 3 日
     * @author iredbaby
     */
    public function memberPay()
    {
        $Trade = D('Trade');
        //按年月日全部付款
        $map['status'] = ['in',[2,3,4]];
        for ($i = 1; $i <= 3; $i++) {
            $map['paytime'] = [['neq', 0], ['gt', format_date($i)], ['lt', $this->now]];
            $member_type[] = $i;
            $member_count[] = $Trade->where($map)->field('buyer,buyer_name,paytime')->count('distinct buyer');
        }
        $day_new = $month_new = 0;
        //日付款新会员
        $map['paytime'] = [['lt', $this->now_d_start]];
        $buyer_all_before_day = Tools::getCols($Trade->where($map)->field("buyer")->group('buyer')->select(),'buyer');
        $map['paytime'] = [['gt', $this->now_d_start],['lt', $this->now]];
        $buyer_after_day = $Trade->where($map)->field("buyer")->group('buyer')->select();
        foreach($buyer_after_day as $k => $v){
            if(in_array($v['buyer'],$buyer_all_before_day)){
                $day_new ++;
            }
        }
        //月付款新会员
        $map['paytime'] = [['lt', $this->now_m_start]];
        $buyer_all_before_month = Tools::getCols($Trade->where($map)->field("buyer")->group('buyer')->select(),'buyer');
        $map['paytime'] = [['gt', $this->now_m_start],['lt', $this->now]];
        $buyer_after_month = $Trade->where($map)->field("buyer")->group('buyer')->select();
        foreach($buyer_after_month as $k => $v){
            if(in_array($v['buyer'],$buyer_all_before_month)){
                $month_new ++;
            }
        }
        /*for ($i = 2; $i <= 3; $i++) {
            $day_member_name = $this->get_new_member(format_date($i));
            $map['paytime'] = [['neq', 0], ['gt', format_date($i)], ['lt', $this->now]];
            $map['buyer'] = [['in', $day_member_name]];
            $map['status'] = ['in',[2,3,4]];
            $new_member_type[] = $i;
            $new_member_str = $Trade->where($map)->field('buyer,buyer_name,paytime')->count('distinct buyer');
            if (empty($new_member_str)) {
                $new_member_count[] = 0;
            } else {
                $new_member_count[] = $new_member_str;
            }
        }*/
        //全部付款
        $map_all['paytime'] = [['neq', 0]];
        $all = $Trade->where($map_all)->field('buyer_name,paytime')->count('distinct buyer_name');
        $this->assign(['day' => $member_count[2], 'month' => $member_count[1], 'year' => $member_count[0], 'all' => $all]);
        $this->assign(['day_new' => $day_new, 'month_new' => $month_new]);
        $this->display();
    }

    /**
     * 会员APP注册
     */
    public function memberRegApp()
    {
        $Member = D('Member');
        $mouth_solt = get_mouth_solt($this->month_start, $this->month_end);
        foreach ($mouth_solt as $k => $v) {
            $map['regtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $map['comefrom'] = [['eq', 'touch']];
            $mouth_solt_member_app[$k]['mouth_sort'] = $v;
            $mouth_solt_member_app[$k]['member'] = $Member->field('regtime,comefrom')->where($map)->select();
            $mouth_solt_member_app[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
            $mouth_solt_member_app[$k]['member_amount'] = count($mouth_solt_member_app[$k]['member']);
            unset($mouth_solt_member_app[$k]['member']);
        }
        $this->assign(['mouth_solt_member_app' => $mouth_solt_member_app]);
        $this->display();
    }

    /**
     * 会员统计
     */
    public function memberCount()
    {
        $Area = D('Area');
        $provice_id = I('pid');
        $provice = $this->getProvice();
        $provice_name = $this->getProvice(1, $provice_id);
        if ($provice_id == "") {
            $provice_id = 17;
        } //默认河南省
        $data = $Area->where('parentid =' . $provice_id)->select();
        foreach ($data as $k => $v) {
            $sql = "select a.areaid as areaid,a.areaname as areaname,b.areaid as areaids,COUNT(b.areaid) as total from `destoon_area` as a,`destoon_member` as b where a.areaid = b.areaid AND a.parentid='" . $v['areaid'] . "' group by b.areaid";
            $data[$k]['sub'] = queryMysql($sql);
        }
        //特殊城市处理 1、北京 2、上海 3、天津 4、重庆
        if ($provice_id == '1' | $provice_id == '2' | $provice_id == '3' | $provice_id == '4') {
            foreach ($data as $k => $v) {
                $sql = "select a.areaid as areaid,a.areaname as areaname,b.areaid as areaids,COUNT(b.areaid) as total from `destoon_area` as a,`destoon_member` as b where a.areaid = b.areaid AND a.areaid='" . $v['areaid'] . "' group by b.areaid";
                $data[$k]['sub'] = queryMysql($sql);
            }
        }
        $this->assign('data', $data);
        $this->assign('provice', $provice);
        $this->assign('provice_name', $provice_name[0]['areaname']);
        $this->display();
    }

    /**
     * 获取省份
     */
    public function getProvice($id, $pid)
    {
        $m = D('area');
        $field = '';
        $data = $m->field($field)->where('parentid = 0')->select();
        if ($id == 1) {
            $data = $m->field('areaname')->where('areaid = ' . $pid)->select();
        }
        return $data;
    }
}