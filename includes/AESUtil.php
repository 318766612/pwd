<?php

class AESUtil
{
    // 私有静态属性来存储单例实例
    private static $instance = null;

    // 私有构造函数和克隆方法来防止外部创建新的实例或克隆现有的实例
    private function __construct()
    {
    }

    private function __clone()
    {
    }

    // 公有静态方法用于获取单例实例
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function Generate_IV()
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    }

    public $iv64 = "oVod5mAqGgCJ1qrJx+79+g==";
    private $key = "holin--password";

    public function Encry($data)
    {
        $iv = base64_decode($this->iv64);
        return openssl_encrypt($data, 'aes-256-cbc', $this->key, 0, $iv);
    }

    public function Decry($ciphertext)
    {
        $iv = base64_decode(iv64);
        return openssl_decrypt($ciphertext, 'aes-256-cbc', $this->key, 0, $iv);
    }

}