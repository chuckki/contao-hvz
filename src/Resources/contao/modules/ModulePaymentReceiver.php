<?php

namespace Chuckki\ContaoHvzBundle;

use Contao\Input;
use Contao\System;
use Haste\Frontend\AbstractFrontendModule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ModulePaymentReceiver extends AbstractFrontendModule
{

    protected $strTemplate = 'mod_paymentreceiver';

    // Auf der "Vielen Dank für die Bestellung"-Seite
    // Anzeigen von Transaktion bla erfolgreich bla
    // Transaktion speichern (erfolgreich)
    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate           = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### Paypal Receiver ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();
        }
        return parent::generate();
    }

    /**
     * Generate the module.
     */
    protected function compile()
    {
        $session  = System::getContainer()->get('session');
        $orderObj = HvzOrderModel::findOneBy('hash', $session->get('orderToken'));
        if (empty($orderObj)) {
            throw new NotFoundHttpException();
        }
        // receive Paypal
        if (!empty(Input::get('paymentId'))) {
            // End Payment
            $paymentId = Input::get('paymentId');
            if (!empty($paymentId)) {
                $this->strTemplate = null;
                $currentOrderObj   = HvzOrderModel::findOneBy('paypal_paymentId', $orderObj->paypal_paymentId);
                if (empty($currentOrderObj) or $currentOrderObj->paypal_paymentId !== $orderObj->paypal_paymentId) {
                    PushMeMessage::pushMe('Paypal Order not found by PaymentId: ' . $orderObj->paypal_paymentId);
                } else {
                    $orderObj->paypal_PayerID = Input::get('PayerID');
                    $orderObj->paypal_token   = Input::get('token');
                    $orderObj->payment_status = "Payed via Paypal";
                    $orderObj->save();
                }
                $hvzPaypal = System::getContainer()->get('chuckki.contao_hvz_bundle.paypal');
                $payment   = $hvzPaypal->executePayment($paymentId, $orderObj->paypal_PayerID);
                ModuleHvz::setSessionForThankYouPage($orderObj);
                if ($payment) {
//                    $orderObj->paypal_first_name = $payment->getPayer()->getPayerInfo()->
                }
            }
        }
        // receive Klarna
        if (!empty(Input::get('auth'))) {
            $orderObj->klarna_auth_token = Input::get('auth');
            $hvzKlarna                   = System::getContainer()->get('chuckki.contao_hvz_bundle.klarna');
            $data                        = $hvzKlarna->executePayment($orderObj);
            $orderObj->klarna_order_id = $data['order_id'];
            $orderObj->payment_status  = "Payed via Klarna";
            $orderObj->save();
            if ($data['fraud_status']) {
                PushMeMessage::pushMe('Klarna Payment was not successfull:' . $orderObj->klarna_order_id);
            }
        }
        $session->clear();
        ModuleHvz::setSessionForThankYouPage($orderObj);

    }

}
