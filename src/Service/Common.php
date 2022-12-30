<?php
namespace App\Service;

/**
 * 公用方法
 *
 *
 *
 */
class Common
{ 
    protected static $debug;
    const RETURN_CODE_SUCCESS   = '200';
    const RETURN_CODE_FAIL      = '500';

    /**
     * geo helper 地址转换为坐标
     * @param $address
     * @return bool|string
     */
    public function geoHelperAddress($address, $merchant_id = '')
    {

        try {
            $returnData = '';
            //1,校验地址是否为空
            if($address){
                //2,地址不为空取缓存信息
                $cackeKey = 'cache-address-'.$address;

                // 從獲取座標
                $returnData = redisx()->get($cackeKey);
                if ($returnData) {
                    //3,缓存中有值直接返回
                    return $returnData;
                }else{
                    //4,缓存中没有值请求三方接口
                    $locationByAddress = $this->getLocationByAddress($merchant_id);
                    if(isset($locationByAddress['status']) == self::RETURN_CODE_SUCCESS){
                        $returnData = $locationByAddress['data']  ?: '';
                    }else{
                        $locationByMId = $this->getLocationByMerchantId($merchant_id);
                        if(isset($locationByMId['status']) == self::RETURN_CODE_SUCCESS){
                            $returnData = $locationByMId['data']  ?: '';
                        }
                    }
                }
            }else{
                //5,地址为空根据商家ID取
                $locationByMId = $this->getLocationByMerchantId($merchant_id);
                if(isset($locationByMId['status']) == self::RETURN_CODE_SUCCESS){
                    $returnData = $locationByMId['data']  ?: '';
                }
            }
            return $returnData;

        } catch (\Throwable $t) {
            criticalLog('geoHelperAddress critical ==' . $t->getMessage());
            return 0;
        }
    }

    /*
     * @DESC 根据地址获取坐标
     * @author lishuqiang
     * @date 20221230
     * @param string address
     * @return array
     */
    public static function getLocationByAddress($address)
    {
        $returnData['status']   = self::RETURN_CODE_SUCCESS;//返回状态码
        $returnData['msg']      = ''; //提示信息
        $returnData['data']     = '';//返回数据信息
        if(!empty($address)){
            $cackeKey = 'cache-address-'.$address;
            $key = 'time=' . time();

            // requestLog：寫日志
            requestLog('Backend', 'Thrift', 'Http', 'phpgeohelper\\Geocoding->convert_addresses', 'https://geo-helper-hostr.ks-it.co',  [[$address, $key]]);

            // getThriftService： 獲取 Thrift 服務
            $geoHelper = ServiceContainer::getThriftService('phpgeohelper\\Geocoding');
            $param = json_encode([[$address, $key]]);

            // 調用接口，以地址獲取座標
            //请求第三方接口传参记录日志
            responseLog('Backend', 'get location inter params', 'https://geo-helper-hostr.ks-it.co', '200', '0',  json_encode($param));
            $response = $geoHelper->convert_addresses($param);
            //请求第三方接口返回值记录日志
            responseLog('Backend', 'get location inter result', 'https://geo-helper-hostr.ks-it.co', '200', '0',  $response);
            $response = json_decode($response, true);

            if(empty($response)){
                $returnData['status']   = self::RETURN_CODE_FAIL;
                $returnData['msg']      = 'requeset get location inter result empty';
            }else{
                if ($response['error'] == 0) {
                    responseLog('Backend', 'phpgeohelper\\Geocoding->hksf_addresses', 'https://geo-helper-hostr.ks-it.co', '200', '0',  $response);
                    $data = $response['data'][0];
                    $coordinate = $data['coordinate'];

                    // 如果返回 '-999,-999'，表示調用接口失敗，那麼直接使用商家位置的座標
                    if ($coordinate == '-999,-999') {
                        infoLog('geoHelper->hksf_addresses change failed === ' . $address);

                        $returnData['status']   = self::RETURN_CODE_FAIL;
                        $returnData['msg']      = 'requeset get location inter result error coordinate：'.$coordinate;

                    }
                    if (!isset($data['error']) && (strpos($coordinate,',') !== false)) {
                        $arr = explode(',', $coordinate);
                        $user_location = $arr[1] . ',' . $arr[0];

                        // set cache
                        redisx()->set($cackeKey, $user_location);
                        $returnData['data'] = $user_location;
                    }
                }else{
                    $returnData['status']   = self::RETURN_CODE_FAIL;
                    $returnData['msg']      = 'requeset get location inter result error data：'.$response['error'];
                }
            }


        }
        return $returnData;
    }


    /*
     * @DESC 根据商家ID获取坐标
     * @author lishuqiang
     * @date 20221230
     * @param string merchant_id
     * @return array
     */
    public static function getLocationByMerchantId($merchant_id){
        $returnData['status']   = self::RETURN_CODE_SUCCESS;//返回状态码
        $returnData['msg']      = ''; //提示信息
        $returnData['data']     = '';//返回数据信息
        if ($merchant_id) {
            $sMerchant = new Merchant();
            $res = $sMerchant->get_merchant_address($merchant_id);
            $returnData['data'] = $res['latitude'] . ',' . $res['longitude'];
        }
        return $returnData;
    }
    // 回调状态过滤
    public static function checkStatusCallback($order_id, $status)
    {
        //1,先验证参数
        $returnData['status']   = self::RETURN_CODE_SUCCESS;//返回状态码
        $returnData['msg']      = ''; //提示信息
        $returnData['data']     = '';//返回数据信息
        if(empty($order_id) || empty($status)){
            $returnData['status']   = self::RETURN_CODE_SUCCESS;
            $returnData['msg']      = 'can not call back';
        }
        
        // backend状态为 909 915 916 时 解锁工作单 但不回调
        $code_arr = ['909', '915', '916'];
        if (in_array($status, $code_arr)) {
            infoLog('checkStatusCallback backend code is 909 915 916');
            return 0;
        }

        $open_status_arr = ['901' => 1, '902' => 2, '903' => 3];
        $returnData['data'] = $order_id.'-'.$open_status_arr[$status];

        return $returnData;
    }
}
