<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

use Contao\Controller;
use Exception;
use Klarna\Rest\CustomerToken\Tokens;
use Klarna\Rest\Payments\Orders;
use Klarna\Rest\Payments\Sessions;
use Klarna\Rest\Transport\Connector;
use Klarna\Rest\Transport\ConnectorInterface;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use Psr\Log\LoggerInterface;
use ResultPrinter;

/**
 * Class HvzKlarna
 *
 *
 *
 * @package Chuckki\ContaoHvzBundle
 */
class HvzKlarna
{
    private $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getKlarnaSession(HvzOrderModel $hvzOrderModel ): Sessions
    {
        $merchantId   = getenv('KLARNA_USER') ?: 'PK09676_34cc248c6138';
        $sharedSecret = getenv('KLARNA_PW') ?: 'HpCMzNiLb7Jy12Kd';
        $klarnaEnv    = getenv('KLARNA_ENV') ?: 'https://api.playground.klarna.com';

        $connector = Connector::create($merchantId, $sharedSecret, $klarnaEnv);
        $order = self::getBillingData($hvzOrderModel);

        try {
            $session = new Sessions($connector);
            $session->create($order);
            return $session;
        } catch (Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
            exit(0);
        }
    }

    public function executePayment(HvzOrderModel $orderModel)
    {
        $merchantId         = getenv('KLARNA_USER') ?: 'K123456_abcd12345';
        $sharedSecret       = getenv('KLARNA_PW') ?: 'sharedSecret';
        $authorizationToken = $orderModel->klarna_auth_token ?: 'authorizationToken';
        $klarnaEnv          = getenv('KLARNA_ENV') ?: 'https://api.playground.klarna.com';

        $connector = Connector::create($merchantId, $sharedSecret, $klarnaEnv);
        $address = [
            "given_name"      => "Omer",
            "family_name"     => "Heberstreit",
            "email"           => "omer+red@Heberstreit.com",
            "title"           => "Herr",
            "street_address"  => "Im Friedenstal 38",
            "street_address2" => "2. Stock",
            "postal_code"     => "55006",
            "city"            => "WestSchon Matishagenfeld",
            "region"          => "",
            "phone"           => "+491522113356",
            "country"         => "DE"
        ];
        $data = self::getBillingData($orderModel);

        try {
            $order = new Orders($connector, $authorizationToken);
            $data  = $order->create($data);
            //dump($data);
            //die;
            return $data['redirect_url'];

        } catch (Exception $e) {
            print_r($e->getMessage());
            die;
        }

        die('error HvzKlarna');

    }

    private function getBillingData(HvzOrderModel $hvzOrderModel): array
    {
        return [
            "auto_capture"      => true, //https://developers.klarna.com/documentation/klarna-payments/integration-guide/place-order#4-3-place-recurring-order-tokenization
            "purchase_country"  => "DE",
            "purchase_currency" => "EUR",
            "locale"            => "de-DE",
            "merchant_data" => $hvzOrderModel->orderNumber,
            // todo: check it
            "merchant_reference1" => 'hier',

            "order_amount"      => self::getCents($hvzOrderModel->getBrutto()),
            "order_tax_amount"  => self::getCents($hvzOrderModel->getMwSt()),
            "order_lines"       => [
                [
                    "type"                  => "physical",
                    "reference"             => $hvzOrderModel->hvz,
                    "name"                  => 'Halterverbotszone in ' . $hvzOrderModel->hvz_ort,
                    "quantity"              => 1,
                    "product_url"           => $hvzOrderModel->getAbsoluteUrl(),
                    "tax_rate"              => (HvzOrderModel::MWST_INTL_GERMANY)*100,
                    "total_tax_amount"      => self::getCents($hvzOrderModel->getMwSt()),
                    "total_amount"          => self::getCents($hvzOrderModel->getBrutto()),
                    "unit_price"            => self::getCents($hvzOrderModel->getBrutto() + $hvzOrderModel->getRabatt()),
                    "total_discount_amount" => self::getCents($hvzOrderModel->getRabatt()),
                ]
            ]
        ];
    }

    private function getCents(string $string)
    {
        $string = round(floatval($string),5);
        $arr = explode('.', $string);
        if(count($arr) === 2){
            if(strlen($arr[1]) == 1){
                $arr[1] .= "0";
            }
            $value = (int)implode("", $arr);
        }elseif(count($arr) === 1){
             $value = (int) (implode("", $arr)."00");
        }else{

        }
        return $value;
    }

}
