<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30 0030
 * Time: 14:11
 */

namespace app\common\validate;


use think\Validate;

class Message extends Validate
{
    protected $rule = [
        ['code', 'require', '编号不能为空'],
        ['title', 'require', '标题不能为空'],
        ['content', 'require', '正文不能为空'],
    ];

    protected $scene = [
        'email_tpl_edit' => ['title', 'content'],
    ];
}