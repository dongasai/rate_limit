<?php


namespace limit\store\predis;


/**
 * 链接对象
 * Class Connect
 * @package limit\store\predis
 * @property-read \Predis\Client $client
 */
class Connect
{
    public $client;
    static private $instance;

    /**
     * 构造函数私有化
     * Connect constructor.
     */
    private function __construct($config)
    {
        $defalut = [
            'scheme' => 'tcp',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
        ];
        $this->client = new \Predis\Client(array_merge($defalut, $config));

    }

    /**
     * 单利模式获取实例
     * @param array $config
     * @return Connect
     */
    public static function getInstance($config = [])
    {

        if (!self::$instance instanceof self) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }


    /**
     * 获取
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return unserialize($this->client->get($key));
    }


    /**
     * 设置内容
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        return $this->client->set($key, serialize($value));
    }



    /**
     * 获取原始数据
     * @param $key
     * @return string
     */
    public function getS($key)
    {
        return $this->client->get($key);
    }

    /**
     * 自增
     * @param $key
     * @param $inc
     * @return int
     */
    public function incrBy($key,$inc)
    {
        return $this->client->incrby($key,$inc);
    }




}
