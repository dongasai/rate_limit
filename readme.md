# 限流组件

> 自然周期


就是年月日,这样的周期,每(天/月,日...)的开始时间为周期的开始,结束时间为周期结束

> 非自然周期

就是多少秒为一个周期,第一个请求为周期开始,周期秒数后为周期结束,当有新的请求进入后为周期开始(**没有新的请求进入即不开始新的周期**),示例
周期为10秒,第一个请求时间为2014:09:12 00:00:01,那么2014:09:12 00:00:01-2014:09:12 00:00:11为这个周期,但是当00:00:01-2014:09:12 00:00:11之后如果你没有新的请求

简单示例:

```php
$config = [
  "host" => "10.0.1.120",
  "password" => null,
  "port" => "6379",
  "database" => 1,
];

 $config = config('database.redis.cache');

$id = 1;
$limit = new \limit\Limit('a1', $id, 10, 'h', true,$config);
dump($limit->used(1)); # 使用成功返回true
dump( $limit->check(1));# 预检是否可用,成功返回true
# 应该先预检后使用

```

## 初始化

```php
$config = [
  "host" => "10.0.1.120",
  "password" => null,
  "port" => "6379",
  "database" => 1,
];

$config = config('database.redis.cache');

$id = 1;
$limit = new \limit\Limit('user', $id, 10, 'h', true,$config);

```
> 六个参数

* `$name`名字,验证器的名字
* `$id`ID,验证起的ID,与名字一起构成唯一标示,例如:user-1
* `$limit`限流数量,单位周期的限流数量
* `$cycle` 周期,非自然周期为秒数,自然周期为:
    * `y` # 年
    * `m` # 月
    * `d` # 日
    * `w` # 周
    * `h` # 小时
    * `i` # 分钟
    * `s` # 秒
* `$natural`是否为自然周期,默认为`true` 
* `$config` 配置项,样式如下,`option`为`Predis`的第二个参数
```php
$defalut = [
            'scheme' => 'tcp',
            'host' =>  '127.0.0.1',
            'password' =>  null,
            'port' =>  6379,
            'database' =>  0,
            'option'=>[
                'prefix'=>'limit'
            ]
        ];
```

## 开始使用

* `check(int $value = 0 ):bool`,检查是否有这些值可用,传入的是预检的值,> 剩余值则不可用
* `getRemaining()`,获取剩余可用值
* `getUsed()`,获取已使用值
* `used(int $value=1,$order=null)`,使用一些值
    - `$value`是要使用的值
    - `$order`进行订单判断,**未实现**