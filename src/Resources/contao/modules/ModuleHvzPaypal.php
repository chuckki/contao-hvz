<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

use Contao\Input;
use Contao\System;
use Haste\Frontend\AbstractFrontendModule;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

/**
 * @author Dennis Esken
 */
class ModuleHvzPaypal extends AbstractFrontendModule
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_hvzpaypal';

    /**
     * Target pages.
     *
     * @var array
     */
    protected $arrTargets = [];

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### Paypal Plus Bezahlung ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the module.
     */
    protected function compile()
    {
        $orderObj = HvzOrderModel::findOneBy('hash', System::getContainer()->get('session')->get('orderToken'));
        if (!$orderObj) {
            PushMeMessage::pushMe('Paypal Order not found by PaymentId: '.$orderObj->paypal_paymentId);
        }
        if (!empty($orderObj->paypal_approvalLink) and empty(Input::get('paymentId'))) {
            // Start Payment
            $this->Template->approvalUrl = $orderObj->paypal_approvalLink;
        }

        if(!empty(Input::get('paymentId')))
        {
            // End Payment
            $paymentId = Input::get('paymentId');
            if (!empty($paymentId)) {
                $this->strTemplate = null;
                $currentOrderObj   = HvzOrderModel::findOneBy('paypal_paymentId', $orderObj->paypal_paymentId);

                if (empty($currentOrderObj) or $currentOrderObj->paypal_paymentId !== $orderObj->paypal_paymentId) {
                    PushMeMessage::pushMe('Paypal Order not found by PaymentId: '.$orderObj->paypal_paymentId);
                    dump('nix gefunden');
                    // todo: log it or pushme it
                } else {
                    $orderObj->paypal_PayerID = Input::get('PayerID');
                    $orderObj->paypal_token   = Input::get('token');
                    $orderObj->save();
                }

                $hvzPaypal = System::getContainer()->get('chuckki.contao_hvz_bundle.paypal');

                dump($payment);

                $payment = $hvzPaypal->executePayment($paymentId, $orderObj->paypal_PayerID);
                ModuleHvz::setSessionForThankYouPage($orderObj);

                dump($payment);
                die;
                if($payment){
//                    $orderObj->paypal_first_name = $payment->getPayer()->getPayerInfo()->
                }

                //die;
                // safe data
                //order -> field
                // do confirm
                
                
            }
        }
    }
}
