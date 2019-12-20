<?php
/** Created by 嗝嗝<china_wangyu@aliyun.com>. Date: 2019-11-20  */

namespace paa\exception;


class AuthenticationException extends \Exception
{
    protected $message = '鉴权错误';
    protected $code = 400;
    protected $error_code = 1001;
}