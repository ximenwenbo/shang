<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30 0030
 * Time: 11:03
 */

namespace app\common\validate;

use think\Validate;

class Album extends Validate
{
    protected $rule = [
        ['aclass_name', 'require', '相册名称必填'],
        ['aclass_des', 'require', '相册描述必填'],
        ['aclass_sort', 'require', '相册排序必填']
    ];

    protected $scene = [
        'album_add' => ['aclass_name','aclass_des','aclass_sort']
    ];

}