# Laravel-signatrue
基于laravel 的接口的签名的验证

## 在config/auth.php 中的guards中 注册守卫者
```php
'mytoken'=>[
            'driver' => 'signatrue',
            'provider' => 'AdminUser',
        ]
'AdminUser' => [
            'driver' => 'eloquent',
            'model' => App\Models\AdminUser::class,
        ],
数据库的模型自己定义，但是数据biao 必须要有
username；api_token；token_expired_at 这三个字段

## 在kernel.php 中这册中间件
```php
use ClearSwitch\Signatrue\Middleware\SignatrueAuthenticate;

'auth.signatrue'=>SignatrueAuthenticate::class
```