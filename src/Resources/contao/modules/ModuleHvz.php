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
use Contao\Database;
use Contao\Form;
use Contao\PageModel;
use Contao\System;
use Date;
use DateTime;
use Exception;
use Frontend;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use mysqli;
use Psr\Log\LoggerInterface;

/**
 * Provide methods regarding HVZs.
 *
 * @author Dennis Esken
 */
class ModuleHvz extends Frontend
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Add HVZs to the indexer.
     *
     * @param array $arrPages
     * @param int   $intRoot
     * @param bool  $blnIsSitemap
     *
     * @return array
     */
    public function getSearchablePages($arrPages, $intRoot = 0, $blnIsSitemap = false): array
    {
        $arrRoot = [];
        if ($intRoot > 0) {
            $arrRoot = $this->Database->getChildRecords($intRoot, 'tl_page');
        }
        $arrProcessed = [];
        $time = Date::floorToMinute();
        // Get all categories
        $objHvz = HvzCategoryModel::findAll();
        // Walk through each category
        if (null !== $objHvz) {
            while ($objHvz->next()) {
                // Skip HVZs without target page
                if (!$objHvz->jumpTo) {
                    continue;
                }
                // Skip HVZs outside the root nodes
                if (!empty($arrRoot) && !in_array($objHvz->jumpTo, $arrRoot, true)) {
                    continue;
                }
                // Get the URL of the jumpTo page
                if (!isset($arrProcessed[$objHvz->jumpTo])) {
                    $objParent = PageModel::findWithDetails($objHvz->jumpTo);
                    // The target page does not exist
                    if (null === $objParent) {
                        continue;
                    }
                    // The target page has not been published (see #5520)
                    if (!$objParent->published || ('' !== $objParent->start && $objParent->start > $time)
                        || ('' !== $objParent->stop
                            && $objParent->stop <= ($time + 60))) {
                        continue;
                    }
                    if ($blnIsSitemap) {
                        // The target page is protected (see #8416)
                        if ($objParent->protected) {
                            continue;
                        }
                        // The target page is exempt from the sitemap (see #6418)
                        if ('map_never' === $objParent->sitemap) {
                            continue;
                        }
                    }
                    // Generate the URL
                    $arrProcessed[$objHvz->jumpTo] =
                        $objParent->getAbsoluteUrl(Config::get('useAutoItem') ? '/%s' : '/items/%s');
                }
                $strUrl = $arrProcessed[$objHvz->jumpTo];
                // Get the items
                $objItems = HvzModel::findByPid($objHvz->id, ['order' => 'isFamus DESC, sorting']);
                if (null !== $objItems) {
                    while ($objItems->next()) {
                        $arrPages[] = sprintf($strUrl, ($objItems->alias ?: $objItems->id));
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
            $stdKreis = standardize($result_plz->kreis);
            $arrPages[] = $objParent->getAbsoluteUrl('/kreis/'.$stdKreis);
        }

        return $arrPages;
    }

    public function mergeFamus()
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
    public function saveFormData(&$arrSubmitted, $arrData, $arrFiles, $arrLabels, Form $objForm): void
    {
        $redirect = null;

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
                        throw new AccessDeniedException('Payment not allowed');
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
                        throw new AccessDeniedException('Payment not allowed');
                    }
                    $hvzKlarna                       = System::getContainer()->get('chuckki.contao_hvz_bundle.klarna');
                    $sessionObj                      = $hvzKlarna->getKlarnaNewOrderSession($orderModel);
                    $orderModel->klarna_session_id   = $sessionObj['session_id'];
                    $orderModel->klarna_client_token = $sessionObj['client_token'];
                    $orderModel->payment_status = 'Klarna in Progress';
                    $redirect = $GLOBALS['TL_CONFIG']['klarna_payment'];
                    break;
                case 'invoice':
                    if (!($invoiceIsAllowed || $showAllPayments)) {
                        throw new AccessDeniedException('Payment not allowed');
                    }
                    $orderModel->payment_status = 'Rechnung';
                    // no break
                    break;
                default:
                    throw new AccessDeniedException('No Payment');
            }
            $orderModel->save();
            self::setSessionForThankYouPage($orderModel);
            if (!empty($redirect)) {
                $objForm->getModel()->jumpTo = $redirect;
            }
        }
    }

    public static function setSessionForThankYouPage(HvzOrderModel $orderModel)
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

    private function calcValidPrices(&$arrSubmitted)
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
                $this->logger->log(500, 'HVZ-Type ist ungültig: '.$arrSubmitted['type'], $arrSubmitted);
        }
        $arrSubmitted['hvz_solo_price'] = $price;
        $arrSubmitted['fullPrice'] = $price + ($arrSubmitted['wievieleTage'] - 1) * $objHvz->hvz_extra_tag;
        //************************************
        //  3. calc valid rabatt
        // get Rabatt and calc new
        $arrSubmitted['rabattCode'] =
            empty($arrSubmitted['gutscheincode']) ? '' : strtolower($arrSubmitted['gutscheincode']);
        $arrSubmitted['rabattValue'] = 0;
        $arrSubmitted['Rabatt'] = '';
        if (!empty($arrSubmitted['rabattCode'])) {
            $objRabatt = Database::getInstance()->prepare(
                "SELECT
                  rabattProzent,
                  rabattCode
                FROM 
                  tl_hvz_rabatt
                WHERE
                  rabattCode=? AND 
                  (start<? OR start='') AND 
                  (stop>? OR stop='')"
            )->limit(1)->execute($arrSubmitted['rabattCode'], time(), time());
            while ($objRabatt->next()) {
                $rabatt = $objRabatt->rabattProzent;
            }
            $arrSubmitted['Rabatt']      = ($rabatt) ? (int)$rabatt : 0;
            $netto                       = $this->roundTo2($arrSubmitted['fullPrice'] / 119 * 100);
            $arrSubmitted['rabattValue'] = $this->roundTo2($netto / 100 * $rabatt);
            $zwischenSummer              = $netto - $arrSubmitted['rabattValue'];
            $newMsst                     = $this->roundTo2($zwischenSummer * 0.19);
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
        $arrSubmitted['fullNetto'] = number_format(($arrSubmitted['fullPrice'] / 119 * 100), 2);
        $arrSubmitted['preisDetaisl'] = $arr;
    }

    private function createOrderAndSaveToDatabase(&$arrSubmitted): HvzOrderModel
    {
        $this->cleanUpSubmit($arrSubmitted);
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
        $hvzOrder->generateHash();
        $hvzOrder->save();

        return $hvzOrder;
    }

    private function sendNewOrderToBackend(HvzOrderModel $orderModel): string
    {
        $api_url = $GLOBALS['TL_CONFIG']['hvz_api'];
        $api_auth = $GLOBALS['TL_CONFIG']['hvz_api_auth'];
        $arrSubmitted['apiGender'] = 'female';
        if ('Herr' === $arrSubmitted['Geschlecht']) {
            $arrSubmitted['apiGender'] = 'male';
        }
        if (!empty($api_url)) {
            $doubleSide = ($arrSubmitted['type'] % 2 === 0) ? true : false;
            // payload with missing value
            $data   = [
                'uniqueRef'      => $orderModel->orderNumber,
                'reason'         => $orderModel->hvz_grund,
                'plz'            => $orderModel->hvz_plz,
                'city'           => $orderModel->hvz_ort,
                'price'          => $orderModel->getBrutto(),
                'streetName'     => $orderModel->hvz_strasse_nr,
                'streetNumber'   => '00',
                'dateFrom'       => $orderModel->hvz_vom,
                'dateTo'         => $orderModel->hvz_bis,
                'timeFrom'       => $orderModel->hvz_vom_time.':00',
                'timeTo'         => $orderModel->hvz_vom_bis.':00',
                'email'          => $orderModel->re_email,
                'length'         => $orderModel->hvz_meter,
                'isDoubleSided'  => $orderModel->hvz_type % 2 === 0,
                'carrier'        => $orderModel->re_vorname . ' '.$orderModel->re_name,
                'additionalInfo' => $orderModel->hvz_zusatzinfos,
                'firma'          => $orderModel->re_firma,
                'vorname'        => $orderModel->re_vorname,
                'name'           => $orderModel->re_name,
                'strasse'        => $orderModel->re_strasse_nr,
                'ort'            => $orderModel->re_ort_plz,
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
                    $this->logger->log(500, 'APICall fehlgeschlagen', $data);
                    $pushMe = 'Hvb2Api:'.$data['uniqueRef']."\n StatusCode:".$response->getStatusCode()
                              ."\nAPICall not found in ModuleHvz.php";
                } else {
                    $responseArray = json_decode($response->getBody(), true);
                    if (!empty($responseArray['data']['uniqueRef'])) {
                        return $responseArray['data']['uniqueRef'];
                    }
                }
            } catch (RequestException $e) {
                $this->logger->log(500, 'APICall not found', $data);
                $pushMe = 'Hvb2Api:'.$data['uniqueRef']."\n APICall Catch:".$e->getMessage();
            }
            if ('' !== $pushMe) {
                PushMeMessage::pushMe($pushMe);
            }
        }
        PushMeMessage::pushMe(
            'HvbOnline2Backend -> Keine Auftragsnummer: '.$orderModel->orderNumber.'_0 :: '
            .$orderModel->ts
        );

        return $orderModel->orderNumber.'_0';
    }
}
