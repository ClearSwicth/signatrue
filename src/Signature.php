<?php

namespace ClearSwitch\Signatrue;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

/**
 * 认证器
 * @package App
 */
class Signature
{

    protected $user;
    /**
     * 请求必须有的参数
     */
    const MUST_REQUEST_PARAMS = ['ts', 'user_id', 'sign'];
    /**
     * 提供者
     * @var  UserProvider
     */
    protected $provider;

    /**
     * 请求
     * @var  \Illuminate\Http\Request;
     */
    protected $request;

    /**
     * @var 错误信息
     */
    protected  $errMessage;

    /**
     * @var 错误状态码
     */
    protected $errCode;

    /**
     * @var 有效时间
     */
    protected static $vailRequestPeriod = 3600;

    /**
     * @var int token默认的有效时间为一周
     */
    public  static $tokenPeriod=604800;
    /**
     * 设置提供者
     * @param $provider
     * @return $this
     * @author daikai
     */

    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * 设置请求
     * @param $request
     * @return $this
     * @author daikai4
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * 验证过程
     * @return bool
     * @author daikai
     */
    public function validated()
    {
        $input = $this->request->all();//请求数据
        $path = $this->request->path();//获得请求地址
        $requestMethod = $this->request->method();//获得请求方法
        return $this->vailRequest($input)
            && $this->vailRequestTime($input['ts'])
            && $this->vialUser($input['user_id'])
            && $this->vailLoginStatus()
            && $this->vailSign($input,$requestMethod,$path);
    }

    /**
     * 验证求亲的合法性
     * @author daikai
     */
    public function vailRequest($input)
    {
        foreach (self::MUST_REQUEST_PARAMS as $param) {
            if (!isset($input[$param])) {
                $this->errCode = 40301;
                $this->errMessage = '请求缺少必要的参数：' . $param;
                return false;
            }
        }
        return true;
    }

    /**
     * 判断请求是否过期
     * @param $ts
     * @return bool
     * @author daikai
     */
    public function vailRequestTime($ts)
    {
        if (time() - $ts > static::$vailRequestPeriod) {
            $this->errCode = 40302;
            $this->errMessage = '请求过期';
            return false;
        }
        return true;
    }

    /**
     * 验证用户是否合法
     * @param $userId
     * @return bool
     * @author daikai
     */
    public function vialUser($userId)
    {
        if (!$this->getUser($userId)) {
            $this->errCode = 40303;
            $this->errMessage = '用户不存在的';
            return false;
        }
        return true;
    }

    /**
     * 验证登陆状态是否过期
     * @return bool
     * @author daikai
     */
    public function vailLoginStatus()
    {
        if ($this->user->token_expired_at < time()) {
            $this->errCode = 40304;
            $this->errMessage = '登陆过期';
            return false;
        }
        return true;
    }

    /**
     * 验证签名
     * @param $input
     * @param $requestMethod
     * @param $path
     * @author daikai
     */
    public function vailSign($input,$requestMethod,$path){
        $sign=$this->makeSign($input,$requestMethod,$path);
        if($sign!=$input['sign']){
            $this->errCode = 40305;
            $this->errMessage = '签名验证失败:'.$sign;
            return false;
        }
        return true;
    }

    /**
     * 生成sign
     * @param $input
     * @param $requestMethod
     * @param $path
     * @author daikai
     */
    public function makeSign($input,$requestMethod,$path){
        unset($input['sign']);
        ksort($input);
        $str='';
        foreach($input as $key=>$item){
            $str.=$key.$item;
        }
        $str.=$this->user->api_token;
        $str.=$requestMethod;
        $str.=$path;
        return sha1($str);
    }

    public function getErrMessage(){
        return $this->errMessage;
    }

    public function getErrCode(){
        return $this->errCode;
    }

    public function getUser($userId)
    {
        return $this->user = $this->provider->retrieveById($userId);
    }

    public function getUserId()
    {
        return $this->user->id;
    }

    /**
     * 设置签名的过期时间时间，
     * @param $timestamp
     * @author daikai
     */
    public static function setVailRequestPeriod($timestamp){
        static::$vailRequestPeriod=$timestamp;
    }

    /**
     * 设置token的过期时间
     * @param $timetamp
     * @author daikai
     */
    public static function setTokenPeriod($timetamp){
        static::$tokenPeriod=$timetamp;
    }
}
