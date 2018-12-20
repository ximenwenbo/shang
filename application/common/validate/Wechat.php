<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30 0030
 * Time: 14:11
 */

namespace app\common\validate;


use think\Validate;

class Wechat extends Validate
{
    protected $rule = [
        ['name', 'require', '菜单名称不能为空'],
        ['sort', 'number','排序只能为数字'],
        ['type', 'require', '类型不能为空'],
        ['value', 'require|url', '类型不能为空|URL地址格式不正确']
    ];

    protected $scene = [
        'menu_add' => ['name', 'sort', 'type', 'value'],
        'menu_edit' => ['name', 'sort', 'type', 'value'],
    ];
}