<?php
/**
 * 服务启动类
 */
namespace ClearSwitch\Signatrue;

use ClearSwitch\Signatrue\Middleware\SignatrueAuthenticate;
use ClearSwitch\Signatrue\Middleware\WebSignatrueAuthenticate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use ClearSwitch\Signatrue\SignatrueGuard;

class SignatrueProvider extends ServiceProvider {

    /**
     * 注册中间件
     * @var string[]
     */
    protected $middleware=[
        'auth.signatrue'=>SignatrueAuthenticate::class,
        'auth.web-signatrue'=>WebSignatrueAuthenticate::class
    ];

    /**
     * 绑定服务
     * @author daikai
     */
    public function register()
    {
        // parent::register(); // TODO: Change the autogenerated stub
    }


    public function boot(){
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->aliasMiddlewares();
        $this->registerGuards();
    }

    protected function aliasMiddlewares(){
        $router=$this->app['router'];
        $method=method_exists($router,'aliasMiddleware')?'aliasMiddleware':'middleware';
        foreach ($this->middleware as $alias=>$middleware){
            $router->$method($alias,$middleware);
        }
    }

    /**
     * 注册新的守卫者
     * @author daikai
     */
    protected function registerGuards(){
        Auth::extend('signatrue',function ($app,$name,$config){
            $guard=new SignatrueGuard(
                new Signature(),
                $app['auth']->createUserProvider($config['provider']),
                $app['request']
            );
            return $guard;
        });
    }
}
