<?php
/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Exception;
use Monolog\Logger;
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

class HvzPaypal
{
    private $clientId;
    private $clientSecret;
    private $logger;
    private $contaoFramework;

    public function __construct(LoggerInterface $logger, ContaoFrameworkInterface $contaoFramework)
    {
        $this->contaoFramework = $contaoFramework;
        $this->contaoFramework->initialize();
        $this->initialCredits($GLOBALS['TL_CONFIG']);
        $this->logger = $logger;
    }

    public function initialCredits(array $conf)
    {
        $this->clientId     = $conf['paypal_id'];
        $this->clientSecret = $conf['paypal_secret'];
    }

    public function generatePayment(HvzOrderModel $hvzOrderModel): ?Payment
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential($this->clientId, $this->clientSecret)
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
        $item->setDescription($hvzOrderModel->getOrderDescription());
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
        $transaction->setDescription($hvzOrderModel->getOrderDescription());
        $transaction->setInvoiceNumber($hvzOrderModel->orderNumber);
        $transaction->setItemList($itemList);
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($hvzOrderModel->getFinishOrderPage())->setCancelUrl(
            $hvzOrderModel->getErrorOrderPage()
        );
        $payment = new Payment();
        $payment->setIntent('sale')->setPayer($payer)->setTransactions([$transaction])->setRedirectUrls($redirectUrls);
        // 4. Make a Create Call and print the values
        try {
            $payment->create($apiContext);
            if ($payment->state !== 'created') {
                $this->logger->error('Paypal created Order not working. State:'.$payment->state . " paypal_id:" . $payment->id . " order_id:" . $hvzOrderModel->orderNumber);
                PushMeMessage::pushMe(
                    "Payment state: \n created != " . $payment->state . "\n\n paypal_id:" . $payment->id . "\n order_id:" . $hvzOrderModel->orderNumber,
                    'HvzPaypal');
            }
            return $payment;
        } catch (PayPalConnectionException $ex) {
            PushMeMessage::pushMe("Paypal Exception: " . $ex->getData());
            $logger->error('Paypal Exception - not possible to create Payment (' . $payment->id . ')', [$ex->getMessage(),$ex->getData()]);
        }
        return null;
    }

    public function executePayment($paymentId, $payerId): Payment
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential($this->clientId, $this->clientSecret)
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
            $result = $payment->execute($execution, $apiContext);
            try {
                $payment = Payment::get($paymentId, $apiContext);
            } catch (Exception $ex) {
                PushMeMessage::pushMe("Paypal Payment not exist: " . $ex->getMessage());
                exit(1);
            }
        } catch (Exception $ex) {
            PushMeMessage::pushMe("Paypal Execute Failure: " . $ex->getMessage());
            $logger->error('Paypal  Execute Failure (' . $paymentId . ')', [$ex->getMessage()]);
            exit(1);
        }
        return $result;
    }
}
