<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30 0030
 * Time: 14:11
 */

namespace app\common\validate;


use think\Validate;

class Voucher extends Validate
{
    protected $rule = [
        ['vouchertemplate_title', 'require|length:1,50', '模版名称不能为空且不能大于50个字符'],
        ['vouchertemplate_total', 'require|number', '可发放数量不能为空且必须为整数'],
        ['vouchertemplate_price', 'require|number', '模版面额不能为空且必须为整数，且面额不能大于限额'],
        ['vouchertemplate_limit', 'require', '模版使用消费限额不能为空且必须是数字'],
        ['vouchertemplate_desc', 'require|length:1,255', '模版描述不能为空且不能大于255个字符']
    ];

    protected $scene = [
        'templateadd' => ['vouchertemplate_title', 'vouchertemplate_total', 'vouchertemplate_price', 'vouchertemplate_limit', 'vouchertemplate_desc'],
        'templateedit' => ['vouchertemplate_title', 'vouchertemplate_total', 'vouchertemplate_price', 'vouchertemplate_limit', 'vouchertemplate_desc'],
    ];
}