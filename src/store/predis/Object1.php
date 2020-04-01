<?php


namespace limit\store\predis;


use limit\ObjectArray;
use limit\ObjectInterface;
use limit\ObjectTrait;
use limit\Limit;

/**
 * 对象
 * Class Object1
 * @package limit\data
 * @property-read Limit $limit
 */
class Object1 implements \ArrayAccess, ObjectInterface
{
    use ObjectTrait, ObjectArray;

    public $app_id;
    public $xiane;
    public $update_time;
    public $create_time;
    public $cycle;
    public $cycle_date;
    public $natural;

    private $limit;

    /**
     * 实例化
     * Object constructor.
     * @param $data
     */
    public function __construct($data=[])
    {
        foreach ($data as $k => $v) {
            if (isset($this, $k)) $this->$k = $v;
        }

    }


    /**
     *
     * 设置 Limit 对象
     * @param Limit $limit
     */
    public function setLimit(Limit $limit)
    {
        $this->limit = $limit;
    }


    /**
     * 获取主键
     * @return String
     */
    public function getKey(): String
    {
        return md5(serialize([$this->app_id, $this->cycle, $this->cycle_date]));
    }

    /**
     * @param $app
     * @param $cycle_date
     * @param $cycle
     */
    public static function get($app, $cycle_date, $cycle)
    {
        $ob=new self();
        $ob->app_id = $app;
        $ob->cycle = $cycle;
        $ob->cycle_date = $cycle_date;
        $key = $ob->getKey();
        $redis = Connect::getInstance();
        return $redis->get($key);
    }

    /**
     * 获取最后的时间对象
     * @param $app
     * @param $cycle_date
     * @param $cycle
     * @return self
     */
    public static function getLast($app, $cycle)
    {
        $key = md5(serialize([$app, $cycle]));
        $redis = Connect::getInstance();
        $key = $redis->client->lindex($key, 0); //最后一条的key
        return $redis->get($key);
    }


    /**
     * 保存
     * @return mixed
     */
    public function save()
    {
        $redis = Connect::getInstance();
        if (!$this->natural) {
            $key = md5(serialize([$this->app_id, $this->cycle]));
            if( $redis->client->lpush($key, $this->getKey()) >10){
                $redis->client->rpop($key);
                $redis->client->rpop($key);
            }
        }
        $redis->set($this->getKey(), $this);

        $redis->client->expireat($this->getKey(), $this->limit->cycle_end_define + 10);
    }


    /**
     * 消耗
     * @param int $value
     * @return int
     */
    public function used(int $value)
    {
        $redis = Connect::getInstance();

        if (!$redis->client->exists($this->getKey() . 'used')) {
            return FALSE;
        }
        return $redis->client->incrby($this->getKey() . 'used', $value);
    }


    /**
     * 获取使用量
     * @return int
     */
    public function getUsed(): int
    {
        $redis = Connect::getInstance();
        $key = $this->getKey() . 'used';
        if ($redis->client->exists($key)) {
        } else {

            $redis->client->set($key, 0);
            $redis->client->expireat($key, $this->limit->cycle_end_define + 10);
        }

        $used = (int)$redis->client->get($key);
        return $used;
    }


}