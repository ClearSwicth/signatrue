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
```
##数据库的模型自己定义，但是数据biao 必须要有
```php
username；api_token；token_expired_at 这三个字段
```
## 在App\Providers\AuthServiceProvider.php 中的bool中设置 签名，api_token 的过期时间
```php
use ClearSwitch\Signatrue\Signature;
Signature::setTokenPeriod(时间戳);
Signature::setVailRequestPeriod(时间戳);
```
##路由中间的调用
```php
Route::middleware('auth.signatrue')
```
##请求参数中必须要有三个参数
```php
ts,user_id,sign
```
