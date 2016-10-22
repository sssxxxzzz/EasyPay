<?php
namespace EasyPay;


class HandleNotify
{
    /**
     * @var \EasyPay\Interfaces\AsyncNotifyInterface
     */
    protected $notify;

    /**
     * @var array
     */
    protected $modes = [
        'wechat' => \EasyPay\PayApi\Wechat\AsyncNotify::class,
    ];

    public function __construct($mode)
    {
        $class = isset($this->modes[$mode]) ? $this->modes[$mode] : $mode;

        $this->notify = new $class;
    }

    /**
     * 注册处理异步回调函数
     *
     * @param callable $callback
     * @return self
     */
    public function handle(callable $callback)
    {
        ob_start();
        try{
            $this->notify->success(
                call_user_func($callback,$this->notify->getNotify()) ?: "OK"
            );
        }catch(\Exception $e){
            $this->notify->fail($e);
        }

        ob_end_clean();
        $this->notify->replyNotify();
    }
}