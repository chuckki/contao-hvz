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
 *
 * @method static HvzOrderModel|null findById($id, $opt=array())
 * @method static HvzOrderModel|null findByPk($id, $opt=array())
 * @method static HvzOrderModel|null findByIdOrAlias($val, $opt=array())
 * @method static HvzOrderModel|null findOneBy($col, $val, $opt=array())
 * @method static HvzOrderModel|null findOneByTstamp($val, $opt=array())
 * @method static HvzOrderModel|null findOneByTitle($val, $opt=array())
 * @method static HvzOrderModel|null findOneByHeadline($val, $opt=array())
 * @method static HvzOrderModel|null findOneByJumpTo($val, $opt=array())
 * @method static HvzOrderModel|null findOneByAllowComments($val, $opt=array())
 * @method static HvzOrderModel|null findOneByNotify($val, $opt=array())
 * @method static HvzOrderModel|null findOneBySortOrder($val, $opt=array())
 * @method static HvzOrderModel|null findOneByPerPage($val, $opt=array())
 * @method static HvzOrderModel|null findOneByModerate($val, $opt=array())
 * @method static HvzOrderModel|null findOneByBbcode($val, $opt=array())
 * @method static HvzOrderModel|null findOneByRequireLogin($val, $opt=array())
 * @method static HvzOrderModel|null findOneByDisableCaptcha($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findByTstamp($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findByTitle($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findByHeadline($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findByJumpTo($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findByAllowComments($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findByNotify($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findBySortOrder($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findByPerPage($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findByModerate($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findByBbcode($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findByRequireLogin($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findByDisableCaptcha($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findMultipleByIds($val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findBy($col, $val, $opt=array())
 * @method static Model\Collection|HvzOrderModel[]|HvzOrderModel|null findAll($opt=array())
 * @method static integer countById($id, $opt=array())
 * @method static integer countByTstamp($val, $opt=array())
 * @method static integer countByTitle($val, $opt=array())
 * @method static integer countByHeadline($val, $opt=array())
 * @method static integer countByJumpTo($val, $opt=array())
 * @method static integer countByAllowComments($val, $opt=array())
 * @method static integer countByNotify($val, $opt=array())
 * @method static integer countBySortOrder($val, $opt=array())
 * @method static integer countByPerPage($val, $opt=array())
 * @method static integer countByModerate($val, $opt=array())
 * @method static integer countByBbcode($val, $opt=array())
 * @method static integer countByRequireLogin($val, $opt=array())
 * @method static integer countByDisableCaptcha($val, $opt=array())
 *
 * @author Dennis Esken
 */
class HvzOrderModel extends \Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_hvz_orders';
}
