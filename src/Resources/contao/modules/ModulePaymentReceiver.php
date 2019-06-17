<?php

namespace Chuckki\ContaoHvzBundle;

use Contao\Input;
use Contao\System;
use Haste\Frontend\AbstractFrontendModule;
use NotificationCenter\Model\Notification;
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
            $orderObj->klarna_order_id   = $data['order_id'];
            $orderObj->payment_status    = "Payed via Klarna";
            $orderObj->save();
            if ($data['fraud_status']) {
                PushMeMessage::pushMe('Klarna Payment was not successfull:' . $orderObj->klarna_order_id);
            }
        }
        $session->clear();
        $array = [
            'form_grussFormel'          => $orderObj->getGrussFormel(),
            'form_Geschlecht'           => $orderObj->re_anrede,
            'form_Vorname'              => $orderObj->re_vorname,
            'form_Name'                 => $orderObj->re_name,
            'form_orderNumber'          => $orderObj->orderNumber,
            'form_Genehmigung'          => $orderObj->hvz_type_name,
            'form_Strasse'              => $orderObj->hvz_strasse_nr,
            'form_PLZ'                  => $orderObj->hvz_plz,
            'form_Ort'                  => $orderObj->hvz_ort,
            'form_vom'                  => $orderObj->hvz_vom,
            'form_bis'                  => $orderObj->hvz_bis,
            'form_vomUhrzeit'           => $orderObj->hvz_vom_time,
            'form_bisUhrzeit'           => $orderObj->hvz_vom_bis,
            'form_Meter'                => $orderObj->hvz_meter,
            'form_Fahrzeug'             => $orderObj->hvz_fahrzeugart,
            'form_Zusatzinformationen'  => $orderObj->hvz_zusatzinfos,
            'form_email'                => $orderObj->re_email,
            'form_Telefon'              => $orderObj->re_telefon,
            'form_gesamtPreis'          => $orderObj->getBrutto(),
            'form_wievieleTage'         => $orderObj->hvz_anzahl_tage,
            'form_Grund'                => $orderObj->hvz_grund,
            'form_genehmigungVorhanden' => $orderObj->hvz_ge_vorhanden,
            'form_gutscheincode'        => $orderObj->hvz_gutscheincode,
            'form_EndPreis'             => $orderObj->getBrutto(),
            'form_fullNetto'            => $orderObj->getNetto(),
            'form_Rabatt'               => $orderObj->hvz_rabatt_percent,
            'form_PreisMwSt'            => $orderObj->getMwSt(),
            'form_firma'                => $orderObj->re_firma,
            'form_strasse_rechnung'     => $orderObj->re_strasse_nr,
            'form_ort_rechnung'         => $orderObj->re_ort_plz
        ];

        $objNotification = Notification::findByPk($GLOBALS['TL_CONFIG']['notifications']);
        if (null !== $objNotification) {
            $objNotification->send($array);
        }
        ModuleHvz::setSessionForThankYouPage($orderObj);

    }

}
