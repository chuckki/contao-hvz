<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class HvzPaypal
{
    public static function generatePayment(array $arrSubmitted): Payment
    {
        $apiContext =
            new ApiContext(new OAuthTokenCredential('ASddZjQ8AbeJ-2tAgopOngzmw3sFQzTheRcLPTMkTFDgrgnJOI5Wn-XigOZFkg7mKCbt4Bf2Od913yOl',
                // ClientID
                'ENLqp1u71TGJ6dE_kwA_j_CmUD_7xHWfydOi1BgmF1Bb9BQqZfMwwlXFwY-Dtu-k2qEE0wUCPPSpkXOl'      // ClientSecret
            ));

        // 3. Lets try to create a Payment

        // https://developer.paypal.com/docs/api/payments/v2/
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $details = new Details();
        $details->setTax($arrSubmitted['preisDetaisl']['newMst']);
        $details->setSubtotal($arrSubmitted['preisDetaisl']['netto']);

        $itemList = new ItemList();
        $item = new Item();
        $item->setDescription($arrSubmitted['Genehmigung'].' in '.$arrSubmitted['Ort']);
        $item->setPrice($arrSubmitted['preisDetaisl']['netto']);
        $item->setCurrency('EUR');
        $item->setQuantity(1);
        $itemList->setItems([$item]);

        $amount = new Amount();
        $amount->setTotal($arrSubmitted['preisDetaisl']['brutto']);
        $amount->setCurrency('EUR');
        $amount->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('Bestellung einer Halterverbotszone in '.$arrSubmitted['Ort']);
        $transaction->setInvoiceNumber($arrSubmitted['orderNumber']);
        $transaction->setItemList($itemList);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl('http://hvb2018.test/bestellung-abgeschlossen.html')
            ->setCancelUrl('https://example.com/your_cancel_url.html');

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions([$transaction])
            ->setRedirectUrls($redirectUrls);

        // 4. Make a Create Call and print the values
        try {
            $payment->create($apiContext);
            //echo $payment;
            return $payment;
            //echo "\n\nRedirect user to approval_url: " . $payment->getApprovalLink() . "\n";
        } catch (PayPalConnectionException $ex) {
            // This will print the detailed information on the exception.
            //REALLY HELPFUL FOR DEBUGGING
            echo $ex->getData();
            // todo: log it
        }

        return null;
    }
}
