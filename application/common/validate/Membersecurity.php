<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30 0030
 * Time: 11:03
 */

namespace app\common\validate;

use think\Validate;

class Membersecurity extends Validate
{
    protected $rule = [
        ['email', 'email', '请正确填写邮箱'],
        ['password', 'require','请正确输入密码'],
        ['confirm_password', 'require', '请正确输入确认密码'],
        ['mobile', 'require', '请正确填写手机号'],
        ['vcode', 'require','请正确填写手机验证码'],
    ];

    protected $scene = [
        'send_bind_email' => ['email'],
        'modify_pwd' => ['password', 'confirm_password'],
        'modify_paypwd' => ['password', 'confirm_password'],
        'modify_mobile' => ['mobile', 'vcode'],
        'send_modify_mobile' => ['mobile'],
    ];

}