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
定义GTCode类接口文件

1. 初始化验证方法startCaptchaServlet，返回验证字符串数据提供给客户端脚本初始化，并在回调函数中获取captchaObj对象提供给二次验证需要的参数
2. 二次验证方法verifyLoginServlet，返回验证的结果，1表示成功，0表示失败

客户端配置请访问极验文档：http://docs.geetest.com/install/client/web-front/
如在使用中有任何疑问可以加我的qq:49078111

```php
class GTCode extends Api {

    public function getRules() {
        return array(
            'startCaptchaServlet' => array(
                'userId' => array('name' => 'user_id', 'type' => 'string', 'require' => true, 'desc' => '用户ID'),
                'clientType' => array('name' => 'client_type', 'type' => 'string', 'require' => true, 'desc' => 'web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式')
            ),
            'verifyLoginServlet' => array(
                'clientType' => array('name' => 'client_type', 'type' => 'string', 'require' => true, 'desc' => 'web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式'),
                'challenge' => array('name' => 'challenge', 'type' => 'string', 'require' => true, 'desc' => 'challenge'),
                'validate' => array('name' => 'validate', 'type' => 'string', 'require' => true, 'desc' => 'validate'),
                'seccode' => array('name' => 'seccode', 'type' => 'string', 'require' => true, 'desc' => 'seccode'),
            ),
        );
    }

    /**
     * 初始化验证
     * @desc 初始化验证
     * @return int success success
     * @return string gt 验证 id，极验后台申请得到
     * @return string challenge 验证流水号，后服务端 SDK 向极验服务器申请得到
     * @return int new_captcha 宕机情况下使用，表示验证是 3.0 还是 2.0，3.0 的 sdk 该字段为 true
     */
    public function startCaptchaServlet() {
        return \PhalApi\DI()->gtcode->startCaptchaServlet(array(
            'user_id' => $this->userId,
            'client_type' => $this->clientType,
            'ip_address' => $_SERVER["REMOTE_ADDR"]
        ));
    }

    /**
     * 二次验证
     * @desc 二次验证
     * @return int code 验证的结果，1表示成功，0表示失败
     */
    public function verifyLoginServlet() {
        $rs = array();

        $code = \PhalApi\DI()->gtcode->verifyLoginServlet($this->challenge, $this->validate, $this->seccode, array(
            'client_type' => $this->clientType,
            'ip_address' => $_SERVER["REMOTE_ADDR"]
        ));

        $rs['code'] = $code;

        return $rs;
    }

}
```