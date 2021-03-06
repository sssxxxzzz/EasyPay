<?php

namespace EasyPay\Strategies\Wechat;

use EasyPay\Exception\PayParamException;
use EasyPay\Strategies\Wechat\BaseWechatStrategy;

/**
 * 查询退款信息
 *
 * Class RefundQuery
 * @package EasyPay\Strategies\Wechat\Transaction
 */
class RefundQuery extends BaseWechatStrategy
{
    /**
     * {@inheritDoc}
     */
    protected function buildData()
    {
        if (
            !$this->payData['transaction_id'] &&
            !$this->payData['out_trade_no'] &&
            !$this->payData['out_refund_no'] &&
            !$this->payData['refund_id']
        ) {
            throw new PayParamException(
                '查询订单必须填写[out_trade_no,transaction_id,out_refund_no,refund_id]中任意一个订单号或退款记录号'
            );
        }

        return parent::buildData();
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequireParams()
    {
        return ['appid', 'mch_id'];
    }

    /**
     * {@inheritDoc}
     */
    protected function getFillParams()
    {
        return [
            'appid', 'mch_id', 'out_trade_no', 'sign_type', 'device_info',
            'refund_id', 'out_refund_no', 'transaction_id',
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
        return BaseWechatStrategy::REFUND_QUERY_URL;
    }
}
