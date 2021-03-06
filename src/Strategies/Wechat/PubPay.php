<?php

namespace EasyPay\Strategies\Wechat;

use EasyPay\TradeData\Wechat\TradeData;

/**
 * 请求微信公众号支付接口,返回Js api使用的Json数据
 *
 * Class PubPay
 * @package EasyPay\Strategies\Wechat
 */
class PubPay extends BaseWechatStrategy
{
    /**
     * {@inheritDoc}
     */
    protected function buildData()
    {
        $payData = parent::buildData();
        // 设定交易模式为公众号支付
        $payData->trade_type = 'JSAPI';

        return $payData;
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequireParams()
    {
        return [
            'appid', 'mch_id', 'body', 'out_trade_no', 'total_fee',
            'spbill_create_ip', 'notify_url', 'openid',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getFillParams()
    {
        return [
            'appid', 'mch_id', 'body', 'out_trade_no', 'total_fee',
            'spbill_create_ip', 'notify_url', 'trade_type', 'product_id',
            'device_info', 'sign_type', 'detail', 'attach', 'fee_type',
            'time_start', 'time_expire', 'goods_tag', 'limit_pay', 'openid',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequestMethod()
    {
        return 'POST';
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequestTarget()
    {
        return BaseWechatStrategy::INIT_ORDER_URL;
    }

    /**
     * 生成公众号支付使用的json数据
     *
     * @param $result
     * @return string
     */
    protected function handleData($result)
    {
        $data = parent::handleData($result);

        // 生成Js api使用的Json数据
        $data = new TradeData([
            'appId' => $data->appid,
            'timeStamp' => (string) time(),
            'nonceStr' => substr(md5(uniqid()), 0, 18) . date("YmdHis"),
            'package' => "prepay_id={$data->prepay_id}",
            'signType' => 'MD5',
        ], $data->getOptions());

        $data->paySign = $data->makeSign();

        return $data->toJson();
    }
}
