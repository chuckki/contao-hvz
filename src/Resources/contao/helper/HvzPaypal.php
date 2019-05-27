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

class HvzPaypal
{
    public static function generatePayment(HvzOrderModel $hvzOrderModel): ?Payment
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                getenv('CLIENT_ID'), getenv('CLIENT_SECRET')
            )
        );
        // 3. Lets try to create a Payment
        // https://developer.paypal.com/docs/api/payments/v2/
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $details = new Details();
        $details->setTax($hvzOrderModel->getMwSt());
        $details->setSubtotal($hvzOrderModel->getNetto());
        $itemList = new ItemList();
        $item     = new Item();
        $item->setDescription($hvzOrderModel->hvz_type_name . ' in ' . $hvzOrderModel->hvz_ort);
        $item->setPrice($hvzOrderModel->getNetto());
        $item->setCurrency('EUR');
        $item->setQuantity(1);
        $itemList->setItems([$item]);
        $amount = new Amount();
        $amount->setTotal($hvzOrderModel->getBrutto());
        $amount->setCurrency('EUR');
        $amount->setDetails($details);
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        // todo: config?
        $transaction->setDescription('Bestellung einer Halterverbotszone in ' . $hvzOrderModel->hvz_ort);
        $transaction->setInvoiceNumber($hvzOrderModel->orderNumber);
        $transaction->setItemList($itemList);
        $redirectUrls = new RedirectUrls();
        // todo: get links from modul config
        $redirectUrls->setReturnUrl('http://hvb2018.test/bestellung-abgeschlossen.html')->setCancelUrl(
                'http://hvb2018.test/bestellung-abgeschlossen-nicht.html'
            );
        $payment = new Payment();
        $payment->setIntent('sale')->setPayer($payer)->setTransactions([$transaction])->setRedirectUrls($redirectUrls);
        // 4. Make a Create Call and print the values
        try {
            $payment->create($apiContext);
            return $payment;
            //echo "\n\nRedirect user to approval_url: " . $payment->getApprovalLink() . "\n";
        } catch (PayPalConnectionException $ex) {
            // todo: catch it
            // This will print the detailed information on the exception.
            //REALLY HELPFUL FOR DEBUGGING
            echo $ex->getData();
            // @todo: log it
        }
        return null;
    }

    public static function executePayment($paymentId, $payerId): Payment
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                getenv('CLIENT_ID'), getenv('CLIENT_SECRET')
            )
        );
        // Get the payment Object by passing paymentId
        // payment id was previously stored in session in
        // CreatePaymentUsingPayPal.php
        $payment = Payment::get($paymentId, $apiContext);
        // ### Payment Execute
        // PaymentExecution object includes information necessary
        // to execute a PayPal account payment.
        // The payer_id is added to the request query parameters
        // when the user is redirected from paypal back to your site
        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);
        try {
            // Execute the payment
            // (See bootstrap.php for more on `ApiContext`)
            $result = $payment->execute($execution, $apiContext);
            try {
                $payment = Payment::get($paymentId, $apiContext);
                dump("Payment after execution:");
                dump($payment);
            } catch (Exception $ex) {
                // todo: log it
                exit(1);
            }
        } catch (Exception $ex) {
            // todo: log it
            exit(1);
        }
        return $result;
    }
}
