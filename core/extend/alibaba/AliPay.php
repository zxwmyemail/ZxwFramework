<?php
namespace core\extend\wechat;
/********************************************************************************************
 阿里巴巴支付类，使用方法见document.txt文件
*********************************************************************************************/
use core\library\HttpCurl;
use core\extend\monolog\Log;

class AliPay extends Alibaba {

    public function __construct($whichConf = 'default') {
        parent::__construct($whichConf);
    }

    /**
     * 当面付（扫码支付）发起订单
     * @param float  $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * 
     * 请求参数中的timeout_express指的是：该笔订单允许的最晚付款时间，逾期将关闭交易。
     * 取值范围：1m～15d，其中m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。
     * 该参数数值不接受小数点，如1.5h，可转换为90m。
     * 
     * @return array
     */
    public function createQrcodePay($totalFee, $outTradeNo, $orderName)
    {
        $config = $this->_aliConf;
        // 请求参数
        $reqParams = [
            'app_id'      => $config['appId'],
            'charset'     => $config['charset'],
            'method'      => 'alipay.trade.precreate',      //接口名称
            'format'      => 'JSON',
            'sign_type'   => $config['signType'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => $config['version'],
            'notify_url'  => $config['notifyUrl'],
            'biz_content' => json_encode([
                'out_trade_no'    => $outTradeNo,
                'total_amount'    => $totalFee,
                'subject'         => $orderName,
                'timeout_express' => '2h' 
            ]),
        ];
        $reqParams["sign"] = $this->getSign($reqParams);
        $result = HttpCurl::post(self::ALIPAY_GATEWAY_URL, $reqParams);
        return json_decode($result, true);
    }

    /**
     * 当面付（条码支付）发起订单
     * @param float  $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $authCode 用户付款码
     * @param string $deviceInfo 用户付款码（商户设备扫描用户二维码读取到的条码数字，或点击支付宝APP-》付钱-》上面有显示一串数字）
     *
     * @return array
     */
    public function createBarCodePay($totalFee, $outTradeNo, $orderName, $authCode = '', $deviceInfo = '')
    {
        $config = $this->_aliConf;
        //请求参数
        $reqParams = array(
            'app_id'      => $config['appId'],
            'method'      => 'alipay.trade.pay',             //接口名称
            'format'      => 'JSON',
            'charset'     => $config['charset'],
            'sign_type'   => $config['signType'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => $config['version'],
            'notify_url'  => $config['notifyUrl'],
            'biz_content' => json_encode([
                'out_trade_no'    => $outTradeNo,
                'scene'           => 'bar_code',     //条码支付固定传入bar_code
                'auth_code'       => $authCode,      //用户付款码
                'total_amount'    => $totalFee,      //单位 元
                'subject'         => $orderName,     //订单标题
                'store_id'        => $deviceInfo,    //商户门店编号
                'timeout_express' => '2m',           //交易超时时间
            ]),
        );
        $reqParams["sign"] = $this->getSign($reqParams);
        $result = HttpCurl::post(self::ALIPAY_GATEWAY_URL, $reqParams);
        $result = iconv('GBK','UTF-8', $result);
        return json_decode($result,true);
    }


    /**
     * 电脑网站支付，发起订单
     * @param float  $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称

     * @return array
     */
    public function createPcPay($totalFee, $outTradeNo, $orderName)
    {
        $config = $this->_aliConf;
        //请求参数
        $reqParams = array(
            //公共参数
            'app_id'      => $config['appId'],
            'method'      => 'alipay.trade.page.pay',             //接口名称
            'format'      => 'JSON',
            'return_url'  => $config['returnUrl'],
            'charset'     => $config['charset'],
            'sign_type'   => $config['signType'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => $config['version'],
            'notify_url'  => $config['notifyUrl'],
            'biz_content' => json_encode([
                'out_trade_no' => $outTradeNo,
                'total_amount' => $totalFee,
                'subject'      => $orderName,
                'product_code' => 'FAST_INSTANT_TRADE_PAY' 
            ]),
        );
        $reqParams["sign"] = $this->getSign($reqParams);
        return $this->buildRequestForm($reqParams);
    }

    /**
     * wap网站支付，发起订单
     * @param float  $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称

     * @return array
     */
    public function createWapPay($totalFee, $outTradeNo, $orderName)
    {
        $config = $this->_aliConf;
        //请求参数
        $reqParams = array(
            //公共参数
            'app_id'      => $config['appId'],
            'method'      => 'alipay.trade.wap.pay',             //接口名称
            'format'      => 'JSON',
            'return_url'  => $config['returnUrl'],
            'charset'     => $config['charset'],
            'sign_type'   => $config['signType'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => $config['version'],
            'notify_url'  => $config['notifyUrl'],
            'biz_content' => json_encode([
                'out_trade_no' => $outTradeNo,
                'total_amount' => $totalFee,
                'subject'      => $orderName,
                'product_code' => 'QUICK_WAP_WAY' 
            ]),
        );
        $reqParams["sign"] = $this->getSign($reqParams);
        return $this->buildRequestForm($reqParams);
    }

    /**
     * jsapi支付（APP支付），发起订单
     * @param float  $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @return array
     */
    public function createJsapiPay($totalFee, $outTradeNo, $orderName)
    {
        $config = $this->_aliConf;
        //请求参数
        $reqParams = array(
            //公共参数
            'app_id'      => $config['appId'],
            'method'      => 'alipay.trade.app.pay',             //接口名称
            'format'      => 'JSON',
            'charset'     => $config['charset'],
            'sign_type'   => $config['signType'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => $config['version'],
            'notify_url'  => $config['notifyUrl'],
            'biz_content' => json_encode([
                'out_trade_no'    => $outTradeNo,
                'total_amount'    => $totalFee,
                'subject'         => $orderName,
                'product_code'    => 'QUICK_MSECURITY_PAY',
                'timeout_express' => '2h',
                // 'store_id'        =>'',              //商户门店编号。该参数用于请求参数中以区分各门店，非必传项。
                // 'extend_params'   => [
                //    'sys_service_provider_id' => ''   //系统商编号，该参数作为系统商返佣数据提取的依据，请填写系统商签约协议的PID
                // ]
            ]),
        );
        $reqParams["sign"] = $this->getSign($reqParams);
        return http_build_query($reqParams);
    }

    /**
     * jsapi2（用户扫码）支付
     * @param float  $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $userId 阿里授权的用户userid
     * @return array
     */
    public function createJsapi2Pay($totalFee, $outTradeNo, $orderName, $userId)
    {
        $config = $this->_aliConf;
        //请求参数
        $reqParams = [
            'app_id'      => $config['appId'],
            'method'      => 'alipay.trade.create',             //接口名称
            'format'      => 'JSON',
            'charset'     => $config['charset'],
            'sign_type'   => $config['signType'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => $config['version'],
            'notify_url'  => $config['notifyUrl'],
            'biz_content' => json_encode([
                'out_trade_no'    => $outTradeNo,
                'total_amount'    => $totalFee,
                'subject'         => $orderName,
                'buyer_id'        => $userId,     //购买者的userid
                'timeout_express' => '2h',
            ]),
        ];
        $reqParams["sign"] = $this->getSign($reqParams);
        $result = HttpCurl::post(self::ALIPAY_GATEWAY_URL, $reqParams);
        return json_decode($result,true);
    }

    /**
     * 发起分账
     * @param string $outTradeNo    //结算请求流水号 开发者自行生成并保证唯一性
     * @param string $tradeNo       //支付宝订单号
     * @param string $tradeOut      //分账支出方账户，本参数为要分账的支付宝账号对应的支付宝唯一用户号。以2088开头的纯16位数字。
     * @param string $tradeIn       //分账收入方账户，本参数为要分账的支付宝账号对应的支付宝唯一用户号。以2088开头的纯16位数字。
     * @param string $amount        //分账金额。即收款方实际收到的金额
     * @return array
     */
    public function createSettlePay($amount, $outTradeNo, $tradeNo, $tradeOut, $tradeIn) {
        $config = $this->_aliConf;
        //请求参数
        $reqParams = [
            'out_request_no'     => $outTradeNo,
            'trade_no'           => $tradeNo,
            'operator_id'        =>'A001',  //操作员id（选填）
            'royalty_parameters' => [
                [
                    'trans_out' => $tranOut,
                    'trans_in'  => $tranIn,
                    'amount'    => $totalFee,
                    //'amount_percentage'=>100,
                    'desc'      => '分账给' . $tranIn,
                ]
            ],
        ];
        //公共参数
        $commParams = [ 
            'app_id'         => $config['appId'],
            'method'         => 'alipay.trade.order.settle',             //接口名称
            'format'         => 'JSON',
            'charset'        => $config['charset'],
            'sign_type'      => $config['signType'],
            'timestamp'      => date('Y-m-d H:i:s'),
            'version'        => $config['version'],
            'app_auth_token' => '',
            'biz_content'    => json_encode($reqParams),
        ];
        $commParams["sign"] = $this->getSign($commParams);
        $result = HttpCurl::post(self::ALIPAY_GATEWAY_URL, $commParams);
        $result = iconv('GBK','UTF-8',$result);
        return json_decode($result,true);
    }

    /**
     * 转帐订单
     * @param float $totalFee 转账金额，单位：元。
     * @param string $outTradeNo 商户转账唯一订单号
     * @param string $remark 转帐备注
     * @param string $account 收款方账户（支付宝登录号，支持邮箱和手机号格式。）
     * @param string $realName 收款方真实姓名
     * @return array
     */
    public function createTransfersPay($totalFee, $outTradeNo, $account, $realName, $remark = '')
    {
        $config = $this->_aliConf;
        //请求参数
        $reqParams = array(
            'app_id'      => $config['appId'],
            'method'      => 'alipay.fund.trans.toaccount.transfer',             //接口名称
            'format'      => 'JSON',
            'charset'     => $config['charset'],
            'sign_type'   => $config['signType'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => $config['version'],
            'biz_content' => json_encode([
                'out_biz_no'      => $outTradeNo,
                'payee_type'      => 'ALIPAY_LOGONID',
                'payee_account'   => $account,
                'payee_real_name' => $realName,  //收款方真实姓名
                'amount'          => $totalFee,  //转账金额，单位：元。
                'remark'          => $remark,    //转账备注（选填）
            ]),
        );
        $reqParams["sign"] = $this->getSign($reqParams);
        $result = HttpCurl::post(self::ALIPAY_GATEWAY_URL, $reqParams);
        $result = iconv('GBK', 'UTF-8//IGNORE', $result);
        return json_decode($result, true);
    }

    /**
     * 转帐交易状态查询
     * @param string $outBizBo 商户转账唯一订单号（商户转账唯一订单号、支付宝转账单据号 至少填一个）
     * @param string $orderId 支付宝转账单据号（商户转账唯一订单号、支付宝转账单据号 至少填一个）
     * @return array
     */
    public function queryTransfersPay($outBizBo = '', $orderId = '')
    {
        $config = $this->_aliConf;
        //请求参数
        $reqParams = array(
            //公共参数
            'app_id'      => $config['appId'],
            'method'      => 'alipay.fund.trans.order.query',             //接口名称
            'format'      => 'JSON',
            'charset'     => $config['charset'],
            'sign_type'   => $config['signType'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => $config['version'],
            'biz_content' => json_encode([
                'out_biz_no' => $outBizBo,
                'order_id'   => $orderId,
            ]),
        );
        $reqParams["sign"] = $this->getSign($reqParams);
        $result = HttpCurl::post(self::ALIPAY_GATEWAY_URL, $reqParams);
        return json_decode($result, true);
    }


    /**
     * 关闭订单
     * 用于交易创建后，用户在一定时间内未进行支付，可调用该接口直接将未付款的交易进行关闭。
     * @param string $tradeNo 支付宝交易流水号,16-64位,和outTradeNo不能同时为空，如果同时传了outTradeNo和tradeNo，则以tradeNo为准。
     * @param string $outTradeNo 订单支付时传入的商户订单号,和tradeNo不能同时为空。
     * @return array
     */
    public function closePay($tradeNo, $outTradeNo) {
        $config = $this->_aliConf;
        //请求参数
        $reqParams = array(
            //公共参数
            'app_id'      => $config['appId'],
            'method'      => 'alipay.trade.close',             //接口名称
            'format'      => 'JSON',
            'charset'     => $config['charset'],
            'sign_type'   => $config['signType'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => $config['version'],
            'biz_content' => json_encode([
                'trade_no'     => $tradeNo,
                'out_trade_no' => $outTradeNo,
            ]),
        );
        $reqParams["sign"] = $this->getSign($reqParams);
        $result = HttpCurl::post(self::ALIPAY_GATEWAY_URL, $reqParams);
        $result = iconv('GBK', 'UTF-8//IGNORE', $result);
        return json_decode($result, true);
    }

    /**
     * 订单交易状态查询
     * @param string $tradeNo 支付宝交易流水号,16-64位,和outTradeNo不能同时为空，如果同时传了outTradeNo和tradeNo，则以tradeNo为准。
     * @param string $outTradeNo 订单支付时传入的商户订单号,和tradeNo不能同时为空。
     * @return array
     */
    public function queryPay($tradeNo, $outTradeNo) {
        $config = $this->_aliConf;
        //请求参数
        $reqParams = array(
            'app_id'      => $config['appId'],
            'method'      => 'alipay.trade.query',             //接口名称
            'format'      => 'JSON',
            'return_url'  => $config['returnUrl'],
            'charset'     => $config['charset'],
            'sign_type'   => $config['signType'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => $config['version'],
            'notify_url'  => $config['notifyUrl'],
            'biz_content' => json_encode([
                'trade_no'     => $tradeNo,
                'out_trade_no' => $outTradeNo,
            ]),
        );
        $reqParams["sign"] = $this->getSign($reqParams);
        $result = HttpCurl::post(self::ALIPAY_GATEWAY_URL, $reqParams);
        return json_decode($result, true);
    }

    /**
     * 退款
     * @param float $refundAmount 转账金额，单位：元。
     * @param string $tradeNo 支付宝交易流水号,16-64位,和outTradeNo不能同时为空，如果同时传了outTradeNo和tradeNo，则以tradeNo为准。
     * @param string $outTradeNo 订单支付时传入的商户订单号,和tradeNo不能同时为空。
     * @return array
     */
    public function refundPay($refundAmount, $tradeNo, $outTradeNo)
    {
        $config = $this->_aliConf;
        //请求参数
        $reqParams = array(
            'app_id'      => $config['appId'],
            'method'      => 'alipay.trade.refund',             //接口名称
            'format'      => 'JSON',
            'charset'     => $config['charset'],
            'sign_type'   => $config['signType'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => $config['version'],
            'biz_content' => json_encode([
                'trade_no'     => $tradeNo,
                'out_trade_no' => $outTradeNo,
                'refund_amount'=> $refundAmount,
            ]),
        );
        $reqParams["sign"] = $this->getSign($reqParams);
        $result = HttpCurl::post(self::ALIPAY_GATEWAY_URL, $reqParams);
        $result = iconv('GBK', 'UTF-8//IGNORE', $result);
        return json_decode($result,true);
    }

}

