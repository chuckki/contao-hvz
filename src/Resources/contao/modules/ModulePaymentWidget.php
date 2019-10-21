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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ModulePaymentWidget extends AbstractFrontendModule
{
    protected $strTemplate = 'mod_paymentwidget';

    protected function compile()
    {
        $orderModel = HvzOrderModel::findOneBy('hash', System::getContainer()->get('session')->get('orderToken'));
        if (!$orderModel) {
            throw new NotFoundHttpException('Keine Bestellung vorhanden');
        }
        $isToken = false;
        // cancel from paypal
        if (\Input::get('token')) {
            $orderModel->paypal_token = Input::get('token');
            $orderModel->payment_status = 'Abort via Paypal';
            $orderModel->save();
        }
        if (!empty(\Input::post('NewPayment'))) {
            $paymentMethode = \Input::post('NewPayment');
            $orderModel->payment_status = 'New Payment via '.$paymentMethode;
            $orderModel->choosen_payment = $paymentMethode;
            $orderModel->save();
            switch ($orderModel->choosen_payment) {
                // todo: make config ;)
                case 'paypal':
                    $hvzPaypal = System::getContainer()->get('chuckki.contao_hvz_bundle.paypal');
                    $paymentObj = $hvzPaypal->generatePayment($orderModel);
                    $orderModel->paypal_paymentId = $paymentObj->getId();
                    $orderModel->paypal_approvalLink = $paymentObj->getApprovalLink();
                    $redirect = $GLOBALS['TL_CONFIG']['paypal_payment'];
                    break;
                case 'klarna':
                    $hvzKlarna = System::getContainer()->get('chuckki.contao_hvz_bundle.klarna');
                    $sessionObj = $hvzKlarna->getKlarnaNewOrderSession($orderModel);
                    $orderModel->klarna_session_id = $sessionObj['session_id'];
                    $orderModel->klarna_client_token = $sessionObj['client_token'];
                    $redirect = $GLOBALS['TL_CONFIG']['klarna_payment'];
                    break;
                default:
            }
            $orderModel->save();
            if (!empty($redirect)) {
                $this->redirectToFrontendPage($redirect);
            }
        }
    }
}
