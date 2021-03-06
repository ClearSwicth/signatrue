<?php
namespace ClearSwitch\Signatrue;
/**
 * 自定义守卫者
 */

use ClearSwitch\Signatrue\Signature;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class SignatrueGuard implements Guard {
    use GuardHelpers;

    protected $signatrue;

    protected $provider;

    protected $request;

    protected $user;
    /**
     * 初始化验证，他需要三个要素，验证器，数据提供者，请求
     *
     * @param Signatrue $signatrue
     * @param UserProvider $provider
     * @param Request $request
     */
    public function  __construct(Signature $signatrue,UserProvider $provider,Request $request)
    {
        $this->signatrue=$signatrue;
        $this->provider=$provider;
        $this->request=$request;
        $this->signatrue->setRequest($this->request);
        $this->signatrue->setProvider($this->provider);
    }

    /**
     * 返回数据提供者认证通过的数据
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @author daikai
     */
    public function user()
    {
        if($this->user!=null){
            return $this->user;
        }
        if($this->signatrue->validated()){
            $user=$this->provider->retrieveById($this->signatrue->getUserId());
        }
        return $this->user=$user;
    }

    /**
     * 验证用户的凭证
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return (bool)$this->attempt($credentials,false);
    }

    /**
     * 尝试验证数据是和否正确
     * @param $credentials
     * @author daikai
     */
    public function attempt($credentials,$login=true){
        $user=$this->provider->retrieveByCredentials($credentials);
        if(empty($user)){
            return  $login? '40306':false;
        }
        if($this->hasValidCredentials($user,$credentials)){
            return $login?$this->login($user):true;
        }
        return false;
    }

    /**
     * 登陆返回token
     * @param $user
     * @return mixed|string
     * @author daikai
     */
    public function login($user){
        $token=(! $user->api_token || $user->token_expired_at<time())?$this->refreshToken($user):$user->api_token;
        $this->user=$user;
        return $token;
    }

    /**
     * 刷新token
     *
     * @return mixed|string
     * @author daikai
     */
    protected function refreshToken($user){
        $user->api_token=Str::random(60);
        $user->token_expired_at=time()+$this->signatrue::$tokenPeriod;
        $user->save();
        return $user->api_token;
    }

    /**
     *验证密码是不是正确的
     * 验证$credentials是否合法
     * @param $user
     * @param $credentials
     * @return bool
     * @author daikai
     */
    public function hasValidCredentials($user,$credentials){
        return $this->provider->validateCredentials($user,$credentials);
    }

    /**
     * 魔术方法当我们调用signatrueGuard中的方法没有是就去调用Signature;
     * @param $method
     * @param $parameters
     * @return mixed
     * @author daikai
     */
    public function __call($method,$parameters)
    {
        if(method_exists($this->signatrue,$method)){
            return call_user_func([$this->signatrue,$method],$parameters);
        }
        throw new \BadMethodCallException("Method[$method] does not exist!");
    }
}
