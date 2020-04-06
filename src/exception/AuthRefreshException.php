<?php
/** Created by 嗝嗝<china_wangyu@aliyun.com>. Date: 2019-11-20  */

namespace paa\exception;


class AuthRefreshException extends \Exception
{
    protected $message = '刷新鉴权有误';
    protected $code = 400;
    protected $error_code = 10050;
}
