<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

use Contao\Database;
use Contao\Frontend;
use Contao\PageModel;
use Contao\System;
use Date;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use mysqli;
use Psr\Log\LoggerInterface;

/**
 * Provide methods regarding HVZs.
 *
 * @author Dennis Esken
 */
class ModuleHvz extends \Frontend
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
    public function getSearchablePages($arrPages, $intRoot = 0, $blnIsSitemap = false)
    {
        $arrRoot = [];

        if ($intRoot > 0) {
            $arrRoot = $this->Database->getChildRecords($intRoot, 'tl_page');
        }

        $arrProcessed = [];
        $time = \Date::floorToMinute();

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
                if (!empty($arrRoot) && !\in_array($objHvz->jumpTo, $arrRoot, true)) {
                    continue;
                }

                // Get the URL of the jumpTo page
                if (!isset($arrProcessed[$objHvz->jumpTo])) {
                    $objParent = \PageModel::findWithDetails($objHvz->jumpTo);

                    // The target page does not exist
                    if (null === $objParent) {
                        continue;
                    }

                    // The target page has not been published (see #5520)
                    if (!$objParent->published || ('' !== $objParent->start && $objParent->start > $time) || ('' !== $objParent->stop && $objParent->stop <= ($time + 60))) {
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
                        $objParent->getAbsoluteUrl(\Config::get('useAutoItem') ? '/%s' : '/items/%s');
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

        $result_plz =
            $this->Database->prepare("SELECT kreis FROM tl_hvz where land like 'Deutschland' group by kreis order by kreis asc")
                ->execute();

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
     * @param $arrSubmitted
     * @param $arrLabels
     * @param $objForm
     * @param mixed $arrData
     * @param mixed $arrFiles
     *
     * @throws Exception
     */
    public function saveFormData(&$arrSubmitted, $arrData, $arrFiles, $arrLabels, $objForm)
    {
        $redirect = null;

        $this->logger = static::getContainer()->get('monolog.logger.contao');
        $this->logger->log(500, 'APICall backup', $arrSubmitted);

        // cleanup - check periodical for errors
        if (empty($arrSubmitted['type'])) {
            $this->logger->log(500, 'APICall miss - no type', $arrSubmitted);

            // set type
            switch ($arrSubmitted['Genehmigung']) {
                case 'Einfache HVZ mit Genehmigung':
                    $arrSubmitted['type'] = 1;
                    break;
                case 'Doppelte HVZ mit Genehmigung':
                    $arrSubmitted['type'] = 2;
                    break;
                case 'Einfache HVZ ohne Genehmigung':
                    $arrSubmitted['type'] = 3;
                    break;
                case 'Doppelte HVZ ohne Genehmigung':
                    $arrSubmitted['type'] = 4;
                    break;
            }

            // set bis
            $returnValue = preg_replace('/\\D/', '.', $arrSubmitted['vom'], -1);
            $curDate = explode('.', $returnValue);
            $berechnungsTage = \intval($arrSubmitted['wievieleTage'], 10) - 1;
            $arrSubmitted['bis'] = date('d.m.Y',
                mktime(0, 0, 0, \intval($curDate[1], 10), (\intval($curDate[0], 10) + $berechnungsTage),
                    \intval($curDate[2], 10)));
        }

        if (!empty($arrSubmitted['type'])) {
            $this->cleanUpSubmit($arrSubmitted);

            $arrSubmitted['orderNumber'] = $this->sendNewOrderToBackend($arrSubmitted);

            if (empty($arrSubmitted['orderNumber'])) {
                $arrSubmitted['orderNumber'] = '<i>wird nachgereicht</i>';
                $this->pushMe('HvbOnline2Backend -> Keine Auftragsnummer: '.$arrSubmitted['ts']);
            }

            // Create Session Data for ResponseView
            $formDatas = [];
            $formDatas['ort'] = $arrSubmitted['Ort'];
            $formDatas['auftragsNr'] = $arrSubmitted['orderNumber'];
            $formDatas['formAnrede'] = 'Sehr geehrte Frau '.$arrSubmitted['Name'];
            $arrSubmitted['apiGender'] = 'female';
            if ('Herr' === $arrSubmitted['Geschlecht']) {
                $arrSubmitted['apiGender'] = 'male';
                $formDatas['formAnrede'] = 'Sehr geehrter Herr '.$arrSubmitted['Name'];
            }
            System::getContainer()->get('session')->set('myform', $formDatas);

            if (!empty(\Input::post('Payment'))) {
                $paymentObj = HvzPaypal::generatePayment($arrSubmitted);
                $arrSubmitted['paypal_id'] = $paymentObj->getId();
                System::getContainer()->get('session')->set('ApprovalLink', ['link' => $paymentObj->getApprovalLink()]);

                // todo: make config ;)
                $redirect = 43;
            }

            $this->saveNewOrderToDatabase($arrSubmitted);

            if (!empty($redirect)) {
                $this->redirectToFrontendPage($redirect);
            }
            die;
        }
    }

    private function roundTo2($value)
    {
        return round(round($value, 3), 2);
    }

    private function cleanUpSubmit(&$arrSubmitted)
    {
        $this->calcValidPrices($arrSubmitted);

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
                $berechnungsTage = \intval($arrSubmitted['wievieleTage'], 10) - 1;
                $arrSubmitted['bis'] = date('d.m.Y',
                    mktime(0, 0, 0, \intval($curDate[1], 10), (\intval($curDate[0], 10) + $berechnungsTage),
                        \intval($curDate[2], 10)));
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
        $arrSubmitted['fullPrice'] = $price + ($arrSubmitted['wievieleTage'] - 1) * $objHvz->hvz_extra_tag;

        //************************************
        //  3. calc valid rabatt

        // get Rabatt and calc new
        $arrSubmitted['rabattCode'] = empty($arrSubmitted['gutscheincode']) ? '' : strtolower($arrSubmitted['gutscheincode']);
        $arrSubmitted['rabattValue'] = 0;
        $arrSubmitted['Rabatt'] = '';
        if (!empty($arrSubmitted['rabattCode'])) {
            $objRabatt = Database::getInstance()->prepare("SELECT
                  rabattProzent,
                  rabattCode
                FROM 
                  tl_hvz_rabatt
                WHERE
                  rabattCode=? AND 
                  (start<? OR start='') AND 
                  (stop>? OR stop='')")->limit(1)->execute($arrSubmitted['rabattCode'], time(), time());

            while ($objRabatt->next()) {
                $rabatt = $objRabatt->rabattProzent;
            }

            $arrSubmitted['Rabatt'] = $rabatt;

            $netto = $this->roundTo2($arrSubmitted['fullPrice'] / 119 * 100);
            $arrSubmitted['rabattValue'] = $this->roundTo2($netto / 100 * $rabatt);

            $zwischenSummer = $netto - $arrSubmitted['rabattValue'];
            $newMsst = $this->roundTo2($zwischenSummer * 0.19);

            $arr = [
                'brutto' => number_format($arrSubmitted['fullPrice'], 2),
                'netto' => $netto,
                'rabatt' => $rabatt,
                'rabattValue' => $arrSubmitted['rabattValue'],
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

    private function saveNewOrderToDatabase(&$arrSubmitted)
    {
        $set = [
            'tstamp' => time(),
            'user_id' => $arrSubmitted['customerId'],
            'type' => $arrSubmitted['submitType'],
            'hvz_type' => $arrSubmitted['type'],
            'hvz_type_name' => $arrSubmitted['Genehmigung'],
            'hvz_preis' => $arrSubmitted['Preis'],
            'hvzTagesPreis' => $arrSubmitted['hvzTagesPreis'],
            'hvz_gutscheincode' => $arrSubmitted['rabattCode'],
            'hvz_rabatt' => $arrSubmitted['rabattValue'],
            'hvz_ge_vorhanden' => substr($arrSubmitted['genehmigungVorhanden'], 0, 1),
            'hvz_ort' => $arrSubmitted['Ort'],
            'hvz_plz' => $arrSubmitted['PLZ'],
            'hvz_strasse_nr' => $arrSubmitted['Strasse'],
            'hvz_vom' => $arrSubmitted['vom'],
            'hvz_bis' => $arrSubmitted['bis'],
            'hvz_vom_time' => $arrSubmitted['vomUhrzeit'],
            'hvz_vom_bis' => $arrSubmitted['bisUhrzeit'],
            'hvz_anzahl_tage' => $arrSubmitted['wievieleTage'],
            'hvz_meter' => $arrSubmitted['Meter'],
            'hvz_fahrzeugart' => $arrSubmitted['Fahrzeug'],
            'hvz_zusatzinfos' => $arrSubmitted['Zusatzinformationen'],
            'hvz_grund' => $arrSubmitted['Grund'],
            're_anrede' => $arrSubmitted['Geschlecht'],
            're_umstid' => $arrSubmitted['umstid'],
            're_firma' => $arrSubmitted['firma'],
            're_name' => $arrSubmitted['Name'],
            're_vorname' => $arrSubmitted['Vorname'],
            're_strasse_nr' => $arrSubmitted['strasse_rechnung'],
            're_ort_plz' => $arrSubmitted['ort_rechnung'],
            're_email' => $arrSubmitted['email'],
            're_telefon' => $arrSubmitted['Telefon'],
            're_ip' => $arrSubmitted['client_ip'],
            're_agb_akzeptiert' => $arrSubmitted['agbakzeptiert'],
            'ts' => $arrSubmitted['ts'],
            'orderNumber' => $arrSubmitted['orderNumber'],
            'paypal_paymentId' => $arrSubmitted['paypal_id'],
        ];

        $objInsertStmt = $this->Database->prepare('INSERT INTO tl_hvz_orders '.' %s')
            ->set($set)->execute();
    }

    private function sendNewOrderToBackend(&$arrSubmitted)
    {
        $api_url = $GLOBALS['TL_CONFIG']['hvz_api'];
        $api_auth = $GLOBALS['TL_CONFIG']['hvz_api_auth'];

        if (!empty($api_url)) {
            $doubleSide = ($arrSubmitted['type'] % 2 === 0) ? true : false;

            // payload with missing value
            $data = [
                'uniqueRef' => $arrSubmitted['orderNumber'],
                'reason' => $arrSubmitted['Grund'],
                'plz' => (int) ($arrSubmitted['PLZ']),
                'city' => $arrSubmitted['Ort'],
                'price' => $arrSubmitted['fullPrice'].'',
                'streetName' => $arrSubmitted['Strasse'],
                'streetNumber' => '00',
                'dateFrom' => $arrSubmitted['vom'],
                'dateTo' => $arrSubmitted['bis'],
                'timeFrom' => $arrSubmitted['vomUhrzeit'].':00',
                'timeTo' => $arrSubmitted['bisUhrzeit'].':00',
                'email' => $arrSubmitted['email'],
                'length' => (int) ($arrSubmitted['Meter']),
                'isDoubleSided' => $doubleSide,
                'carrier' => $arrSubmitted['Vorname'].' '.$arrSubmitted['Name'],
                'additionalInfo' => $arrSubmitted['Zusatzinformationen'].'Genehmigung vorhanden:'
                                    .$arrSubmitted['genehmigungVorhanden'],
                'firma' => $arrSubmitted['firma'],
                'vorname' => $arrSubmitted['Vorname'],
                'name' => $arrSubmitted['Name'],
                'strasse' => $arrSubmitted['strasse_rechnung'],
                'ort' => $arrSubmitted['ort_rechnung'],
                'telefon' => $arrSubmitted['Telefon'],
                'needLicence' => $arrSubmitted['apiNeedLicence'],
                'gender' => $arrSubmitted['apiGender'],
                'customerId' => 'hvb_'.$arrSubmitted['customerId'],
            ];

            $pushMe = '';
            try {
                // Send order to API
                $client = new Client([
                    'base_uri' => $api_url,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'authorization' => 'Basic '.$api_auth,
                    ],
                ]);

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
                $this->pushMe($pushMe);
            }
        }

        return null;
    }

    private function pushMe($msg)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://pushme.projektorientiert.de/');
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'key' => 2,
            'msg' => $msg,
            'url' => 'https://www.halteverbot-beantragen.de/contao',
            'hash' => 'b7050601bca827a1c11264fb05f898e9',
        ]);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_exec($ch);
        curl_close($ch);
    }
}
