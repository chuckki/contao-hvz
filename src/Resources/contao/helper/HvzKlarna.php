<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

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

    private static function getCents(string $string)
    {
        $arr = explode('.', $string);
        if(count($arr) === 2){
            return (int)implode("", $arr);
        }elseif(count($arr) === 1){
            return (int) (implode("", $arr)."00");
        }
    }

    public static function getKlarnaSession(array $arrSubmitted): Sessions
    {
        $merchantId   = getenv('KLARNA_USER') ?: 'K123456_abcd12345';
        $sharedSecret = getenv('KLARNA_PW') ?: 'sharedSecret';
        $klarnaEnv    = getenv('KLARNA_ENV') ?: 'https://api.playground.klarna.com';

        /*
        EU_BASE_URL = 'https://api.klarna.com'
        EU_TEST_BASE_URL = 'https://api.playground.klarna.com'
        NA_BASE_URL = 'https://api-na.klarna.com'
        NA_TEST_BASE_URL = 'https://api-na.playground.klarna.com'
        //$apiEndpoint = ConnectorInterface::$klarnaEnv;
        */
        $connector = Connector::create($merchantId, $sharedSecret, $klarnaEnv);

        dump ($arrSubmitted);

        $order = [
            "auto_capture"      => true, //https://developers.klarna.com/documentation/klarna-payments/integration-guide/place-order#4-3-place-recurring-order-tokenization
            "purchase_country"  => "DE",
            "purchase_currency" => "EUR",
            "locale"            => "de-DE",
            "merchant_data" => $arrSubmitted['orderNumber'],
            "merchant_reference1" => 'hier',

            "order_amount"      => self::getCents($arrSubmitted['fullNetto'] +$arrSubmitted['PreisMwSt']),
            "order_tax_amount"  => self::getCents($arrSubmitted['PreisMwSt']),
            "order_lines"       => [
                [
                    "type"                  => "physical",
                    "reference"             => $arrSubmitted['hvzID'],
                    "name"                  => 'Bestellung einer Halterverbotszone in ' . $arrSubmitted['Ort'],
                    "quantity"              => 1,
                    "unit_price"            => self::getCents($arrSubmitted['fullNetto'] - $arrSubmitted['rabattValue']),
                    "tax_rate"              => 1900,
                    "total_amount"          => self::getCents($arrSubmitted['fullNetto'] + (float)$arrSubmitted['PreisMwSt']),
                    "total_discount_amount" => self::getCents($arrSubmitted['rabattValue']),
                    "total_tax_amount"      => self::getCents($arrSubmitted['PreisMwSt']),
                ]
            ],

        ];

        dump($order);
        die;


        try {
            $session = new Sessions($connector);
            $session->create($order);
            // Get some data if needed
            //         Session ID: $sessionId
            //         Client Token: $session[client_token]
            return $session;
        } catch (Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
            exit(0);
        }
    }

    public static function executePayment($token, HvzOrderModel $orderModel)
    {
        $merchantId         = getenv('KLARNA_USER') ?: 'K123456_abcd12345';
        $sharedSecret       = getenv('KLARNA_PW') ?: 'sharedSecret';
        $authorizationToken = $token ?: 'authorizationToken';
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

        $data = [
            //            "billing_address" => $address,
            //            "shipping_address" => $address,
            "auto_capture"      => true,
            "purchase_country"  => "DE",
            "purchase_currency" => "EUR",
            "locale"            => "de-DE",
/*
            "order_amount"     => 6000,
            "order_tax_amount" => 1200,
            "order_lines"      => [
                [
                    "type"             => "physical",
                    "reference"        => "123050",
                    "name"             => "Tomatoes",
                    "quantity"         => 10,
                    "quantity_unit"    => "kg",
                    "unit_price"       => 600,
                    "tax_rate"         => 2500,
                    "total_amount"     => 6000,
                    "total_tax_amount" => 1200
                ]
            ],
*/

            "order_amount"      => self::getCents($orderModel->hvz_preis)-23,
            "order_tax_amount"  => 0,
            "order_lines"       => [
                [
                    "type"                  => "physical",
                    "reference"             => $orderModel->orderNumber,
                    "name"                  => 'Bestellung einer Halterverbotszone in ' . $orderModel->hvz_ort,
                    "quantity"              => 1,
                    "unit_price"            => self::getCents($orderModel->hvz_preis), //self::getCents($arrSubmitted['EndPreis']),
                    "tax_rate"              => 1900,
                    "total_amount"          => self::getCents($orderModel->hvz_preis)-23,
                    "total_discount_amount" => 23,
                    "total_tax_amount"      => 0
                ]
            ],





            "merchant_urls"    => [
                "confirmation" => 'http://hvb2018.test/bestellung-abgeschlossen.html',
                "notification" => "https://example.com/pending" // optional
            ]
        ];
        try {
            $order = new Orders($connector, $authorizationToken);
            $data  = $order->create($data);
            dump($data);
            die;
            return $data['redirect_url'];

        } catch (Exception $e) {
            dump($e->getMessage());
            die;
        }

        die('error HvzKlarna');

    }



    public static function getTokenDetails($customerToken)
    {
        $merchantId         = getenv('KLARNA_USER') ?: 'K123456_abcd12345';
        $sharedSecret       = getenv('KLARNA_PW') ?: 'sharedSecret';
        $klarnaEnv          = getenv('KLARNA_ENV') ?: 'https://api.playground.klarna.com';

        $connector = Connector::create($merchantId, $sharedSecret, $klarnaEnv);

        try {
            $token = new Tokens($connector, $customerToken);
            $token->fetch();
            print_r($token->getArrayCopy());
            die;
        } catch (Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }
}
