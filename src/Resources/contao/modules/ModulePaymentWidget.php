<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

use Contao\CoreBundle\Exception\AccessDeniedException;
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

            // set order for payment session
            $this->import('FrontendUser', 'user');
            $showAllPayments  = false;
            $invoiceIsAllowed = false;
            if (FE_USER_LOGGED_IN) {
                $showAllPayments  = $this->user->paymentAllowed;
                $invoiceIsAllowed = $this->user->isAktive_invoice;
            }

            switch ($orderModel->choosen_payment) {
                // todo: make config ;)
                case 'paypal':
                    if (!($GLOBALS['TL_CONFIG']['isAktive_paypal'] || $showAllPayments)) {
                        throw new AccessDeniedException('Payment "paypal" not allowed');
                    }
                    $hvzPaypal = System::getContainer()->get('chuckki.contao_hvz_bundle.paypal');
                    $paymentObj = $hvzPaypal->generatePayment($orderModel);
                    $orderModel->paypal_paymentId = $paymentObj->getId();
                    $orderModel->paypal_approvalLink = $paymentObj->getApprovalLink();
                    $redirect = $GLOBALS['TL_CONFIG']['paypal_payment'];
                    break;
                case 'klarna':
                    if (!($GLOBALS['TL_CONFIG']['isAktive_klarna'] || $showAllPayments)) {
                        throw new AccessDeniedException('Payment "klarna" not allowed');
                    }
                    $hvzKlarna = System::getContainer()->get('chuckki.contao_hvz_bundle.klarna');
                    $sessionObj = $hvzKlarna->getKlarnaNewOrderSession($orderModel);
                    $orderModel->klarna_session_id = $sessionObj['session_id'];
                    $orderModel->klarna_client_token = $sessionObj['client_token'];
                    $redirect = $GLOBALS['TL_CONFIG']['klarna_payment'];
                    break;
                case 'invoice':
                    if ($GLOBALS['TL_CONFIG']['invoice_for_all'] || $invoiceIsAllowed || $showAllPayments || (!$GLOBALS['TL_CONFIG']['isAktive_klarna'] && !$GLOBALS['TL_CONFIG']['isAktive_paypal'])){
                        $orderModel->payment_status = 'Rechnung';
                        $redirect = $GLOBALS['TL_CONFIG']['finish_order'];
                    }else{
                        throw new AccessDeniedException('Payment "invoice" not allowed');
                    }
                    // no break
                    break;
                default:
                    throw new AccessDeniedException('No Payment');
            }
            $orderModel->save();
            if (!empty($redirect)) {
                $this->redirectToFrontendPage($redirect);
            }
        }

        $this->Template->isUser = false;
        $this->Template->isKlarnaPaymentActive = $GLOBALS['TL_CONFIG']['isAktive_klarna'];
        $this->Template->isPaypalPaymentActive = $GLOBALS['TL_CONFIG']['isAktive_paypal'];
        $this->Template->hasOtherPaymentsThanInvoice = ($GLOBALS['TL_CONFIG']['isAktive_klarna'] or $GLOBALS['TL_CONFIG']['isAktive_paypal']);
        $this->Template->isInvoicePaymentActive = false;

        // import FrontEndUser Data
        $this->import('FrontendUser', 'user');
        if (FE_USER_LOGGED_IN) {

            $this->Template->isInvoicePaymentActive = $this->user->isAktive_invoice;
            $this->Template->hasOtherPaymentsThanInvoice = $this->user->paymentAllowed
                                                           || ($GLOBALS['TL_CONFIG']['isAktive_klarna']
                                                               or $GLOBALS['TL_CONFIG']['isAktive_paypal']);
            $this->Template->isKlarnaPaymentActive       =
                $this->user->paymentAllowed || $GLOBALS['TL_CONFIG']['isAktive_klarna'];
            $this->Template->isPaypalPaymentActive       =
                $this->user->paymentAllowed || $GLOBALS['TL_CONFIG']['isAktive_paypal'];
            $this->Template->isUser = true;
        }

    }
}
