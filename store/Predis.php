<?php

namespace limit\store;

use limit\Limit;
use limit\store\predis\Object;

/**
 * Predis 的数据库储存驱动
 * Description of Predis
 * @property-read Limit $limit
 */
class Predis
{

    protected $dataob; # 数据对象
    private $limit;

    /**
     * 获取数据, 不存在则初始化
     * @param type $app App标示
     * @param type $cycle 周期类型/周期数量
     * @param type $cycle_date 周期时间
     * @param type $limit 限流数量
     * @param boolean $natural 是否自然周期
     */

    public function initDate($app, $cycle, $cycle_date, $limit, $natural)
    {
        $limitobject = $this->getObject($app, $cycle, $cycle_date, $limit, $natural);
        if ($limitobject) {
            $limitobject->setLimit($this->limit);
            # 存在旧对象
            if ($limitobject->xiane != $limit) {
                # 限制变了,改一下
                $limitobject->update_time = time();
                $limitobject->xiane = $limit;

                $limitobject->save();
            }
            $this->dataob = $limitobject;
        } else {
            # 新建对象
            $limitobject = new predis\Object([
                'xiane' => $limit,
                'app_id' => $app,
                'create_time' => time(),
                'update_time' => time(),
                'cycle' => $cycle,
                'cycle_date' => $cycle_date,
                'natural' => $natural
            ]);
            $limitobject->setLimit($this->limit);
            $limitobject->save();
            $this->dataob = $limitobject;
        }


        return $limitobject->toArray();
    }


    /**
     * 获取对象
     * @param $app
     * @param $cycle
     * @param $cycle_date
     * @param $limit
     * @param $natural
     */
    private function getObject($app, $cycle, $cycle_date, $limit, $natural)
    {

        if (!$natural) {
            $object = predis\Object::getLast($app, $cycle);

            if ($object) {

                if($object->cycle_date >= $this->limit->cycle_start_define){

                    return $object;
                }
            }
            return null;
        } else {
            return predis\Object::get($app, $cycle_date, $cycle);
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
     * 使用
     * @param type $value
     */
    public function used(int $value = 1)
    {
        return $this->dataob->used($value);
    }

    /**
     * 按照订单处理
     * @param type $orderid
     * @param type $value
     */
    public function used4order($orderid, $value)
    {
        $order = tp5db\LimitOrder::get([
            'oid' => $this->dataob->id,
            'order_id' => $orderid
        ]);
        if ($order) {
            # 已处理
            return TRUE;
        } else {
            $order = new tp5db\LimitOrder();
            $order->save([
                'oid' => $this->dataob->id,
                'order_id' => $orderid,
                'create_time' => time()
            ]);
            return $this->used($value);
        }
    }

    /**
     * 获取当前已使用
     */
    public function getUsed()
    {
        return $this->dataob->used;
    }

    /*
     * 刷新数据为最新数据
     */
    private function refresh()
    {

    }
}
