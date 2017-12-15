# 极验验证码扩展


## 安装和配置
修改项目下的composer.json文件，并添加：  
```
    "phalapi/gtcode":"dev-master"
```
在/path/to/phalapi/config/app.php文件中，配置： 
```
    'gtserver' => array(
        'captchaId'   => '',  //ID
        'privateKey' => '',  //KEY
    )
```
然后执行```composer update```。  

## 注册
在/path/to/phalapi/config/di.php文件中，注册：  
```php

$di->gtcode = function() {
	return new \PhalApi\GTCode\Lite();
};
```

## 使用
1. 初始化验证
```php
$rs = \PhalApi\DI()->gtcode->startCaptchaServlet(array(
    'user_id' => 'test', # 网站用户id
    'client_type' => 'web', #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
    'ip_address' => '127.0.0.1' # 请在此处传输用户请求验证时所携带的IP
));
```
2. 二次验证
```php
$rs = \PhalApi\DI()->gtcode->verifyLoginServlet("$challenge", "$validate", "$seccode", array(
    'client_type' => 'web', #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
    'ip_address' => '127.0.0.1' # 请在此处传输用户请求验证时所携带的IP
));
```