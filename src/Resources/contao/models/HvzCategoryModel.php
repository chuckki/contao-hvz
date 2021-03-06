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

use Contao\Model\Collection;

/**
 * Reads and writes Hvz categories.
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $title
 * @property string $headline
 * @property int    $jumpTo
 * @property string $lkz
 * @property bool   $allowComments
 * @property string $notify
 * @property string $sortOrder
 * @property int    $perPage
 * @property bool   $moderate
 * @property bool   $bbcode
 * @property bool   $requireLogin
 * @property bool   $disableCaptcha
 *
 * @method static HvzCategoryModel|null findById($id, $opt=array())
 * @method static HvzCategoryModel|null findByPk($id, $opt=array())
 * @method static HvzCategoryModel|null findByIdOrAlias($val, $opt=array())
 * @method static HvzCategoryModel|null findOneBy($col, $val, $opt=array())
 * @method static HvzCategoryModel|null findOneByTstamp($val, $opt=array())
 * @method static HvzCategoryModel|null findOneByTitle($val, $opt=array())
 * @method static HvzCategoryModel|null findOneByHeadline($val, $opt=array())
 * @method static HvzCategoryModel|null findOneByJumpTo($val, $opt=array())
 * @method static HvzCategoryModel|null findOneByAllowComments($val, $opt=array())
 * @method static HvzCategoryModel|null findOneByNotify($val, $opt=array())
 * @method static HvzCategoryModel|null findOneBySortOrder($val, $opt=array())
 * @method static HvzCategoryModel|null findOneByPerPage($val, $opt=array())
 * @method static HvzCategoryModel|null findOneByModerate($val, $opt=array())
 * @method static HvzCategoryModel|null findOneByBbcode($val, $opt=array())
 * @method static HvzCategoryModel|null findOneByRequireLogin($val, $opt=array())
 * @method static HvzCategoryModel|null findOneByDisableCaptcha($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findByTstamp($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findByTitle($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findByHeadline($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findByJumpTo($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findByAllowComments($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findByNotify($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findBySortOrder($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findByPerPage($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findByModerate($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findByBbcode($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findByRequireLogin($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findByDisableCaptcha($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findMultipleByIds($val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findBy($col, $val, $opt=array())
 * @method static Collection|HvzCategoryModel[]|HvzCategoryModel|null findAll($opt=array())
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
class HvzCategoryModel extends \Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_hvz_category';
}
