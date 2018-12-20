<?php
/**
 * 售后统计分析
 */

namespace app\admin\controller;

use think\Lang;
use think\Loader;

class Stataftersale extends AdminControl
{
    private $search_arr;//处理后的参数

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        Lang::load(APP_PATH.'admin/lang/'.config('default_lang').'/stat.lang.php');
        Loader::import('mall.statistics');
        Loader::import('mall.datehelper');
        $stat_model = model('stat');
        //存储参数
        $this->search_arr = $_REQUEST;
        //处理搜索时间
        if (in_array(request()->action(),array('refund'))){
            $this->search_arr = $stat_model->dealwithSearchTime($this->search_arr);
            //获得系统年份
            $year_arr = getSystemYearArr();
            //获得系统月份
            $month_arr = getSystemMonthArr();
            //获得本月的周时间段
            $week_arr = getMonthWeekArr($this->search_arr['week']['current_year'], $this->search_arr['week']['current_month']);
            $this->assign('year_arr', $year_arr);
            $this->assign('month_arr', $month_arr);
            $this->assign('week_arr', $week_arr);
        }
        $this->assign('search_arr', $this->search_arr);
    }

    /**
     * 退款统计
     */
    public function refund(){
        $where = array();
        if(!isset($this->search_arr['search_type'])){
            $this->search_arr['search_type'] = 'day';
        }
        $stat_model = model('stat');

        //获得搜索的开始时间和结束时间
        $searchtime_arr = $stat_model->getStarttimeAndEndtime($this->search_arr);

        $field = ' SUM(refund_amount) as amount ';
        if($this->search_arr['search_type'] == 'day'){
            //构造横轴数据
            for($i=0; $i<24; $i++){
                $stat_arr['xAxis']['categories'][] = "$i";
                $statlist[$i] = 0;
            }
            $field .= ' ,HOUR(FROM_UNIXTIME(add_time)) as timeval ';
        }
        if($this->search_arr['search_type'] == 'week'){
            //构造横轴数据
            for($i=1; $i<=7; $i++){
                $tmp_weekarr = getSystemWeekArr();
                //横轴
                $stat_arr['xAxis']['categories'][] = $tmp_weekarr[$i];
                unset($tmp_weekarr);
                $statlist[$i] = 0;
            }
            $field .= ' ,WEEKDAY(FROM_UNIXTIME(add_time))+1 as timeval ';
        }
        if($this->search_arr['search_type'] == 'month'){
            //计算横轴的最大量（由于每个月的天数不同）
            $dayofmonth = date('t',$searchtime_arr[0]);
            //构造横轴数据
            for($i=1; $i<=$dayofmonth; $i++){
                //横轴
                $stat_arr['xAxis']['categories'][] = $i;
                $statlist[$i] = 0;
            }
            $field .= ' ,day(FROM_UNIXTIME(add_time)) as timeval ';
        }
        $where = array();
        $where['add_time'] = array('between',$searchtime_arr);
        $statlist_tmp = $stat_model->statByRefundreturn($where, $field, 0, 0, 'timeval asc', 'timeval');
        if ($statlist_tmp){
            foreach((array)$statlist_tmp as $k=>$v){
                $statlist[$v['timeval']] = floatval($v['amount']);
            }
        }
        //得到统计图数据
        $stat_arr['legend']['enabled'] = false;
        $stat_arr['series'][0]['name'] = '退款金额';
        $stat_arr['series'][0]['data'] = array_values($statlist);
        $stat_arr['title'] = '退款金额统计';
        $stat_arr['yAxis'] = '金额';
        $stat_json = getStatData_LineLabels($stat_arr);
        $this->assign('stat_json',$stat_json);
        $this->assign('searchtime',implode('|',$searchtime_arr));
        $this->setAdminCurItem('refund');
        return $this->fetch('aftersale_refund');
    }
    /**
     * 退款统计
     */
    public function refundlist(){
        $refundreturn_model = model('refundreturn');
        $refundstate_arr = $this->getRefundStateArray();
        $where = array();
        $statlist= array();
        $searchtime_arr_tmp = explode('|',$this->search_arr['t']);
        foreach ((array)$searchtime_arr_tmp as $k=>$v){
            $searchtime_arr[] = intval($v);
        }
        $where['add_time'] = array('between',$searchtime_arr);
        if (isset($this->search_arr['exporttype']) && $this->search_arr['exporttype'] == 'excel'){
            $refundlist_tmp = $refundreturn_model->getRefundreturnList($where, 0);
        } else {
            $refundlist_tmp = $refundreturn_model->getRefundreturnList($where, 10);
        }
        $statheader = array();
        $statheader[] = array('text'=>'订单编号','key'=>'order_sn');
        $statheader[] = array('text'=>'退款编号','key'=>'refund_sn');
        $statheader[] = array('text'=>'商品名称','key'=>'goods_name','class'=>'alignleft');
        $statheader[] = array('text'=>'买家会员名','key'=>'buyer_name');
        $statheader[] = array('text'=>'申请时间','key'=>'add_time');
        $statheader[] = array('text'=>'退款金额','key'=>'refund_amount');
        $statheader[] = array('text'=>'平台确认','key'=>'refund_state');
        foreach ((array)$refundlist_tmp as $k=>$v){
            $tmp = $v;
            foreach ((array)$statheader as $h_k=>$h_v){
                $tmp[$h_v['key']] = $v[$h_v['key']];
                if ($h_v['key'] == 'add_time'){
                    $tmp[$h_v['key']] = @date('Y-m-d',$v['add_time']);
                }
                if ($h_v['key'] == 'refund_state'){
                    $tmp[$h_v['key']] = $refundstate_arr['admin'][$v['refund_state']];
                }
                if ($h_v['key'] == 'goods_name'){
                    $tmp[$h_v['key']] = '<a href="'.url('Goods/index', array('goods_id' => $v['goods_id'])).'" target="_blank">'.$v['goods_name'].'</a>';
                }
            }
            $statlist[] = $tmp;
        }
        if (isset($this->search_arr['exporttype']) && $this->search_arr['exporttype'] == 'excel'){
            //导出Excel
            $excel_obj = new \excel\Excel();
            $excel_data = array();
            //设置样式
            $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
            //header
            foreach ((array)$statheader as $k=>$v){
                $excel_data[0][] = array('styleid'=>'s_title','data'=>$v['text']);
            }
            //data
            foreach ((array)$statlist as $k=>$v){
                foreach ((array)$statheader as $h_k=>$h_v){
                    $excel_data[$k+1][] = array('data'=>$v[$h_v['key']]);
                }
            }
            $excel_data = $excel_obj->charset($excel_data,CHARSET);
            $excel_obj->addArray($excel_data);
            $excel_obj->addWorksheet($excel_obj->charset('退款记录',CHARSET));
            $excel_obj->generateXML($excel_obj->charset('退款记录',CHARSET).date('Y-m-d-H',time()));
            exit();
        } else {
            $this->assign('statheader',$statheader);
            $this->assign('statlist',$statlist);
            $this->assign('show_page',$refundreturn_model->page_info->render());
            $this->assign('searchtime',input('param.t'));
            $this->assign('actionurl',url('Stataftersale/refundlist',['t'=>$this->search_arr['t']]));
            echo $this->fetch('stat_listandorder');
        }
    }
    
    function getRefundStateArray($type = 'all') {
        $state_array = array(
            '1' => lang('ds_examine'),
            '2' => lang('refund_state_yes'),
            '3' => lang('refund_state_no')
        ); //卖家处理状态:1为待审核,2为同意,3为不同意
        $this->assign('state_array', $state_array);

        $admin_array = array(
            '1' => '处理中',
            '2' => '待处理',
            '3' => '已完成'
        ); //确认状态:1为买家或卖家处理中,2为待平台管理员处理,3为退款退货已完成
        $this->assign('admin_array', $admin_array);

        $state_data = array(
            'admin' => $admin_array
        );
        if ($type == 'all') {
            return $state_data; //返回所有
        }
        return $state_data[$type];
    }

    protected function getAdminItemList()
    {
       $menu_array=array(
           array('name'=>'refund','text'=>lang('stat_refund'),'url'=>url('Stataftersale/refund')),
       );
       return $menu_array;
    }
}