<?php

namespace limit;
/**
 * 限额,限流组件
 * @property-read
 */
class Limit
{
    
    private $config = [
        'drive'   => 'Predis', # 默认驱动为 Predis 驱动
    ];
    private $cycle = 'd';
    private $natural=true;#是否为自然周期
    private $app;# app标示
    private $limit;# 限流数量
    private $cycle_date;# 周期时间
    private $store;
    # 时间格式
    private $format=[
                'y'=>'Y',# 年
                'm'=>'Ym',# 月
                'd'=>'Ymd',# 日
                'w'=>'Ymdw',# 周
                'h'=>'YmdH', # 小时
                'i'=>'YmdHi',# 分钟
                's'=>'YmdHis'# 秒
            ];

    public $cycle_start;# 周期开始时间
    public $cycle_start_define;# 周期开始界定时间

    public $cycle_end;# 周期结束时间
    public $cycle_end_define;# 周期结束界定时间


    /**
     * 初始化一个限流器
     * @param type $name 应用名字
     * @param type $id 应用id ,名字和id组合生成应用的唯一标示
     * @param type $limit 限流数量
     * @param type $cycle 周期
     * @param type $config 配置
     */
    public function __construct($name,$id,$limit,$cycle,$natural,$config=[])
    {
        $this->app=$name.'-'.$id;
        $this->limit=$limit;
        $this->cycle=$cycle;
        $this->natural=$natural;
        $this->config= array_merge($this->config,$config);
        $this->getStore();
        $this->createApp();
    }
    
    /**
     * 创建APP周期,或读取旧的生命周期信息
     */
    private function createApp()
    {
        $cycle_date       = $this->getCycleDate();
        $this->cycle_date = $cycle_date;

        $this->store->initDate($this->app,$this->cycle,$this->cycle_date,$this->limit,$this->natural);


    }
    
    /**
     * 获取储存器
     */
    private function getStore()
    {
        $drive= '\limit\store\\'.$this->config['drive'];
        if(!class_exists($drive)){
            throw new \Exception('驱动不存在!');
        }
        $this->store=new $drive($this->config);
        $this->store->setLimit($this);
        return $this->store;
    }

    

    /**
     * 获取限流周期标示
     * @return type
     */
    private function getCycleDate()
    {
        $time= time();
        $this->StartEnd($time);
        if($this->natural) {
            # 自然日
            if (!isset($this->format[$this->cycle])) {
                throw new \Exception('周期格式不正确!');
            }
            return date($this->format[$this->cycle],$time);
        }

        return $time;
    }

    /**
     * 周期的开始和结束时间
     */
    public function StartEnd($time)
    {

        if ($this->natural) {
            # 自然周期
            if (!isset($this->format[$this->cycle])) {
                throw new \Exception('周期格式不正确!');
            }
            $dataCarbon = \Carbon\Carbon::createFromTimestamp($time,'PRC');
            $function = [
                'y' => 'Year',# 年
                'm' => 'Month',# 月
                'd' => 'Day',# 日
                'w' => 'Week',# 周
                'h' => 'Hour', # 小时
                'i' => 'Minute',# 分钟
                's' => 'Second'# 秒
            ];

            $startfunction = 'startof' . $function[$this->cycle];
            $endfunction = 'endof' . $function[$this->cycle];
            $this->cycle_start =$this->cycle_start_define = $dataCarbon->$startfunction()->getTimestamp();
            $this->cycle_end =$this->cycle_end_define  = $dataCarbon->$endfunction()->getTimestamp();
        }else{
            # 非自然周期的 开始和结束时间
            $this->cycle_start_define = $time - intval($this->cycle);
            $this->cycle_end_define = $time + intval($this->cycle);
        }

    }

    
    /**
     * 周期检查,是否位于当前周期
     * @return type
     */
    private function cycleDateCheck()
    {
        if($this->natural){
            return $this->getCycleDate()== $this->cycle_date;
        }else{
            # 非自然周期

            if(($this->getCycleDate()- $this->cycle_date)>= (int)$this->cycle){
                return TRUE;
            }
            return FALSE;
        }
    }




    /**
     * 使用限速
     * @param type $value
     * @param type $order
     */
    public function used(int $value=1,$order=null)
    {
        if(!$this->check($value)){
            return FALSE;
        }

        if($order){
            return $this->store->used4order($order,$value);
        }else{
            return $this->store->used($value);
        }
    }
    
    
    /**
     * 查看是否可以继续
     */
    public function check(int $value= 0)
    {
        if($this->cycleDateCheck()){
            # 周期发生变化
            $this->createApp();
        }
        if($this->limit ==0 ){
            return true;
        }

        return (bcadd($this->store->getUsed(), $value) <= $this->limit);
    }
    
    /**
     * 获取剩余
     */
    public function getRemaining()
    {
       return bcsub($this->limit, $this->store->getUsed());
    }
    
    /**
     * 获取已使用的
     * @return type
     */
    public function getUsed()
    {
        return  $this->store->getUsed();
    }
    
   
}
