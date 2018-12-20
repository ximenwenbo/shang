<?php

namespace app\common\model;

use think\Model;

class Spec extends Model {
public $page_info;


    /**
     * 规格列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $page 分页
     * @param type $order 排序
     * @return type
     */
    public function getSpecList($condition, $page = '', $order = 'sp_sort asc') {
        if($page){
            $result= db('spec')->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info=$result;
            return $result->items();
        }else{
            return db('spec')->where($condition)->order($order)->select();
        }
    }

    /**
     * 单条规格信息
     * @access public
     * @author csdeshang
     * @param type $sp_id 规格ID
     * @param type $field 字段
     * @return type
     */
    public function getSpecInfo($sp_id, $field = '*') {
        return db('spec')->where('sp_id',$sp_id)->field($field)->find();
    }

    /**
     * 规格值列表
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @param type $order 排序
     * @return type
     */
    public function getSpecvalueList($where, $field = '*', $order = 'spvalue_sort asc,spvalue_id asc') {
        $result = db('specvalue')->field($field)->where($where)->order($order)->select();
        return empty($result) ? array() : $result;
    }

    /**
     * 更新规格值
     * @access public
     * @author csdeshang
     * @param array $update 更新数据
     * @param array $where  条件
     * @return boolean
     */
    public function editSpecvalue($update, $where) {
        $result = db('specvalue')->where($where)->update($update);
        return $result;
    }

    /**
     * 增加规格值 
     * @access public
     * @author csdeshang
     * @param array $data 数据
     * @return boolean
     */
    public function addSpecvalue($data) {
        $result = db('specvalue')->insertGetId($data);
        return $result;
    }

    /**
     * 添加规格 多条
     * @access public
     * @author csdeshang
     * @param array $data 数据
     * @return boolean
     */
    public function addSpecvalueALL($data) {
        $result = db('specvalue')->insertAll($data);
        return $result;
    }

    /**
     * 删除规格值
     * @access public
     * @author csdeshang
     * @param array $where 条件
     * @return boolean
     */
    public function delSpecvalue($where) {
        $result = db('specvalue')->where($where)->delete();
        return $result;
    }

    /**
     * 更新规格信息
     * @access public
     * @author csdeshang
     * @param type $update 更新数据
     * @param type $condition 条件
     * @return boolean
     */
    public function editSpec($update, $condition) {
        if (empty($update)) {
            return false;
        }
        return db('spec')->where($condition)->update($update);
    }

    /**
     * 添加规格信息
     * @access public
     * @author csdeshang
     * @param type $data 数据
     * @return type
     */
    public function addSpec($data) {
        // 规格表插入数据
        $result = db('spec')->insertGetId($data);
        return $result;
    }
 
    /**
     * 删除规格
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function delSpec($condition) {
        return db('spec')->where($condition)->delete();
    }

}

?>
