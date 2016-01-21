<?php
/*
 * @Controller 会员模块
 * @Date 2016/01/18
 * @Author  iredbaby   1596229276@qq.com
 */
namespace Admin\Controller;

Use Common\Controller\AuthController;
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
        $mouth_solt = get_mouth_solt($this->date_start, $this->date_end);
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
     * 会员信息
     */
    public function memberInfo()
    {
        $TradeOrder = D('TradeOrder');
        $map['status'] = array('in', '2,3,4');
        $map['status'] = array('neq', '');
        $count = $TradeOrder->field('buyer')->where($map)->count(('DISTINCT buyer'));
        $Page = new \Think\Page($count, 17);
        $show = $Page->show();
        $data = $TradeOrder->field('DISTINCT buyer,buyer_name,SUM(total) as a,SUM(amount) as b')->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->group('buyer')->order('b desc')->select();
        $all_amount_count = $TradeOrder->where($map)->field('amount')->sum('amount');
        foreach ($data as $k => $v) {
            $member_info[$k]['buyer'] = $v['buyer'];
            $member_info[$k]['buyer_name'] = $v['buyer_name'];
            $member_info[$k]['total'] = $v['a'];
            $member_info[$k]['amount'] = $v['b'];
            $member_info[$k]['rate'] = round($v['b'] / $all_amount_count * 100, 5);
        }

        //导出excel
        if (I('get.type') == 'export') {
            /*导入phpExcel核心类 */


            $fileName = "会员信息";
            $headArr = array('账号', '姓名', '购买数量', '交易额', '购买率', 'a');
            exportExcel($fileName, $headArr, $data); //数据导出
        }
        $this->assign(['member_info' => $member_info, ['day_s' => $day_s], ['day_e' => $day_e]]);
        $this->assign(['show' => $show, 'count' => $count]);
        $this->display();
    }


    /**
     * 获取新注册会员
     * @author iredbaby
     */
    private function get_new_member($date_id)
    {
        $Member = D('Member');
        $map_new['regtime'] = [['gt', $date_id], ['lt', time()]];
        $reg_member_data = $Member->where($map_new)->field('truename,regtime')->select();
        foreach ($reg_member_data as $key => $value) {
            $reg_member_count[] = $value['truename'];
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
        $TradeOrder = D('TradeOrder');
        //按年月日全部付款
        for ($i = 1; $i <= 3; $i++) {
            $map['paytime'] = [['neq', 0], ['gt', format_date($i)], ['lt', time()]];
            $member_type[] = $i;
            $member_count[] = $TradeOrder->where($map)->field('buyer_name,paytime')->count('distinct buyer_name');
        }
        //新会员按月日付款
        for ($i = 2; $i <= 3; $i++) {
            $day_member_name = $this->get_new_member(format_date($i));
            $map_day['paytime'] = [['neq', 0], ['gt', format_date($i)], ['lt', time()]];
            $map_day['buyer_name'] = [['in', $day_member_name]];
            $new_member_type[] = $i;
            $new_member_str = $TradeOrder->where($map_day)->field('buyer_name,paytime')->count('distinct buyer_name');
            if (empty($new_member_str)) {
                $new_member_count[] = 0;
            } else {
                $new_member_count[] = $new_member_str;
            }
        }
        //全部付款
        $map_all['paytime'] = [['neq', 0]];
        $all = $TradeOrder->where($map_all)->field('buyer_name,paytime')->count('distinct buyer_name');

        $this->assign(['day' => $member_count[2], 'month' => $member_count[1], 'year' => $member_count[0], 'all' => $all]);
        $this->assign(['day_new' => $new_member_count[1], 'month_new' => $new_member_count[0]]);
        $this->display();
    }

    /**
     * 会员APP注册
     */
    public function memberRegApp()
    {
        $Member = D('Member');
        $mouth_solt = get_mouth_solt($this->date_start, $this->date_end);
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
        $this->display();
    }
}