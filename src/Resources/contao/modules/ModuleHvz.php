<?php
/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

use Chuckki\ContaoRabattBundle\Model\HvzRabattModel;
use Contao\Config;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Form;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Date;
use DateTime;
use Exception;
use Frontend;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use mysqli;

/**
 * Provide methods regarding HVZs.
 *
 * @author Dennis Esken
 */
class ModuleHvz extends Frontend
{
    public function getSearchablePages(array $arrPages, int $intRoot = 0, bool $blnIsSitemap = false): array
    {
        $arrRoot = [];
        if ($intRoot > 0)
        {
            $arrRoot = $this->Database->getChildRecords($intRoot, 'tl_page');
        }

        $arrProcessed = [];
        $time = Date::floorToMinute();
        // Get all categories
        $objHvzCategories = HvzCategoryModel::findAll();

        // Walk through each category
        if (null !== $objHvzCategories)
        {
            while ($objHvzCategories->next())
            {
                // Skip HVZs outside the root nodes
                if (!empty($arrRoot) && !in_array($objHvzCategories->jumpTo, $arrRoot, true)) {
                    continue;
                }
                // Get the URL of the jumpTo page
                if (!isset($arrProcessed[$objHvzCategories->jumpTo])) {
                    $objParent = PageModel::findWithDetails($objHvzCategories->jumpTo);
                    // Generate the URL
                    $arrProcessed[$objHvzCategories->jumpTo] =
                        $objParent->getAbsoluteUrl( '/%s/%s');
                }
                $strUrl = $arrProcessed[$objHvzCategories->jumpTo];
                // Get the items
                $objItems = HvzModel::findByPid($objHvzCategories->id, ['order' => 'isFamus DESC, sorting']);
                if (null !== $objItems) {
                    while ($objItems->next()) {
                        $arrPages[] = sprintf($strUrl, $objHvzCategories->lkz, ($objItems->alias ?: $objItems->id));
                    }
                }
            }
        }
        // add sites from BL and Kreis
        $objParent = PageModel::findWithDetails(29);
        $bLand_alias = [
            'baden-wuerttemberg',
            'bayern',
            'berlin',
            'brandenburg',
            'bremen',
            'hamburg',
            'hessen',
            'mecklenburg-vorpommern',
            'niedersachsen',
            'nordrhein-westfalen',
            'rheinland-Pfalz',
            'saarland',
            'sachsen',
            'sachsen-anhalt',
            'schleswig-holstein',
            'thueringen',
        ];
        foreach ($bLand_alias as $site) {
            $arrPages[] = $objParent->getAbsoluteUrl('/bundesland/'.$site);
        }
        $this->import('Database');
        $result_plz = $this->Database->prepare(
            "SELECT kreis FROM tl_hvz where land like 'Deutschland' group by kreis order by kreis asc"
        )->execute();
        while ($result_plz->next()) {
            $stdKreis = StringUtil::standardize($result_plz->kreis);
            $arrPages[] = $objParent->getAbsoluteUrl('/kreis/'.$stdKreis);
        }

        return $arrPages;
    }

    public function mergeFamus(): void
    {
        // todo: mergeFamus Hvz´s ! tag 1.1
        $passw = $GLOBALS['TL_CONFIG']['dbPass'];
        $host = $GLOBALS['TL_CONFIG']['dbHost'];
        $user = $GLOBALS['TL_CONFIG']['dbUser'];
        $database = $GLOBALS['TL_CONFIG']['dbDatabase'];
        $db = new mysqli($host, $user, $passw, $database);
        if ($db->connect_errno > 0) {
            die('Unable to connect to database ['.$db->connect_error.']');
        }
        $db->set_charset('utf8');
        $sql = 'select isFamus as myid , count(isFamus) as anzahl from tl_hvz group by isFamus order by isFamus';
        $result = $db->query($sql);
        $counter = 1;
        $log = '';
        $allSql = '';
        while ($group = $result->fetch_assoc()) {
            $sql = 'update tl_hvz set isFamus='.$counter++.' where isFamus='.$group['myid'].'; ';
            $allSql .= $sql;
            $log .= $sql.'<br>';
        }
        $result2 = $db->multi_query($allSql);
        if ($result2) {
            --$counter;
        } else {
            $counter = 'Error with merging';
        }
        $db->close();
        $myString = date('Ymd H:i').'::merged::'.$counter."\n";
        $file = TL_ROOT.'/merge-log.txt';
        file_put_contents($file, $myString, FILE_APPEND);
    }

    /**
     * @param       $arrSubmitted
     * @param       $arrLabels
     * @param Form  $objForm
     * @param mixed $arrData
     * @param mixed $arrFiles
     *
     * @throws Exception
     */
    public function saveFormData(&$arrSubmitted, &$arrData, $arrFiles, $arrLabels, Form $objForm): void
    {
        $redirect = null;

        // order cancel by customer
        if($arrData["formID"] === "cancel"){
            $orderObj = HvzOrderModel::findOneBy('hash', System::getContainer()->get('session')->get('orderToken'));
            if($orderObj){
                $orderObj->payment_status = "Cancelled by website user";
                $orderObj->save();
                $this->updatePaymentStatus($orderObj);
                $this->updateOrderStatus($orderObj, "Cancelled");
            }
        }

        // order process
        if (!empty($arrSubmitted['type'])) {
            $orderModel              = $this->createOrderAndSaveToDatabase($arrSubmitted);
            $orderModel->orderNumber = $this->sendNewOrderToBackend($orderModel);
            $orderModel->save();
            // set order for payment session
            $this->import('FrontendUser', 'user');
            $showAllPayments  = false;
            $invoiceIsAllowed = false;
            if (FE_USER_LOGGED_IN) {
                $showAllPayments  = $this->user->paymentAllowed;
                $invoiceIsAllowed = $this->user->isAktive_invoice;
            }
            System::getContainer()->get('session')->set('orderToken', $orderModel->hash);
            switch ($orderModel->choosen_payment) {
                // todo: make config ;)
                case 'paypal':
                    if (!($GLOBALS['TL_CONFIG']['isAktive_paypal'] || $showAllPayments)) {
                        throw new AccessDeniedException('Payment "paypal" not allowed');
                    }
                    $hvzPaypal                       = System::getContainer()->get('chuckki.contao_hvz_bundle.paypal');
                    $paymentObj                      = $hvzPaypal->generatePayment($orderModel);
                    $orderModel->paypal_paymentId    = $paymentObj->getId();
                    $orderModel->paypal_approvalLink = $paymentObj->getApprovalLink();
                    $orderModel->payment_status = 'Paypal in Progress';
                    $redirect = $GLOBALS['TL_CONFIG']['paypal_payment'];
                    break;
                case 'klarna':
                    if (!($GLOBALS['TL_CONFIG']['isAktive_klarna'] || $showAllPayments)) {
                        throw new AccessDeniedException('Payment "klarna" not allowed');
                    }
                    $hvzKlarna                       = System::getContainer()->get('chuckki.contao_hvz_bundle.klarna');
                    $sessionObj                      = $hvzKlarna->getKlarnaNewOrderSession($orderModel);
                    $orderModel->klarna_session_id   = $sessionObj['session_id'];
                    $orderModel->klarna_client_token = $sessionObj['client_token'];
                    $orderModel->payment_status = 'Klarna in Progress';
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
            $arrData['nc_notification'] = null;
            self::setSessionForThankYouPage($orderModel);
            if (!empty($redirect)) {
                $objForm->getModel()->jumpTo = $redirect;
            }
        }
    }

    public static function setSessionForThankYouPage(HvzOrderModel $orderModel): void
    {
        // Create Session Data for ResponseView
        $formDatas = [];
        $formDatas['ort'] = $orderModel->hvz_ort;
        $formDatas['auftragsNr'] = $orderModel->orderNumber;
        $formDatas['formAnrede'] = 'Sehr geehrte Frau '.$orderModel->re_name;
        if ('Herr' === $orderModel->re_anrede) {
            $formDatas['formAnrede'] = 'Sehr geehrter Herr '.$orderModel->re_name;
        }
        System::getContainer()->get('session')->set('myform', $formDatas);
    }

    private function roundTo2($value)
    {
        return round(round($value, 3), 2);
    }

    private function cleanUpSubmit(&$arrSubmitted): void
    {
        $this->calcValidPrices($arrSubmitted);
        $arrSubmitted['choosen_payment'] = \Input::post('Payment');
        //  ************************************
        //  2. prepare Data
        if ('on' === $arrSubmitted['genehmigung_vorhanden']) {
            $arrSubmitted['genehmigungVorhanden'] = 'ja';
        } else {
            $arrSubmitted['genehmigungVorhanden'] = 'nein';
        }
        // add client_ip
        if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arrSubmitted['client_ip'] = $_SERVER['REMOTE_ADDR'];
        } else {
            $arrSubmitted['client_ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // add frontend userId
        $arrSubmitted['customerId'] = 'www';
        $this->import('FrontendUser', 'user');
        if (FE_USER_LOGGED_IN) {
            $arrSubmitted['customerId'] = $this->user->id;
            $arrSubmitted['billingmail'] = $this->user->billing_mail;
        }
        // add $umstid
        if (empty($arrSubmitted['umstid'])) {
            $arrSubmitted['umstid'] = '';
        }
        // create OrderNumber temp OrderNumber
        $date = new DateTime();
        $arrSubmitted['ts'] = $date->format('Y-m-d H:i:s');
        $arrSubmitted['orderNumber'] = dechex(time());
        // decide for order or request and add missing requires for insert
        $arrSubmitted['submitType'] = 0; // anfrage
        if (0 !== $arrSubmitted['Preis']) {
            $arrSubmitted['submitType'] = 1; // bestellung
        } else {
            $arrSubmitted['StrasseRechnung'] = '';
            $arrSubmitted['OrtRechnung'] = '';
            $arrSubmitted['Preis'] = 0;
            $arrSubmitted['hvzTagesPreis'] = 0;
            $arrSubmitted['agbakzeptiert'] = '';
            try {
                $returnValue = preg_replace('/\\D/', '.', $arrSubmitted['vom'], -1);
                $curDate = explode('.', $returnValue);
                $berechnungsTage = (int)$arrSubmitted['wievieleTage'] - 1;
                $arrSubmitted['bis'] = date(
                    'd.m.Y', mktime(0, 0, 0, (int)$curDate[1], ((int)$curDate[0] + $berechnungsTage), (int)$curDate[2])
                );
            } catch (Exception $e) {
                $arrSubmitted['bis'] = '-';
            }
        }
    }

    private function calcValidPrices(&$arrSubmitted): void
    {
        $this->import('Database');
        //************************************
        //  1. check valid Values - lookup DB for Price and Calc
        $objHvz = HvzModel::findById($arrSubmitted['hvzID']);
        $arrSubmitted['hvzTagesPreis'] = $objHvz->hvz_extra_tag;
        //************************************
        //  2. calc valid price
        $arrSubmitted['apiNeedLicence'] = false;
        $price = 0;
        switch ($arrSubmitted['type']) {
            case 1:
                $price = $objHvz->hvz_single;
                $arrSubmitted['apiNeedLicence'] = true;
                break;
            case 2:
                $price = $objHvz->hvz_double;
                $arrSubmitted['apiNeedLicence'] = true;
                break;
            case 3:
                $price = $objHvz->hvz_single_og;
                break;
            case 4:
                $price = $objHvz->hvz_double_og;
                break;
            default:
                PushMeMessage::pushMe('HVZ-Type ist ungültig: '.$arrSubmitted['type'] . 'ts: '. date('d.m.Y H:i'), 'ModuleHvz');
        }
        $arrSubmitted['hvz_solo_price'] = $price;
        $arrSubmitted['fullPrice'] = $price + ($arrSubmitted['wievieleTage'] - 1) * $objHvz->hvz_extra_tag;
        //************************************
        //  3. calc valid rabatt
        // get Rabatt and calc new
        $arrSubmitted['rabattCode'] =
            empty($arrSubmitted['gutscheincode']) ? '' : strtolower($arrSubmitted['gutscheincode']);
        $arrSubmitted['rabattValue'] = 0;
        $arrSubmitted['Rabatt'] = 0;
        if (!empty($arrSubmitted['rabattCode'])) {
            $rabatt = HvzRabattModel::findRabattOnCode($arrSubmitted['rabattCode']);
            $arrSubmitted['Rabatt'] = ($rabatt) ? (int) $rabatt : 0;
            $netto = $this->roundTo2($arrSubmitted['fullPrice'] / (100 + HvzOrderModel::MWST_INTL_GERMANY) * 100);
            $arrSubmitted['rabattValue'] = $this->roundTo2($netto / 100 * $rabatt);
            $zwischenSummer              = $netto - $arrSubmitted['rabattValue'];
            $newMsst                     = $this->roundTo2($zwischenSummer * (HvzOrderModel::MWST_INTL_GERMANY / 100));
            $arr                         = [
                'brutto'        => number_format($arrSubmitted['fullPrice'], 2),
                'netto'         => $netto,
                'rabatt'        => $rabatt,
                'rabattValue'   => $arrSubmitted['rabattValue'],
                'zwischenSumme' => $zwischenSummer,
                'newMst' => $newMsst,
                'fullNew' => $newMsst + $zwischenSummer,
            ];
            foreach ($arr as $key => $item) {
                $arr[$key] = number_format($item, 2);
            }
            $arrSubmitted['fullPrice'] = $newMsst + $zwischenSummer;
        }
        $arrSubmitted['Preis'] = $arrSubmitted['fullPrice'];
        $arrSubmitted['fullNetto'] = number_format(($arrSubmitted['fullPrice'] / (100 + HvzOrderModel::MWST_INTL_GERMANY) * 100), 2);
        $arrSubmitted['preisDetaisl'] = $arr;
    }

    private function createOrderAndSaveToDatabase(&$arrSubmitted): HvzOrderModel
    {
        $hvzObj = HvzModel::findById($arrSubmitted['hvzID']);

        $this->cleanUpSubmit($arrSubmitted);
        if(!empty($arrSubmitted['billingmail'])){
            $arrSubmitted['Zusatzinformationen'] .= 'Rechnung an: '.$arrSubmitted['billingmail'];
        }

        $hvzOrder                      = new HvzOrderModel();
        $hvzOrder->tstamp              = time();
        $hvzOrder->hvz_solo_price      = $arrSubmitted['hvz_solo_price'];
        $hvzOrder->hvz_extra_tag       = $arrSubmitted['hvzTagesPreis'];
        $hvzOrder->hvz_rabatt_percent  = $arrSubmitted['Rabatt'];
        $hvzOrder->hvz_preis           = $arrSubmitted['Preis'];
        $hvzOrder->hvzTagesPreis       = $arrSubmitted['hvzTagesPreis'];
        $hvzOrder->hvz_gutscheincode   = $arrSubmitted['rabattCode'];
        $hvzOrder->hvz_rabatt          = $arrSubmitted['rabattValue'];
        $hvzOrder->user_id             = $arrSubmitted['customerId'];
        $hvzOrder->type                = $arrSubmitted['submitType'];
        $hvzOrder->hvz_type            = $arrSubmitted['type'];
        $hvzOrder->hvz_type_name       = $arrSubmitted['Genehmigung'];
        $hvzOrder->hvz_ge_vorhanden    = substr($arrSubmitted['genehmigungVorhanden'], 0, 1);
        $hvzOrder->hvz_ort             = $arrSubmitted['Ort'];
        $hvzOrder->hvz_land            = $hvzObj->land;
        $hvzOrder->hvz_plz             = $arrSubmitted['PLZ'];
        $hvzOrder->hvz_strasse_nr      = $arrSubmitted['Strasse'];
        $hvzOrder->hvz_vom             = $arrSubmitted['vom'];
        $hvzOrder->hvz_bis             = $arrSubmitted['bis'];
        $hvzOrder->hvz_vom_time        = $arrSubmitted['vomUhrzeit'];
        $hvzOrder->hvz_vom_bis         = $arrSubmitted['bisUhrzeit'];
        $hvzOrder->hvz_anzahl_tage     = $arrSubmitted['wievieleTage'];
        $hvzOrder->hvz_meter           = $arrSubmitted['Meter'];
        $hvzOrder->hvz_fahrzeugart     = $arrSubmitted['Fahrzeug'];
        $hvzOrder->hvz_zusatzinfos     = $arrSubmitted['Zusatzinformationen'];
        $hvzOrder->hvz_grund           = $arrSubmitted['Grund'];
        $hvzOrder->re_anrede           = $arrSubmitted['Geschlecht'];
        $hvzOrder->re_umstid           = $arrSubmitted['umstid'];
        $hvzOrder->re_firma            = $arrSubmitted['firma'];
        $hvzOrder->re_name             = $arrSubmitted['Name'];
        $hvzOrder->re_vorname          = $arrSubmitted['Vorname'];
        $hvzOrder->re_strasse_nr       = $arrSubmitted['strasse_rechnung'];
        $hvzOrder->re_ort_plz          = $arrSubmitted['ort_rechnung'];
        $hvzOrder->re_email            = $arrSubmitted['email'];
        $hvzOrder->re_telefon          = $arrSubmitted['Telefon'];
        $hvzOrder->re_ip               = $arrSubmitted['client_ip'];
        $hvzOrder->re_agb_akzeptiert   = $arrSubmitted['agbakzeptiert'];
        $hvzOrder->ts                  = $arrSubmitted['ts'];
        $hvzOrder->orderNumber         = $arrSubmitted['orderNumber'];
        $hvzOrder->paypal_paymentId    = $arrSubmitted['paypal_id'];
        $hvzOrder->paypal_approvalLink = '';
        $hvzOrder->klarna_session_id   = '';
        $hvzOrder->klarna_client_token = '';
        $hvzOrder->klarna_auth_token   = '';
        $hvzOrder->hvz_id              = $arrSubmitted['hvzID'];
        $hvzOrder->choosen_payment     = \Input::post('Payment');
        $hvzOrder->klarna_order_id     = '';
        $hvzOrder->re_bemail           = $arrSubmitted['billingmail'];
        $hvzOrder->generateHash();
        $hvzOrder->save();

        return $hvzOrder;
    }

    private function sendNewOrderToBackend(HvzOrderModel $orderModel): string
    {
        $api_url = $GLOBALS['TL_CONFIG']['hvz_api'];
        $api_auth = $GLOBALS['TL_CONFIG']['hvz_api_auth'];

        if (!empty($api_url)) {
            // payload with missing value
            $data   = [
                'uniqueRef'      => $orderModel->orderNumber,
                'reason'         => $orderModel->hvz_grund,
                'plz'            => (int)$orderModel->hvz_plz,
                'city'           => $orderModel->hvz_ort,
                'price'          => $orderModel->getBrutto(),
                'streetName'     => $orderModel->hvz_strasse_nr,
                'streetNumber'   => '00',
                'dateFrom'       => $orderModel->hvz_vom,
                'dateTo'         => $orderModel->hvz_bis,
                'timeFrom'       => $orderModel->hvz_vom_time.':00',
                'timeTo'         => $orderModel->hvz_vom_bis.':00',
                'email'          => $orderModel->re_email,
                'length'         => (int)$orderModel->hvz_meter,
                'isDoubleSided'  => $orderModel->hvz_type % 2 === 0,
                'carrier'        => $orderModel->re_vorname . ' '.$orderModel->re_name,
                'additionalInfo' => $orderModel->hvz_zusatzinfos,
                'firma'          => $orderModel->re_firma,
                'vorname'        => $orderModel->re_vorname,
                'name'           => $orderModel->re_name,
                'strasse'        => $orderModel->re_strasse_nr,
                'ort'            => $orderModel->re_ort_plz,
                'country'        => $orderModel->hvz_land,
                'telefon'        => $orderModel->re_telefon,
                'needLicence'    => $orderModel->hvz_type <= 2,
                'gender'         => $orderModel->re_anrede === 'Herr' ? 'male' : 'female',
                'customerId'     => 'hvb_'. $orderModel->user_id,
                'paymentStatus'  => $orderModel->choosen_payment .' in Progress',
            ];
            $pushMe = '';
            try {
                // Send order to API
                $client = new Client(
                    [
                        'base_uri' => $api_url,
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'authorization' => 'Basic '.$api_auth,
                        ],
                    ]
                );
                $response = $client->post('/v1/order/new', ['body' => json_encode($data)]);
                if (201 !== $response->getStatusCode()) {
                    $pushMe = 'Hvb2Api:'.$data['uniqueRef']."\n StatusCode:".$response->getStatusCode()
                              ."\nAPICall not found in ModuleHvz.php";
                } else {
                    $responseArray = json_decode($response->getBody(), true);
                    if (!empty($responseArray['data']['uniqueRef'])) {
                        return $responseArray['data']['uniqueRef'];
                    }
                }
            } catch (RequestException $e) {
                $pushMe = 'Hvb2Api:'.$data['uniqueRef']."\n APICall Catch:".$e->getMessage();
            }
            if ('' !== $pushMe) {
                PushMeMessage::pushMe($pushMe, 'ModuleHvz');
            }
        }
        PushMeMessage::pushMe(
            'HvbOnline2Backend -> Keine Auftragsnummer: '.$orderModel->orderNumber.'_0 :: '
            .$orderModel->ts,
            'ModuleHvz'
        );

        return $orderModel->orderNumber.'_0';
    }

    private function updatePaymentStatus(HvzOrderModel $hvzOrderModel)
    {
        $api_url = $GLOBALS['TL_CONFIG']['hvz_api'];
        $api_auth = $GLOBALS['TL_CONFIG']['hvz_api_auth'];
        if (!empty($api_url)) {
            $doubleSide = ($arrSubmitted['type'] % 2 === 0) ? true : false;
            // payload with missing value
            $data = [
                'uniqueRef' => $hvzOrderModel->orderNumber,
                'paymentStatus' => $hvzOrderModel->payment_status,
            ];
            $pushMe = '';
            try {
                // Send order to API
                $client = new Client(
                    [
                        'base_uri' => $api_url,
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'authorization' => 'Basic '.$api_auth,
                        ],
                    ]
                );
                $response = $client->put('/v1/order/updatePaymentStatus', ['body' => json_encode($data)]);

                if (200 !== $response->getStatusCode()) {
                    $pushMe = 'Hvb2Api:'.$data['uniqueRef']."\n StatusCode:".$response->getStatusCode()
                              ."\n updatePaymentStatus failed";
                } else {
                    $responseArray = json_decode($response->getBody(), true);
                    if (!empty($responseArray['data']['uniqueRef'])) {
                        return $responseArray['data']['uniqueRef'];
                    }
                }
            } catch (RequestException $e) {
                $pushMe = 'Hvb2Api:'.$data['uniqueRef']."\n APICall updatePaymentStatus:".$e->getMessage();
            }
            if ('' !== $pushMe) {
                PushMeMessage::pushMe($pushMe, 'ModulePaymentReceiver');
            }
        }
    }

    private function updateOrderStatus(HvzOrderModel $hvzOrderModel, string $status)
    {
        $api_url = $GLOBALS['TL_CONFIG']['hvz_api'];
        $api_auth = $GLOBALS['TL_CONFIG']['hvz_api_auth'];
        if (!empty($api_url)) {
            // payload with missing value
            $pushMe = '';
            try {
                // Send order to API
                $client = new Client(
                    [
                        'base_uri' => $api_url,
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'authorization' => 'Basic '.$api_auth,
                        ],
                    ]
                );
                $response = $client->put('/v1/order/' . $hvzOrderModel->orderNumber . '/status/'. $status);

                if (200 !== $response->getStatusCode()) {
                    $pushMe = 'Hvb2Api:'.$data['uniqueRef']."\n StatusCode:".$response->getStatusCode()
                              ."\n updateOrderStatus failed";
                } else {
                    $responseArray = json_decode($response->getBody(), true);
                    if (!empty($responseArray['data']['uniqueRef'])) {
                        return $responseArray['data'];
                    }
                }
            } catch (RequestException $e) {
                $pushMe = 'Hvb2Api:'.$data['uniqueRef']."\n APICall updateOrderStatus:".$e->getMessage();
            }
            if ('' !== $pushMe) {
                PushMeMessage::pushMe($pushMe, 'ModuleHvz');
            }
        }
    }

}
