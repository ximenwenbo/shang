<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30 0030
 * Time: 14:11
 */

namespace app\common\validate;


use think\Validate;

class Navigation extends Validate
{
    protected $rule = [
        ['nav_sort', 'number', '排序只能为数字'],
        ['nav_title', 'require', '标题不能为空'],
    ];

    protected $scene = [
        'add' => ['nav_sort', 'nav_title'],
        'edit' => ['nav_sort', 'nav_title'],
    ];
}