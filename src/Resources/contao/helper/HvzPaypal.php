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
use PayPal\Api\InputFields;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Presentation;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\WebProfile;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use Psr\Log\LoggerInterface;
use ResultPrinter;

class HvzPaypal
{
    private $clientId;
    private $clientSecret;
    private $env;
    private $logger;
    private $contaoFramework;

    public function __construct(LoggerInterface $logger, ContaoFrameworkInterface $contaoFramework)
    {
        $this->contaoFramework = $contaoFramework;
        $this->contaoFramework->initialize();
        $this->initialCredits($GLOBALS['TL_CONFIG']);
        $this->logger = $logger;
    }

    public function createProfile()
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential($this->clientId, $this->clientSecret)
        );
        // Parameters for style and presentation.
        $presentation = new \PayPal\Api\Presentation();
        // A URL to logo image. Allowed vaues: .gif, .jpg, or .png.
        $presentation->setLogoImage(
            "https://www.halteverbot-beantragen.de/files/halteverbot-theme/img/halteverbot-beantragen-Paypal.png"
        )//	A label that overrides the business name in the PayPal account on the PayPal pages.
        ->setBrandName("Halteverbot Beantragen")//  Locale of pages displayed by PayPal payment experience.
        ->setLocaleCode("DE")// A label to use as hypertext for the return to merchant link.
        ->setReturnUrlLabel(
            "zurück"
        )// A label to use as the title for the note to seller field. Used only when `allow_note` is `1`.
            ->setNoteToSellerLabel("Danke schön!");
        // Parameters for input fields customization.
        $inputFields = new \PayPal\Api\InputFields();
        // Enables the buyer to enter a note to the merchant on the PayPal page during checkout.
        $inputFields->setAllowNote(
            true
        )// Determines whether or not PayPal displays shipping address fields on the experience pages. Allowed values: 0, 1, or 2. When set to 0, PayPal displays the shipping address on the PayPal pages. When set to 1, PayPal does not display shipping address fields whatsoever. When set to 2, if you do not pass the shipping address, PayPal obtains it from the buyer’s account profile. For digital goods, this field is required, and you must set it to 1.
            ->setNoShipping(
                1
            )// Determines whether or not the PayPal pages should display the shipping address and not the shipping address on file with PayPal for this buyer. Displaying the PayPal street address on file does not allow the buyer to edit that address. Allowed values: 0 or 1. When set to 0, the PayPal pages should not display the shipping address. When set to 1, the PayPal pages should display the shipping address.
            ->setAddressOverride(0);
        // #### Payment Web experience profile resource
        $webProfile = new \PayPal\Api\WebProfile();
        // Name of the web experience profile. Required. Must be unique
        $webProfile->setName("Halteverbot Online Demo2" . uniqid())// Parameters for flow configuration.
        ->setPresentation($presentation)// Parameters for input field customization.
        ->setInputFields(
            $inputFields
        )// Indicates whether the profile persists for three hours or permanently. Set to `false` to persist the profile permanently. Set to `true` to persist the profile for three hours.
            ->setTemporary(false);
        try {
            // Use this call to create a profile.
            $createProfileResponse = $webProfile->create($apiContext);
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
            ResultPrinter::printError("Created Web Profile", "Web Profile", null, $request, $ex);
            dump('regest');
            dump($request);
            die;

        }
        dump($createProfileResponse);
        die;

    }

    public function initialCredits(array $conf)
    {
        $this->clientId = $conf['paypal_id'];
        $this->clientSecret = $conf['paypal_secret'];
        $this->env = $conf['paypal_env'];
    }

    public function generatePayment(HvzOrderModel $hvzOrderModel): ?Payment
    {
        $apiContext = new ApiContext(new OAuthTokenCredential($this->clientId, $this->clientSecret));

        if ($this->env) {
            $apiContext->setConfig(['mode' => 'sandbox']);
        } else {
            $apiContext->setConfig(['mode' => 'live']);
        };

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
        $payment->setExperienceProfileId('XP-PMY9-TKQ2-4KR9-4BEH');
        // 4. Make a Create Call and print the values
        try {
            $payment->create($apiContext);
            if ($payment->state !== 'created') {
                $this->logger->error(
                    'Paypal created Order not working. State:' . $payment->state . " paypal_id:" . $payment->id
                    . " order_id:" . $hvzOrderModel->orderNumber
                );
                PushMeMessage::pushMe(
                    "Payment state: \n created != " . $payment->state . "\n\n paypal_id:" . $payment->id
                    . "\n order_id:" . $hvzOrderModel->orderNumber,
                    'HvzPaypal'
                );
            }
            return $payment;
        } catch (PayPalConnectionException $ex) {
            PushMeMessage::pushMe("Paypal Exception: " . $ex->getData());
        }
        return null;
    }

    public function executePayment($paymentId, $payerId): Payment
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential($this->clientId, $this->clientSecret)
        );
        $apiContext->setConfig(['mode' => 'sandbox']);
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

    private function getApiContext()
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential($this->clientId, $this->clientSecret)
        );
        $apiContext->setConfig(['mode' => 'sandbox']);
        return $apiContext;
    }

}
