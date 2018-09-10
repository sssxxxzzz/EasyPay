<?php 

namespace EasyPay\Notifies;

use EasyPay\TradeData\BaseTradeData;
use EasyPay\Interfaces\NotifyInterface;

/**
 * 支付通知信息
 *
 * @method mixed getOriginal(string $name = null)
 * @method array toArray()
 * @method string toJson()
 * @method string toXml()
 * 
 * Class BaseNoitfy
 * @package EasyPay\Notifies
 */
abstract class BaseNoitfy implements NotifyInterface
{
    /**
     * @var BaseTradeData
     */
    protected $tradeData;

    /**
     * @var array
     */
    protected static $badModes = ['cli', 'continuity', 'milter', 'webjames'];

    public function __get($key)
    {
        return $this->tradeData[$key];
    }

    public function __set($key, $value)
    {
        $this->tradeData[$key] = $value;
    }

    public function __call($method, $params)
    {
        return $this->tradeData->$method(...$params);
    }

    abstract protected function verifySign();
}