<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

//namespace Chuckki\ContaoHvzBundle;

namespace Chuckki\ContaoHvzBundle;

use Contao\Environment;
use Contao\Model;
use Contao\PageModel;
use Model\Collection;

/**
 * Reads and writes Hvz categories.
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $title
 * @property string $headline
 * @property int    $jumpTo
 * @property bool   $allowComments
 * @property string $notify
 * @property string $sortOrder
 * @property int    $perPage
 * @property bool   $moderate
 * @property bool   $bbcode
 * @property bool   $requireLogin
 * @property bool   $disableCaptcha
 * @property string $hvz_solo_price
 * @property string $hvz_extra_tag
 * @property string $hvz_rabatt_percent
 * @property string $hvz_preis
 * @property string $hvzTagesPreis
 * @property string $hvz_gutscheincode
 * @property string $hvz_rabatt
 * @property string $user_id
 * @property string $type
 * @property string $hvz_type
 * @property string $hvz_type_name
 * @property string $hvz_ge_vorhanden
 * @property string $hvz_ort
 * @property string $hvz_land
 * @property int $hvz_plz
 * @property string $hvz_strasse_nr
 * @property string $hvz_vom
 * @property string $hvz_bis
 * @property string $hvz_vom_time
 * @property string $hvz_vom_bis
 * @property string $hvz_anzahl_tage
 * @property string $hvz_meter
 * @property string $hvz_fahrzeugart
 * @property string $hvz_zusatzinfos
 * @property string $hvz_grund
 * @property string $re_anrede
 * @property string $re_umstid
 * @property string $re_firma
 * @property string $re_name
 * @property string $re_vorname
 * @property string $re_strasse_nr
 * @property string $re_ort_plz
 * @property string $re_email
 * @property string $re_bemail
 * @property string $re_telefon
 * @property string $re_ip
 * @property string $re_agb_akzeptiert
 * @property string $ts
 * @property int    $hvz_id
 * @property string $hash
 * @property string $orderNumber
 * @property string $choosen_payment
 * @property string $payment_status
 * @property string $klarna_session_id
 * @property string $klarna_client_token
 * @property string $klarna_auth_token
 * @property string $klarna_order_id
 * @property string $paypal_token
 * @property string $paypal_paymentId
 * @property string $paypal_approvalLink
 *
 * @method static HvzOrderModel|null findById($id, $opt = array())
 * @method static HvzOrderModel|null findByPk($id, $opt = array())
 * @method static HvzOrderModel|null findByIdOrAlias($val, $opt = array())
 * @method static HvzOrderModel|null findOneBy($col, $val, $opt = array())
 * @method static HvzOrderModel|null findOneByTstamp($val, $opt = array())
 * @method static HvzOrderModel|null findOneByTitle($val, $opt = array())
 * @method static HvzOrderModel|null findOneByHeadline($val, $opt = array())
 * @method static HvzOrderModel|null findOneByJumpTo($val, $opt = array())
 * @method static HvzOrderModel|null findOneByAllowComments($val, $opt = array())
 * @method static HvzOrderModel|null findOneByNotify($val, $opt = array())
 * @method static HvzOrderModel|null findOneBySortOrder($val, $opt = array())
 * @method static HvzOrderModel|null findOneByPerPage($val, $opt = array())
 * @method static HvzOrderModel|null findOneByModerate($val, $opt = array())
 * @method static HvzOrderModel|null findOneByBbcode($val, $opt = array())
 * @method static HvzOrderModel|null findOneByRequireLogin($val, $opt = array())
 * @method static HvzOrderModel|null findOneByDisableCaptcha($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findByTstamp($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findByTitle($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findByHeadline($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findByJumpTo($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findByAllowComments($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findByNotify($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findBySortOrder($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findByPerPage($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findByModerate($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findByBbcode($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findByRequireLogin($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findByDisableCaptcha($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findMultipleByIds($val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findBy($col, $val, $opt = array())
 * @method static Collection|HvzOrderModel[]|HvzOrderModel|null findAll($opt = array())
 * @method static integer countById($id, $opt = array())
 * @method static integer countByTstamp($val, $opt = array())
 * @method static integer countByTitle($val, $opt = array())
 * @method static integer countByHeadline($val, $opt = array())
 * @method static integer countByJumpTo($val, $opt = array())
 * @method static integer countByAllowComments($val, $opt = array())
 * @method static integer countByNotify($val, $opt = array())
 * @method static integer countBySortOrder($val, $opt = array())
 * @method static integer countByPerPage($val, $opt = array())
 * @method static integer countByModerate($val, $opt = array())
 * @method static integer countByBbcode($val, $opt = array())
 * @method static integer countByRequireLogin($val, $opt = array())
 * @method static integer countByDisableCaptcha($val, $opt = array())
 *
 * @author Dennis Esken
 */
class HvzOrderModel extends Model
{
    public const MWST_DECIMAL_GERMANY = 1.19;
    public const MWST_INTL_GERMANY    = 19;

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_hvz_orders';

    public function getOrderDescription(): string
    {
        return $this->hvz_type_name.' in '.$this->hvz_ort;
    }

    public function getErrorOrderPage(): string
    {
        $id = $GLOBALS['TL_CONFIG']['edit_order'];
        $page = PageModel::findById($id);

        return $page->getAbsoluteUrl();
    }

    public function getFinishOrderPage(): string
    {
        $id = $GLOBALS['TL_CONFIG']['finish_order'];
        $page = PageModel::findById($id);

        return $page->getAbsoluteUrl();
    }

    public function generateHash(): void
    {
        // geiler Scheiss...
        $breakCounter = 100;
        do {
            $hash = bin2hex(random_bytes(32));
            $orderModel = self::findBy('hash', $hash);
            if ($breakCounter-- < 0) {
                $hash = 'fehler_'.$hash;
                break;
            }
        } while ($orderModel);
        $this->hash = $hash;
    }

    public function getBrutto()
    {
        $fullBrutto = $this->getFullBrutto() * (1 - ($this->hvz_rabatt_percent / 100));

        return number_format(round($fullBrutto, 2), 2);
    }

    public function getRabatt()
    {
        $rabatt = $this->getFullBrutto() - $this->getBrutto();

        return number_format(round($rabatt, 2), 2);
    }

    public function getMwSt()
    {
        $mwst = $this->getBrutto() / (self::MWST_INTL_GERMANY + 100) * self::MWST_INTL_GERMANY;

        return number_format(round($mwst, 2), 2);
    }

    public function getNetto()
    {
        return number_format(round($this->getBrutto() - $this->getMwSt(), 2), 2);
    }

    public function getAbsoluteUrl(): string
    {
        $hvzModel = HvzModel::findById($this->hvz_id);
        $env = Environment::get('base');
        return $env.'halteverbot/'.$hvzModel->alias.'.html';
    }

    public function getGrussFormel(): string
    {
        $anrede = $this->re_anrede.' '.$this->re_name;
        $tagesStunde = (int) (date('H'));
        $grussFormel = 'Sehr geehrte Damen und Herren,';
        if (!empty($this->re_name)) {
            $grussFormel = 'Guten Tag';
            if ($tagesStunde < 10) {
                $grussFormel = 'Guten Morgen';
            }
            if ($tagesStunde >= 18) {
                $grussFormel = 'Guten Abend';
            }
            $grussFormel .= ' '.trim($anrede).',';
        }

        return $grussFormel;
    }

    private function getFullBrutto()
    {
        return number_format($this->hvz_solo_price + (($this->hvz_anzahl_tage - 1) * $this->hvz_extra_tag), 2);
    }
}
