<?php
namespace EasyPay\DataManager\Wechat;

use DOMDocument;
use EasyPay\Config;
use EasyPay\Exception\PayException;
use EasyPay\Exception\PayFailException;
use EasyPay\Exception\PayParamException;
use EasyPay\DataManager\BaseDataManager;
use EasyPay\Exception\SignVerifyFailException;

/**
 * Class Data
 * @package EasyPay\Strategy\Wechat
 */
class Data extends BaseDataManager
{
    /**
     * 生成CDATA格式的XML
     *
     * @return string
     */
    public function toXml()
    {
        $dom = new DOMDocument();
        $xml = $dom->createElement('xml');

        foreach ($this->items as $key => $value) {
            $item = $dom->createElement($key);
            $item->appendChild($dom->createCDATASection($value));
            $xml->appendChild($item);
        }

        $dom->appendChild($xml);
        return $dom->saveXML();
    }

    /**
     * 释放生成器结果
     */
    public function free()
    {
        $this->items = [];
    }

    /**
     * 设置签名
     */
    public function setSign()
    {
        $this->createNonceStr();
        $this->sign = $this->makeSign();
    }

    /**
     * 生成签名(每次都重新生成,确保是最新参数生成的签名)
     *
     * @return string
     * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=4_3
     */
    public function makeSign()
    {
        // 默认使用MD5加密
        $signType = $this->offsetExists('sign_type') ? $this->sign_type : "MD5";

        switch ($signType) {
            case 'MD5':
                $result = md5($this->toUrlParam());
                break;
            default:
                throw new PayException("签名类型错误");
        }

        return strtoupper($result);
    }

    /**
     * 生成URL参数
     *
     * @return string
     */
    protected function toUrlParam()
    {
        // 优先使用实例时传入的配置信息
        // 其次在使用公共配置信息
        ksort($this->items);
        $items = $this->filterItems($this->items);
        if (!$key = $this->getOption('key')) {
            throw new PayParamException('商户支付密钥不存在,请检查参数');
        }

        // 构造完成后,使用urldecode进行解码
        $items['key'] = $key;
        return urldecode(http_build_query($items));
    }

    /**
     * 过滤参数
     *
     * @param $items
     * @return array
     */
    protected function filterItems($items)
    {
        $data = [];
        foreach ($items as $key => $value) {
            // 参数不为空且不为签名
            if (!empty($value) && $key !== 'sign') {
                $data[$key] = trim($value);
            }
        }

        return $data;
    }

    /**
     * 产生随机字符串
     *
     * @param int $length
     * @return string
     */
    public function createNonceStr($length = 32)
    {
        if (!$this->nonce_str) {
            $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
            $this->nonce_str = "";
            for ( $i = 0; $i < $length; $i++ ) {
                $this->nonce_str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
            }
        }

        return $this->nonce_str;
    }

    /**
     * 检查返回结果是否正确
     */
    public function checkResult()
    {
        //通信是否成功
        if (!$this->isSuccess($this->return_code)) {
            throw new PayException($this, $this->return_msg);
        }

        //交易是否发起
        if (!$this->isSuccess($this->result_code)) {
            //抛出错误码与错误信息
            throw new PayFailException(
                $this,$this->err_code_des,$this->err_code
            );
        }

        //签名是否一致
        if (!$this->offsetExists('sign') || $this->sign != $this->makeSign()) {
            throw new SignVerifyFailException($this, '返回结果错误,签名校验失败');
        }
    }

    protected function getOption($name)
    {
        return Config::wechat($name);
    }

    /**
     * 检查结果是否成功
     *
     * @param $code
     * @return bool
     */
    public function isSuccess($code)
    {
        return $code == 'SUCCESS';
    }

    /**
     * 输出XML信息
     *
     * @return string
     */
    public function __toString()
    {
        $this->setSign();
        return $this->toXml();
    }
}