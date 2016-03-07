<?php

namespace purrweb\methods;

use linslin\yii2\curl;
use Yii;
use yii\base\Exception;

class SetExpressCheckout
{
    public static function getSandBoxPaymentUrl($request)
    {
        if ($request['ACK'] == 'Success') {
            return "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=" . $request['TOKEN'];
        }

        Yii::$app->getSession()->setFlash('error', $request['L_SHORTMESSAGE0']);

        return Yii::$app->controller->redirect(Yii::$app->request->referrer);
    }

    public static function getPaymentUrl($request)
    {
        if ($request['ACK'] == 'Success') {
            return "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=" . $request['TOKEN'];
        }

        Yii::$app->getSession()->setFlash('error', $request['L_SHORTMESSAGE0']);
        return Yii::$app->controller->redirect(Yii::$app->request->referrer);
    }

    public static function getToken($params)
    {
        $curl = new curl\Curl();

        try {
            $response = $curl->setOption(
                CURLOPT_POSTFIELDS,
                http_build_query(array(
                        'USER' => $params['USER'],
                        'PWD' => $params['PWD'],
                        'SIGNATURE' => $params['SIGNATURE'],
                        'METHOD' => 'SetExpressCheckout',
                        'VERSION' => $params['VERSION'],
                        'L_BILLINGTYPE0' => 'RecurringPayments',
                        'L_BILLINGAGREEMENTDESCRIPTION0' => 'Test description',
                        'cancelUrl' => $params['cancelUrl'],
                        'returnUrl' => $params['returnUrl'],
                    )
                ))->post('https://api-3t.sandbox.paypal.com/nvp');

            parse_str(urldecode($response), $request);

            return $request;
        } catch (Exception $e) {
            $request['ACK'] = 'Error';
            $request['L_SHORTMESSAGE0'] = $e->getMessage();

            return $request;
        }

    }
}