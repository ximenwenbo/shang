<?php

namespace app\admin\controller;

use think\Lang;

class Vrgroupbuy extends AdminControl {

    public function _initialize() {
        parent::_initialize(); // TODO: Change the autogenerated stub
        Lang::load(APP_PATH . 'admin/lang/' . config('default_lang') . '/groupbuy.lang.php');
    }

    /*
     * 列表分类
     */

    public function index() {
        $vrgroupbuyclass_model = model('vrgroupbuyclass');
        $vrgroupbuyclass_list = $vrgroupbuyclass_model->getVrgroupbuyclassList();
        $this->assign('vrgroupbuyclass_list', $vrgroupbuyclass_list);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    /**
     * 添加虚拟抢购页面
     */
    public function groupbuy_add_vr() {
        if (!request()->isPost()) {
            // 虚拟抢购分类
            $this->assign('groupbuy_vr_classes', model('groupbuy')->getGroupbuyVrClasses());
            $vrgroupbuyclass_model = model('vrgroupbuyclass');
            $classlist = $vrgroupbuyclass_model->getVrgroupbuyclassList(array('vrgclass_parent_id' => 0));
            $this->assign('classlist', $classlist);
            $this->setAdminCurItem('groupbuy_add_vr');
            return $this->fetch('groupbuy_add_vr');
        } else {
            //获取提交的数据
            $goods_id = intval(input('post.groupbuy_goods_id'));
            if (empty($goods_id)) {
                ds_json_encode(10001,lang('param_error'));
            }

            $groupbuy_model = model('groupbuy');
            $goods_model = model('goods');

            $goods_info = $goods_model->getGoodsInfoByID($goods_id);
            if (empty($goods_info)) {
                ds_json_encode(10001,lang('param_error'));
            }

            $param = array();
            $param['groupbuy_name'] = input('post.groupbuy_name');
            $param['groupbuy_remark'] = input('post.remark');
            $param['groupbuy_starttime'] = strtotime(input('post.start_time'));
            $param['groupbuy_endtime'] = strtotime(input('post.end_time'));
            $param['groupbuy_price'] = floatval(input('post.groupbuy_price'));
            $param['groupbuy_rebate'] = ds_price_format(floatval(input('post.groupbuy_price')) / floatval($goods_info['goods_price']) * 10);
            $param['groupbuy_image'] = input('post.groupbuy_image');
            $param['groupbuy_image1'] = input('post.groupbuy_image1');
            $param['virtual_quantity'] = intval(input('post.virtual_quantity'));
            $param['groupbuy_upper_limit'] = intval(input('post.upper_limit'));
            $param['groupbuy_intro'] = input('post.groupbuy_intro');
            $param['gclass_id'] = input('post.gclass_id', 0);
            $param['goods_id'] = $goods_info['goods_id'];
            $param['goods_commonid'] = $goods_info['goods_commonid'];
            $param['goods_name'] = $goods_info['goods_name'];
            $param['goods_price'] = $goods_info['goods_price'];

            if ($param['groupbuy_upper_limit'] > 0 && $goods_info['virtual_limit'] > 0 && $param['groupbuy_upper_limit'] > $goods_info['virtual_limit']) {
                ds_json_encode(10001,sprintf(lang('virtual_panic_buying'), $param['groupbuy_upper_limit'], $goods_info['virtual_limit']));
            }

            $param += array(
                'groupbuy_is_vr' => 1,
                'vr_class_id' => (int) input('post.class'),
                'vr_s_class_id' => (int) input('post.s_class'),
            );

            //保存
            $result = $groupbuy_model->addGroupbuy($param);
            if ($result) {
                $this->log(lang('release_snap_up') . $param['groupbuy_name'] . '，' . lang('ds_goods_name') . '：' . $param['goods_name']);
                ds_json_encode(10000,lang('groupbuy_add_success'));
            } else {
                ds_json_encode(10001,lang('groupbuy_add_fail'));
            }
        }
    }

    public function ajax_vr_class() {
        $vrgclass_id = intval(input('param.vrgclass_id'));
        if (empty($vrgclass_id)) {
            exit('false');
        }

        $condition = array();
        $condition['vrgclass_parent_id'] = $vrgclass_id;

        $vrgroupbuyclass_model = model('vrgroupbuyclass');
        $class_list = $vrgroupbuyclass_model->getVrgroupbuyclassList($condition);

        if (!empty($class_list)) {
            echo json_encode($class_list);
        } else {
            echo 'false';
        }

        exit;
    }

    /**
     * 选择活动虚拟商品
     */
    public function search_vr_goods() {
        $goods_model = model('goods');
        $condition = array();
        $condition['goods_name'] = array('like', '%' . input('param.goods_name') . '%');
        $goods_list = $goods_model->getGoodsCommonListForVrPromotion($condition, '*', 8);

        $this->assign('goods_list', $goods_list);
        $this->assign('show_page', $goods_model->page_info->render());
        echo $this->fetch('search_goods');
    }

    /*
     * 添加分类
     */

    public function class_add() {
        if (request()->isPost()) { //添加虚拟抢购分类
            // 数据验证
            $data = [
                'vrgclass_name' => input('post.vrgclass_name'),
                'vrgclass_sort' => input('post.vrgclass_sort'),
            ];

            $vrgroupbuy_validate = validate('vrgroupbuy');
            if (!$vrgroupbuy_validate->scene('class_add')->check($data)) {
                $this->error($vrgroupbuy_validate->getError());
            }

            $params = array();
            $params['vrgclass_name'] = trim(input('post.vrgclass_name'));
            $params['vrgclass_sort'] = intval(input('post.vrgclass_sort'));
            if (intval(input('post.vrgclass_parent_id')) > 0) {
                $params['vrgclass_parent_id'] = input('post.vrgclass_parent_id');
            } else {
                $params['vrgclass_parent_id'] = 0;
            }

            $vrgroupbuyclass_model = model('vrgroupbuyclass');
            $res = $vrgroupbuyclass_model->addVrgroupbuyclass($params); //添加分类
            if ($res) {
                // 删除虚拟抢购分类缓存
                model('groupbuy')->dropCachedData('groupbuyvrclasses');

                $this->log('添加虚拟抢购分类[ID:' . $res . ']', 1);

                dsLayerOpenSuccess(lang('ds_common_op_succ'));
            } else {
                $this->error(lang('ds_common_op_fail'));
            }
        } else {
            $vrgroupbuyclass_model = model('vrgroupbuyclass'); //一级分类
            $vrgroupbuyclass_list = $vrgroupbuyclass_model->getVrgroupbuyclassList(array('vrgclass_parent_id' => 0));
            $this->assign('vrgroupbuyclass_list', $vrgroupbuyclass_list);
            $this->setAdminCurItem('class_add');
            $parent_id = input('param.vrgclass_parent_id');
            $this->assign('vrgclass_parent_id', !empty($parent_id) ? intval($parent_id) : 0);
            return $this->fetch('class_form');
        }
    }

    /*
     * 编辑分类
     */

    public function class_edit() {
        if (request()->isPost()) {
            // 数据验证
            $data = [
                'vrgclass_name' => input('post.vrgclass_name'),
                'vrgclass_sort' => input('post.vrgclass_sort'),
            ];

            $vrgroupbuy_validate = validate('vrgroupbuy');
            if (!$vrgroupbuy_validate->scene('class_edit')->check($data)) {
                $this->error($vrgroupbuy_validate->getError());
            }

            $params = array();
            $params['vrgclass_name'] = trim(input('post.vrgclass_name'));
            $params['vrgclass_sort'] = intval(input('post.vrgclass_sort'));
            if (intval(input('post.vrgclass_parent_id')) > 0) {
                $params['vrgclass_parent_id'] = input('post.vrgclass_parent_id');
            } else {
                $params['vrgclass_parent_id'] = 0;
            }

            $condition = array(); //条件
            $condition['vrgclass_id'] = intval(input('param.vrgclass_id'));

            $vrgroupbuyclass_model = model('vrgroupbuyclass');
            $result = $vrgroupbuyclass_model->editVrgroupbuyclass($condition, $params);

            if ($result >= 0) {
                // 删除虚拟抢购分类缓存
                model('groupbuy')->dropCachedData('groupbuyvrclasses');

                $this->log('编辑虚拟抢购分类[ID:' . intval(input('param.vrgclass_id')) . ']', 1);
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
            } else {
                $this->error(lang('ds_common_op_fail'));
            }
        } else {
            $vrgroupbuyclass_model = model('vrgroupbuyclass'); //分类信息
            $class = $vrgroupbuyclass_model->getVrgroupbuyclassInfo(array('vrgclass_id' => intval(input('param.vrgclass_id'))));
            if (empty($class)) {
                $this->error('该分类不存在');
            }
            $this->assign('class', $class);

            $vrgroupbuyclass_list = $vrgroupbuyclass_model->getVrgroupbuyclassList(array('vrgclass_parent_id' => 0));
            $this->assign('vrgroupbuyclass_list', $vrgroupbuyclass_list);
            $this->setAdminCurItem('class_edit');
            return $this->fetch('class_form');
        }
    }

    /*
     * 删除分类
     */

    public function class_del() {
        $vrgclass_id = input('param.vrgclass_id');
        $vrgclass_id_array = ds_delete_param($vrgclass_id);
        if ($vrgclass_id_array == FALSE) {
            ds_json_encode(10001, lang('param_error'));
        }
        $vrgroupbuy_model = model('vrgroupbuyclass');
        foreach ($vrgclass_id_array as $val) {
            $class = $vrgroupbuy_model->getVrgroupbuyclassInfo(array('vrgclass_id' => $val));
            if ($class['vrgclass_parent_id'] == 0) {
                $class = $vrgroupbuy_model->delVrgroupbuyclass(array('vrgclass_parent_id' => $class['vrgclass_id']));
            }
            $class = $vrgroupbuy_model->delVrgroupbuyclass(array('vrgclass_id' => $val));
        }
        // 删除虚拟抢购分类缓存
        model('groupbuy')->dropCachedData('groupbuy_vr_classes');
        $this->log('删除虚拟抢购分类[ID:' . input('post.vrgclass_id') . ']', 1);
        ds_json_encode(10000, lang('ds_common_del_succ'));
    }

    public function groupbuy_goods_info()
    {
        $goods_commonid = intval(input('param.goods_commonid'));

        $data = array();
        $data['result'] = true;

        $goods_model = model('goods');

        $condition = array();
        $condition['goods_commonid'] = $goods_commonid;
        $goods_list = $goods_model->getGoodsOnlineList($condition);

        if (empty($goods_list)) {
            $data['result'] = false;
            $data['message'] = lang('param_error');
            echo json_encode($data);
            die;
        }

        $goods_info = $goods_list[0];
        $data['goods_id'] = $goods_info['goods_id'];
        $data['goods_name'] = $goods_info['goods_name'];
        $data['goods_price'] = $goods_info['goods_price'];
        $data['goods_image'] = goods_thumb($goods_info, 240);
        $data['goods_href'] = url('Goods/index', array('goods_id' => $goods_info['goods_id']));

        if ($goods_info['is_virtual']) {
            $data['is_virtual'] = 1;
            $data['virtual_indate'] = $goods_info['virtual_indate'];
            $data['virtual_indate_str'] = date('Y-m-d H:i', $goods_info['virtual_indate']);
            $data['virtual_limit'] = $goods_info['virtual_limit'];
        }

        echo json_encode($data);
        die;
    }

    public function ajax() {
        $field = input('param.column');
        $id = input('param.id');
        $value = input('param.value');

        switch (input('param.column')) {
            case 'vrgclass_name':
                if (mb_strlen((string) $value, 'utf-8') > 10)
                    return;
                break;
            case 'vrgclass_sort':
                if ($value < 0 || $value > 255)
                    return;
                break;

            default:
                return;
        }

        switch (input('param.branch')) {
            case 'class':
                $vrgroupbuyclass_model = model('vrgroupbuyclass');
                $res = $vrgroupbuyclass_model->editVrgroupbuyclass(array('vrgclass_id' => $id), array($field => $value));
                if ($res) {
                    // 删除虚拟抢购分类缓存
                    model('groupbuy')->dropCachedData('groupbuy_vr_classes');

                    $this->log('编辑虚拟抢购分类[ID:' . $id . ']', 1);
                    echo 'true';
                } else {
                    echo 'false';
                }
                exit;

            default:
                return;
        }
    }

    protected function getAdminItemList() {

        $menu_array = array(
            array(
                'name' => 'index', 'text' => '分类管理', 'url' => url('Vrgroupbuy/index')
            ),
            array(
                'name' => 'groupbuy_add_vr', 'text' => '添加虚拟产品', 'url' => url('Vrgroupbuy/groupbuy_add_vr')
            ),
            array(
                'name' => 'class_add', 'text' => '添加分类', 'url' => "javascript:dsLayerOpen('" . url('Vrgroupbuy/class_add') . "','添加分类')"
            )
        );
        return $menu_array;
    }

}
