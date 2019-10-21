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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Dennis Esken
 */
class ModuleHvzKlarna extends \Module
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_hvzklarna';

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
            $objTemplate->wildcard = '### Klarna Bezahlung ###';
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
            PushMeMessage::pushMe('Klarna Order not found by PaymentId: '.$orderObj->paypal_paymentId);
            throw new NotFoundHttpException();
        }
        if (!empty($orderObj->klarna_client_token) and empty(Input::get('auth'))) {
            $this->Template->clientToken = $orderObj->klarna_client_token;
            $this->Template->successSite = $orderObj->getFinishOrderPage();
            $this->Template->editOrder = $orderObj->getErrorOrderPage();
            $this->Template->payment_method_category = ($GLOBALS['TL_CONFIG']['klarna_env']) ? 'pay_later' : 'pay_now';
        }
        // receive Klarna
        if (!empty(Input::get('auth'))) {
            $orderObj->klarna_auth_token = Input::get('auth');
            $orderObj->save();
            $hvzKlarna = System::getContainer()->get('chuckki.contao_hvz_bundle.klarna');
            $data = $hvzKlarna->executePayment($orderObj);
            $orderObj->klarna_order_id = $data['order_id'];
            $orderObj->payment_status = 'Payed via Klarna';
            $orderObj->save();
            if ('ACCEPTED' !== $data['fraud_status']) {
                PushMeMessage::pushMe('Klarna Payment was not successfull ('.$data['fraud_status'].'):'.$orderObj->klarna_order_id);
                $orderObj->payment_status = 'Payed via Klarna failed: '.$data['fraud_status'];
            }
            $orderObj->save();
            self::redirect($data['redirect_url']);
        }
    }
}
