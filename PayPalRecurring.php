<?php

namespace purrweb;

use common\models\User;
use Exception;
use Yii;
use yii\base\Component;
use linslin\yii2\curl;
use yii\helpers\VarDumper;
use yii\web\HttpException;

class PayPalRecurring extends Component
{
    public $recurringParams;

    public function SetExpressCheckout($user_hash = null)
    {
        $request = [
            'USER' => $this->recurringParams['USER'],
            'PWD' => $this->recurringParams['PWD'],
            'SIGNATURE' => $this->recurringParams['SIGNATURE'],
            'METHOD' => 'SetExpressCheckout',
            'VERSION' => $this->recurringParams['VERSION'],
            'L_BILLINGTYPE0' => 'RecurringPayments',
            'L_BILLINGAGREEMENTDESCRIPTION0' => "Subscription to http://passgeek.com for $100 per month",
            'cancelUrl' => $this->recurringParams['cancelUrl'],
            'returnUrl' => $this->recurringParams['returnUrl'],
        ];

        if ($user_hash) {
            $user = User::findOne(['hash' => $user_hash]);
            if ($user) {
                $request['PAYMENTREQUEST_0_CUSTOM'] = $user_hash;
                $request['L_BILLINGAGREEMENTDESCRIPTION0'] = "Subscription to http://passgeek.com for $100 per month Subscriber name: "
                    . $user->first_name . " "
                    . $user->last_name;
            } else {
                throw new HttpException(404, 'Page not found');
            }
        }

        $result = $this->_sendRequest($request);

        return "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=" . $result['TOKEN'];
    }

    public function GetExpressCheckoutDetails($token)
    {
        $this->recurringParams['TOKEN'] = $token;

        $request = [
            'USER' => $this->recurringParams['USER'],
            'PWD' => $this->recurringParams['PWD'],
            'SIGNATURE' => $this->recurringParams['SIGNATURE'],
            'METHOD' => 'GetExpressCheckoutDetails',
            'VERSION' => $this->recurringParams['VERSION'],
            'TOKEN' => $token,
        ];

        return $this->_sendRequest($request);
    }

    public function CreateRecurringPaymentsProfile(RecurringSubscription $subscription)
    {
        $request = [
            'USER' => $this->recurringParams['USER'],
            'PWD' => $this->recurringParams['PWD'],
            'SIGNATURE' => $this->recurringParams['SIGNATURE'],

            'METHOD' => 'CreateRecurringPaymentsProfile',

            'VERSION' => $this->recurringParams['VERSION'],
            'TOKEN' => $this->recurringParams['TOKEN'],

            'PAYERID' => $subscription->PAYERID,

            'PROFILESTARTDATE' => $subscription->PROFILESTARTDATE,

            'DESC' => $subscription->DESC,

            'BILLINGPERIOD' => $subscription->BILLINGPERIOD,
            'INITAMT' => $subscription->INITAMT,
            'FAILEDINITAMTACTION' => $subscription->FAILEDINITAMTACTION,
            'BILLINGFREQUENCY' => $subscription->BILLINGFREQUENCY,
            'AMT' => $subscription->AMT,
            'CURRENCYCODE' => 'USD',
            'COUNTRYCODE' => 'US',
            'MAXFAILEDPAYMENTS' => 3
        ];

        return $this->_sendRequest($request);
    }

    private function _sendRequest($request)
    {
        $curl = new curl\Curl();

        try {
            $response = $curl->setOption(CURLOPT_POSTFIELDS, http_build_query($request))
                ->post('https://api-3t.sandbox.paypal.com/nvp');

            parse_str(urldecode($response), $result);
        } catch (Exception $e) {
            $result['ACK'] = 'Error';
            $result['L_SHORTMESSAGE0'] = $e->getMessage();
        }

        if ($result['ACK'] == 'Success') {
            return $result;
        }
        Yii::$app->getSession()->setFlash('error', $result['L_SHORTMESSAGE0']);

        return Yii::$app->controller->redirect(Yii::$app->request->referrer);
    }
}
