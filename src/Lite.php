<?php
namespace PhalApi\GTCode;

use PhalApi\Exception\BadRequestException;
use PhalApi\GTCode\SDK\GeetestLib as GeetestLib;

/**
 * 2017/12/15 极验验证码拓展 @吞吞小猴<49078111@qq.com>
 */


class Lite
{
    //ID
    private $captchaId = '';

    //KEY
    private $privateKey = '';

    //SDK实例
    private $GtLib;


    /**
     * Lite 构造方法初始化配置
     *
     * @param  $config array 配置文件
     */
    public function __construct($config = null) {

        if ($config === NULL) {
            //获得配置项
            $config = \PhalApi\DI()->config->get('app.gtserver');
        }

        //ID是否配置
        if ($this->getIndex($config, 'captchaId')) {
            $this->captchaId = $config['captchaId'];
        } else {
            throw new BadRequestException('captchaId There is no', 1);
        }
        //KEY是否配置
        if ($this->getIndex($config, 'privateKey')) {
            $this->privateKey = $config['privateKey'];
        } else {
            throw new BadRequestException('privateKey There is no', 1);
        }
     
        //初始化SDK
        $this->GtLib = new GeetestLib($this->captchaId, $this->privateKey);
    }

    /**
     * 初始化验证
     *
     * @param array $data
     * @return mixed
     */
    public function startCaptchaServlet($param, $new_captcha=1) {

        if(!isset($_SESSION)){
            session_start();
        }

        $data = array(
            'user_id' => 'test', # 网站用户id
            'client_type' => 'web', #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            'ip_address' => '127.0.0.1' # 请在此处传输用户请求验证时所携带的IP
        );

        $data = array_merge($data,$param);

        //调用预处理接口
        $status = $this->GtLib->pre_process($data, $new_captcha);

        $_SESSION['gtserver'] = $status;

        //返回验证字符串接口
        return $this->GtLib->get_response();
    }


    /**
     * 二次验证
     *
     * @param string $challenge
     * @param string $validate
     * @param string $seccode
     * @param array $param
     * @return int
     */
    public function verifyLoginServlet($challenge, $validate, $seccode,$param, $json_format=1) {

        if(!isset($_SESSION)){
            session_start();
        }
        
        $data = array(
            'user_id' => 'test', # 网站用户id
            'client_type' => 'web', #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            'ip_address' => '127.0.0.1' # 请在此处传输用户请求验证时所携带的IP
        );

        $data = array_merge($data,$param);

        if ($_SESSION['gtserver'] == 1) {   //服务器正常
            $result = $this->GtLib->success_validate($challenge, $validate, $seccode, $data, $json_format);
            if ($result) {
                return 1;
            } else{
                return 0;
            }
        }else{  //服务器宕机,走failback模式
            if ($this->GtLib->fail_validate($challenge, $validate, $seccode)) {
                return 1;
            }else{
                return 0;
            }
        }
    }

    /**
     * 数组对象取值相关 - 避免出错
     */
    private function getIndex($arr, $key, $default = '') {

        return isset($arr[$key]) ? $arr[$key] : $default;
    }

}

