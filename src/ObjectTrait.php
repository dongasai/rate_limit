<?php


namespace limit;


trait ObjectTrait
{
    /**
     * 获取数据使用量
     * @return string
     */
    public function __get($key)
    {
        $function = 'get'.ucfirst($key);
        if(method_exists($this,$function)){
            return $this->$function();
        }
        return NULL;
    }

}