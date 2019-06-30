<?php


namespace limit\store\predis;


use limit\DataArray;
use limit\ObjectInterface;
use limit\ObjectTrait;

/**
 * 对象
 * Class Object
 * @package limit\data
 */
class Order implements \ArrayAccess,ObjectInterface
{
    use ObjectTrait,DataArray;

    public $app_id;
    public $xiane;
    public $used;
    public $update_time;
    public $create_time;
    public $cycle;
    public $cycle_date;

    public function __construct($data)
    {
        array_merge($this,$data);
    }
}