<?php
/** Created by 嗝嗝<china_wangyu@aliyun.com>. Date: 2019-11-20  */

namespace paa\common\authorize;


use Firebase\JWT\JWT as FirebaseJwt;
use paa\exception\AuthenticationException;
use paa\exception\AuthRefreshException;

class Jwt
{
    protected static $signature;
    protected static $uniqueId;
    protected static $data;

    public static function encode(string $uniqueId, string $signature)
    {
        self::$uniqueId = $uniqueId;
        self::$signature = $signature;
        return [
            'access_token' => static::create(false),
            'refresh_token' => static::create(true)
        ];
    }


    public static function decode(string $token = '')
    {
        if (empty($token)){
            static::check();
        }else{
            static::$data = (array)FirebaseJwt::decode($token,static::getTokenKey(),['HS256']);
        }
        return static::$data;
    }

    public static function getHeaderAuthorization(){
        $authorization = request()->header(config('paa.jwt.param'));
        if (empty($authorization)){
            throw new AuthenticationException();
        }
        try {
            list($type, $token) = explode(' ', $authorization);
        } catch (\Exception $exception) {
            throw new AuthenticationException('authorization信息不正确');
        }
        if ($type !== config('paa.jwt.type')) {
            throw new AuthenticationException('接口认证方式需为'.config('paa.jwt.param'));
        }

        if (!$token) {
            throw new AuthenticationException();
        }
        return [$type, $token];
    }


    public static function check(bool $refresh = false):void
    {
        list($type, $token) = static::getHeaderAuthorization();
        try {
            static::$data = (array)\Firebase\JWT\JWT::decode($token, static::getTokenKey($refresh), ['HS256']);
        } catch (\Firebase\JWT\SignatureInvalidException $exception) {  //签名不正确
            throw new AuthenticationException('令牌签名不正确，请确认令牌有效性或令牌类型');
        } catch (\Firebase\JWT\BeforeValidException $exception) {  // 签名在某个时间点之后才能用
            throw new AuthenticationException('令牌尚未生效');
        } catch (\Firebase\JWT\ExpiredException $exception) {  // token过期
            throw new AuthRefreshException('令牌已过期，刷新浏览器重试');
        } catch (\UnexpectedValueException $exception) {
            throw new AuthenticationException('access_token不正确，' . $exception->getMessage());
        } catch (\Exception $exception) {  //其他错误
            throw new AuthenticationException($exception->getMessage());
        }
    }

    public static function refresh()
    {
        static::check();
        self::$uniqueId = self::$data['uniqueId'];
        self::$signature = self::$data['signature'];
        return ['access_token'=>static::create(true)];
    }


    /**
     * 创建JWT鉴权
     * @return string 是否为刷新鉴权
     * @throws AuthenticationException
     */
    private static function create(bool $refresh = false){
        $payload = config('paa.jwt.payload');
        if (empty($payload)){
            throw new AuthenticationException('请检查paa配置文件中jwt选项是否正确');
        }
        $payload['iat'] = time();
        $payload['uniqueId'] = static::$uniqueId;
        $payload['signature'] = static::$signature;
        $payload['exp'] = static::getTokenExpire($refresh);
        return FirebaseJwt::encode($payload, static::getTokenKey($refresh), 'HS256');
    }


    public static function getTokenKey(bool $refresh = false){
        return $refresh ?  config('paa.jwt.key').".refresh" : config('paa.jwt.key') ;
    }

    public static function getTokenExpire(bool $refresh = false){
        $exp = $refresh ? config('paa.jwt.refresh_exp') : config('paa.jwt.access_exp');
        return $payload['exp'] = time() + $exp;
    }
}
