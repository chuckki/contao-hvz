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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            throw new NotFoundHttpException();
        }
        if (!empty($orderObj->paypal_approvalLink) and empty(Input::get('paymentId'))) {
            // Start Payment
            if ($GLOBALS['TL_CONFIG']['paypal_env']) {
                $this->Template->mode = 'sandbox';
            } else {
                $this->Template->mode = 'live';
            }


            $this->Template->approvalUrl = $orderObj->paypal_approvalLink;
            $this->Template->editOrder = $orderObj->getErrorOrderPage();
        }
    }
}
