<?php
namespace EasyPay\Strategies\Ali;

/**
 * 下载账单,返回账单下载地址
 *
 * Class DownloadBill
 * @package EasyPay\Strategies\Ali
 */
class DownloadBill extends BaseAliStrategy
{
    /**
     * {@inheritDoc}
     */
    protected function getMethod()
    {
        return BaseAliStrategy::DOWN_LOAD_BILL;
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequireParams()
    {
        return ['app_id', 'bill_type', 'bill_date'];
    }

    /**
     * {@inheritDoc}
     */
    protected function getFillParams()
    {
        return [
            'app_id', 'method', 'format', 'charset', 'sign_type', 'sign',
            'timestamp', 'version', 'app_auth_token', 'biz_content',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function buildBizContent()
    {
        return [
            // 账单类型 (trade、signcustomer)
            'bill_type' => $this->payData['bill_type'],
            // 账单时间
            'bill_date' => $this->payData['bill_date'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function handleData($data)
    {
        return parent::handleData($data)->bill_download_url;
    }
}
