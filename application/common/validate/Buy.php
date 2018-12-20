<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30 0030
 * Time: 11:03
 */

namespace app\common\validate;

use think\Validate;

class Buy extends Validate
{
    protected $rule = [
        ['address_realname', 'require', '真实姓名必填'],
        ['address_detail', 'require', '地址为必填'],
    ];

    protected $scene = [
        'add_addr' => ['address_realname', 'address_detail'],
    ];

}