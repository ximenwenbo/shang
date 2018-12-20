<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30 0030
 * Time: 14:11
 */

namespace app\common\validate;


use think\Validate;

class Link extends Validate
{
    protected $rule = [
        ['link_sort', 'number', '排序只能为数字'],
        ['link_title', 'require', '链接名称不能为空'],
    ];

    protected $scene = [
        'add' => ['link_sort', 'link_title'],
        'edit' => ['link_sort', 'link_title'],
    ];
}