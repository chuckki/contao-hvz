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
        $orderObj = HvzOrderModel::findOneBy('hash', System::getContainer()->get('session')->get('orderToken'));
        if(!$orderObj){
                // todo: falsher hash - was jetzt?
        }

        if(!empty($orderObj->klarna_client_token) and empty(Input::get('auth')))
        {
            $this->Template->clientToken = $orderObj->klarna_client_token;
        }

        if(!empty(Input::get('auth')))
        {
            $orderObj->klarna_auth_token = Input::get('auth');
            $orderObj->save();

            $hvzKlarna = System::getContainer()->get('chuckki.contao_hvz_bundle.klarna');

            $redirectUrl = $hvzKlarna->executePayment($orderObj);
            \Controller::redirect($redirectUrl);
        }
    }
}
