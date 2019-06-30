<?php


namespace limit;

/**
 * 数据对象接口
 * Class DataInterface
 * @package limit
 */

interface ObjectInterface
{


    /**
     * 获取主键
     * @return mixed
     */
    public function getKey():String;

    /**
     * 获取数据
     * @param $app app标示
     * @param $cycle_date 周期时间
     * @param $cycle 周期类型/长度
     * @param $natural 是否自然周期
     * @return mixed
     */
    public static function get($app, $cycle_date, $cycle);

    /**
     * 获取最后一条
     * @param $app
     * @param $cycle
     * @return mixed
     */
    public static function getLast($app,$cycle);



    /**
     * 保存数据
     * @return mixed
     */
    public function save();


    /**
     * 消耗限额
     * @param int $value
     * @return mixed
     */
    public function used(int $value);

    /**
     * 获取数据使用量
     * @return int
     */
    public function getUsed():int ;

}
