<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/16/16
 * Time: 5:42 PM
 */

namespace Admin\Controller;


use Admin\FixedData;
use Common\Controller\AuthController;
use Common\Tools;

class PerformanceController extends AuthController
{
    use FixedData;

    protected $DepartMember; //部门模型
    public $Message; //留言模型

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->DepartMember = D('DepartMember');
        $this->Message = D('Message');
    }

    public function performance_index(){}

    /**
     * 留言询价处理
     */
    public function messageAndInquiryProcessing()
    {
        $departmembers = $this->DepartMember->where(['bumen'=>1])->field(['id'=>'member_id','username','bumen'=>'depart_id'])->order()->limit()->select();
        foreach($departmembers as $k => $v){
            $departmembers[$k]['depart_name'] = $this->depart_data[$v['depart_id']]['depart_name'];
            $x = D('Member')->where(['username'=>$v['username']])->field(['truename'])->find();
            $departmembers[$k]['member_name'] = $x['truename'];
            // 询价统计
            $departmembers[$k]['inquiry_status'] = $this->getInquiry($v['username'],true);
        }

        $this->assign(['departmembers'=>$departmembers]);
        $this->display();
    }

    /**
     * ajax询价处理
     */
    public function getAjaxInquiryProcessing()
    {
        $column_index = [
            "content",
            "amswer",
            "member_truename",
            "product_id",
            "product_title",
            "product_standard",
            "price",
            "addtime",
        ];
        $column_search = [
            "message.content",
            "message.answer",
            "member.truename",
            "product.itemid",
            "product.title",
            "product.standard",
            "product.price",
            "message.addtime",
        ];

        for($i = 0; $i < count($column_index); $i++){
            $field[] = " {$column_search[$i]} AS {$column_index[$i]} ";
        }
        $field = Tools::arr2str($field);
        $draw = $_GET['draw'];
        $start = $_GET['start'];
        $limit = $_GET['length'];
        $order = $_GET['order'];
        $order = "{$column_index[$order[0]['column']]} {$order[0]['dir']}";

        if($_GET['search']['value']!=''){
            $search_time = Tools::str2arr($_GET['search']['value']);
            $this->month_start = strtotime($search_time[0] . "-01 00:00:00");
            $this->month_end = strtotime($search_time[1] . "-01 23:59:59");
            $y[] = " message.addtime > {$this->month_start} AND  message.addtime < {$this->month_end} ";
        }
        foreach ($_GET['columns'] as $k => $v) {
            if ($v['search']['value'] != '') {
                if($column_search[$v['data']]=="addtime"){}else{
                    $y[] = "{$column_search[$v['data']]} LIKE '%{$v[search][value]}%'";
                }
            }
        }
        $search = '';
        if (count($y) > 0) {
            $search = " AND " . Tools::arr2str($y, " AND ");
        }

        $sql = "SELECT COUNT(message.itemid) AS total
            FROM __MALL_message AS message
            INNER JOIN __MALL_sell_5 AS product ON message.cpid = product.itemid
            INNER JOIN __MALL_member AS member ON message.msgbelong = member.username
            WHERE message.is_xunjia = 1 {$search}";
        $z = $this->MallDb->list_query($sql);
        $total = $z[0]['total'];
        $sql = "SELECT  {$field}
            FROM __MALL_message AS message
            INNER JOIN __MALL_sell_5 AS product ON message.cpid = product.itemid
            INNER JOIN __MALL_member AS member ON message.msgbelong = member.username
            WHERE message.is_xunjia = 1 {$search}
            ORDER BY {$order}
            LIMIT {$start}, {$limit}";
        $data = $this->MallDb->list_query($sql);

        foreach ($data as $k => $v) {
            foreach ($column_index as $key => $value) {
                if($value == 'addtime'){
                    $x[$k][] = date("Y-m-d H:i", $v[$value]);
                }else{
                    $x[$k][] = $v[$value];
                }
            }
        }
        //获取Datatables发送的参数 必要
        $show = [
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $x,
        ];
        $x = json_encode($show);
        echo $x;
    }

    /**
     * 询价统计
     */
    private function getInquiry($depart_name,$getStatusCount=false,$field=false)
    {
        if($getStatusCount){
            $sql = "SELECT message.itemid, message.addtime, message.fixtime, product.price FROM __MALL_message AS message
                LEFT JOIN __MALL_sell_5 AS product ON message.cpid = product.itemid
                WHERE message.msgbelong = '{$depart_name}' AND message.addtime > {$this->month_start} AND message.addtime < {$this->month_end}";
            $x = $this->MallDb->list_query($sql);
            $inquiry_info['count_no'] = 0;
            $inquiry_info['count_yes'] = 0;
            $inquiry_info['count_timeout'] = 0;
            $inquiry_info['total'] = 0;
            foreach($x as $k => $v){
                if($v['price'] == 0){
                    $inquiry_info['count_no']++;
                }else{
                    $inquiry_info['count_yes'] ++;
                    $_time = $v['fixtime'] - $v['addtime'];
                    if($_time>24*3600){
                        $inquiry_info['count_timeout']++;
                    }
                }
            }
            $inquiry_info['total'] = $inquiry_info['count_no'] + $inquiry_info['count_yes'];
            $inquiry_info['disposal_rate'] = ($inquiry_info['count_yes']*100/$inquiry_info['total']) . "%";
            $inquiry_info['disposal_time_rate'] = (($inquiry_info['count_yes'] - $inquiry_info['count_timeout'])*100/$inquiry_info['total']) . "%";
        }
        return $inquiry_info;
    }

    /**
     * 询价转换提成
     */
    public function inquiryConversionCommission()
    {
        $sql = "SELECT depart.username, member.truename, COUNT(trade.itemid) AS trade_count FROM __MALL_depart AS depart
            INNER JOIN __MALL_member AS member ON depart.username = member.username
            INNER JOIN __MALL_finance_trade AS trade ON depart.username = trade.xunjia_ticheng
            WHERE trade.status IN(2, 3, 4) AND depart.bumen = 1
            GROUP BY depart.username";
        $list = $this->MallDb->list_query($sql);
        $this->assign(['list'=>$list]);
        $this->display();
    }
}