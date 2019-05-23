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
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

/**
 * @author Dennis Esken
 */
class ModuleHvzPaypal extends \Module
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
        // Start Payment
        if (!empty(System::getContainer()->get('session')->get('ApprovalLink'))) {
            $this->Template->approvalUrl = System::getContainer()->get('session')->get('ApprovalLink');
            System::getContainer()->get('session')->clear();
        } else {
            // End Payment
            $paymentId = Input::get('paymentId');
            if (!empty($paymentId)) {
                $this->strTemplate = null;
                $orderObj          = HvzOrderModel::findBy('paypal_paymentId', $paymentId);
                dump($orderObj);
                if (empty($orderObj)) {
                    dump('nix gefunden');
                    // todo: log it or pushme it
                } else {
                    $orderObj->paypal_PayerID = Input::get('PayerID');
                    $orderObj->paypal_token   = Input::get('token');
                    $orderObj->save();
                }

                $payment = HvzPaypal::executePayment($paymentId, $orderObj->paypal_PayerID);

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
