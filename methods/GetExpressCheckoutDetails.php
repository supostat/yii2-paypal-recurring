<?php

namespace purrweb\methods;

use yii\base\Exception;
use linslin\yii2\curl;

class GetExpressCheckoutDetails
{

    public static function getPayerInfo($params)
    {
        try {
            $curl = new curl\Curl();

            $response = $curl->setOption(
                CURLOPT_POSTFIELDS,
                http_build_query(array(
                        'USER' => $params['USER'],
                        'PWD' => $params['PWD'],
                        'SIGNATURE' => $params['SIGNATURE'],
                        'METHOD' => 'GetExpressCheckoutDetails',
                        'VERSION' => $params['VERSION'],
                        'TOKEN' => $params['TOKEN'],
                    )
                ))
                ->post('https://api-3t.sandbox.paypal.com/nvp');

            parse_str(urldecode($response), $request);

            return $request;
        } catch (Exception $e) {
            $request['ACK'] = 'Error';
            $request['L_SHORTMESSAGE0'] = $e->getMessage();

            return $request;
        }
    }
}