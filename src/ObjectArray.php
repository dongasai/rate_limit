<?php


namespace limit;


/**
 * 对象的数组集合
 * Trait ObjectArray
 * @package limit
 */
trait ObjectArray
{

    /**
     * 数组转换
     * @return array
     */
    public function toArray()
    {
        return [
            'app_id' => $this->app_id,
            'xiane' => $this->xiane,
            'update_time' => $this->update_time,
            'create_time' => $this->update_time,
            'cycle' => $this->cycle,
            'cycle_date' => $this->cycle_date,
            'natural' => $this->natural
        ];
    }

    /**
     * 数组接口 ,是否存在
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset): boolean
    {
        return property_exists($this, $offset);
    }

    /**
     * 数组接口,获取值
     * @param $offset
     * @return |null
     */
    public function offsetGet($offset)
    {
        if (property_exists($this, $offset)) {
            return $this->$offset;
        }
        return null;
    }

    /**
     * 数组接口,设置值
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
        if (property_exists($this, $offset)) {
            $this->$offset = $value;
            return $value;
        }
        return FALSE;

    }

    /**
     * 数组接口,unset
     * @param $offset
     * @return bool
     */
    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset)) {
            $this->$offset = null;
            return TRUE;
        }
        return FALSE;

    }

}