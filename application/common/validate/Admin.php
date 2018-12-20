<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30 0030
 * Time: 14:11
 */

namespace app\common\validate;


use think\Validate;

class Admin extends Validate
{
    protected  $rule = [
        ['admin_name', 'require|length:3,12|unique:admin', '登录名必填|登录名长度在3到12位|登录名已存在'],
        ['admin_password', 'require', '密码为必填'],
        ['admin_gid', 'require', '权限组为必填']
    ];

    protected $scene = [
        'admin_add' => ['admin_name', 'admin_password', 'admin_gid'],
    ];
}