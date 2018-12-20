<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30 0030
 * Time: 14:11
 */

namespace app\common\validate;


use think\Validate;

class Login extends Validate
{
    protected $rule = [
        ['admin_name', 'require|min:5', '帐号为必填|帐号长度至少为5位'],
        ['admin_password', 'require|min:6', '密码为必填|密码长度至少为6位'],
        ['captcha', 'require|min:3', '验证码为必填|验证码长度至少为3位'],
        ['member_name', 'require|length:3,15', '账户为必填|帐号长度必须为3-15之间'],
        ['member_password', 'require|length:6,20', '密码为必填|密码长度必须为6-20之间']
    ];

    protected $scene = [
        'index' => ['admin_name', 'admin_password', 'captcha'],
        'login' => ['member_name', 'member_password'],
        'register' => ['member_name', 'member_password'],
    ];
}