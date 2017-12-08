<?php

/**
 * Contao Open Source CMS
 *
 *
 * @package Hvz
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

//namespace Chuckki\ContaoHvzBundle;
namespace Contao;

/**
 * Reads and writes Hvzs
 *
 * @property integer $id
 * @property integer $pid
 * @property integer $sorting
 * @property integer $tstamp
 * @property string  $question
 * @property string  $alias
 *
 * @property string  $descOrt
 * @property string  $seitentitel
 * @property string  $hvzinfo
 * @property string  $hvzzusatz
 * @property string  $hvz_single
 * @property string  $hvz_double
 * @property string  $hvz_double_og
 * @property string  $hvz_single_og
 * @property string  $hvz_only
 * @property string  $hvz_extra_tag
 * @property string  $isFamus
 * @property string  $bundesland
 * @property string  $kreis
 * @property string  $land
 * @property string  $plz
 * @property string  $featured
 *
 * @property boolean $addImage
 * @property string  $singleSRC
 * @property string  $alt
 * @property string  $size
 * @property string  $imagemargin
 * @property string  $imageUrl
 * @property boolean $fullsize
 * @property string  $caption
 * @property string  $floating
 * @property boolean $addEnclosure
 * @property string  $enclosure
 * @property boolean $published
 *
 * @method static HvzModel|null findById($id, $opt=array())
 * @method static HvzModel|null findByPk($id, $opt=array())
 * @method static HvzModel|null findByIdOrAlias($val, $opt=array())
 * @method static HvzModel|null findOneBy($col, $val, $opt=array())
 * @method static HvzModel|null findOneByPid($val, $opt=array())
 * @method static HvzModel|null findOneBySorting($val, $opt=array())
 * @method static HvzModel|null findOneByTstamp($val, $opt=array())
 * @method static HvzModel|null findOneByQuestion($val, $opt=array())
 * @method static HvzModel|null findOneByAlias($val, $opt=array())
 * @method static HvzModel|null findOneByAuthor($val, $opt=array())
 * @method static HvzModel|null findOneByAnswer($val, $opt=array())
 * @method static HvzModel|null findOneByAddImage($val, $opt=array())
 * @method static HvzModel|null findOneBySingleSRC($val, $opt=array())
 * @method static HvzModel|null findOneByAlt($val, $opt=array())
 * @method static HvzModel|null findOneBySize($val, $opt=array())
 * @method static HvzModel|null findOneByImagemargin($val, $opt=array())
 * @method static HvzModel|null findOneByImageUrl($val, $opt=array())
 * @method static HvzModel|null findOneByFullsize($val, $opt=array())
 * @method static HvzModel|null findOneByCaption($val, $opt=array())
 * @method static HvzModel|null findOneByFloating($val, $opt=array())
 * @method static HvzModel|null findOneByAddEnclosure($val, $opt=array())
 * @method static HvzModel|null findOneByEnclosure($val, $opt=array())
 * @method static HvzModel|null findOneByNoComments($val, $opt=array())
 * @method static HvzModel|null findOneByPublished($val, $opt=array())
 *
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByPid($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findBySorting($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByTstamp($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByQuestion($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByAlias($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByAuthor($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByAnswer($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByAddImage($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findBySingleSRC($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByAlt($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findBySize($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByImagemargin($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByImageUrl($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByFullsize($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByCaption($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByFloating($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByAddEnclosure($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByEnclosure($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByNoComments($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findByPublished($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findMultipleByIds($val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findBy($col, $val, $opt=array())
 * @method static Model\Collection|HvzModel[]|HvzModel|null findAll($opt=array())
 *
 * @method static integer countById($id, $opt=array())
 * @method static integer countByPid($val, $opt=array())
 * @method static integer countBySorting($val, $opt=array())
 * @method static integer countByTstamp($val, $opt=array())
 * @method static integer countByQuestion($val, $opt=array())
 * @method static integer countByAlias($val, $opt=array())
 * @method static integer countByAuthor($val, $opt=array())
 * @method static integer countByAnswer($val, $opt=array())
 * @method static integer countByAddImage($val, $opt=array())
 * @method static integer countBySingleSRC($val, $opt=array())
 * @method static integer countByAlt($val, $opt=array())
 * @method static integer countBySize($val, $opt=array())
 * @method static integer countByImagemargin($val, $opt=array())
 * @method static integer countByImageUrl($val, $opt=array())
 * @method static integer countByFullsize($val, $opt=array())
 * @method static integer countByCaption($val, $opt=array())
 * @method static integer countByFloating($val, $opt=array())
 * @method static integer countByAddEnclosure($val, $opt=array())
 * @method static integer countByEnclosure($val, $opt=array())
 * @method static integer countByNoComments($val, $opt=array())
 * @method static integer countByPublished($val, $opt=array())
 *
 * @author Dennis Esken
 */
class HvzModel extends \Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_hvz';


	/**
	 * Find a published HVZ from one or more categories by its ID or alias
	 *
	 * @param mixed $varId      The numeric ID or alias name
	 * @param array $arrPids    An array of parent IDs
	 * @param array $arrOptions An optional options array
	 *
	 * @return HvzModel|null The model or null if there is no FAQ
	 */
	public static function findPublishedByParentAndIdOrAlias($varId, $arrPids, array $arrOptions=array())
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = array("($t.id=? OR $t.alias=?) AND pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");

		if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN)
		{
			$arrColumns[] = "$t.published='1'";
		}

		return static::findOneBy($arrColumns, array((is_numeric($varId) ? $varId : 0), $varId), $arrOptions);
	}


	/**
	 * Find all published HVZs by their parent IDs
	 *
	 * @param array $arrPids    An array of parent IDs
	 * @param array $arrOptions An optional options array
	 *
	 * @return Model\Collection|HvzModel[]|HvzModel|null A collection of models or null if there are no Hvzs
	 */
	public static function findPublishedByFeatured($arrPids, array $arrOptions=array())
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");

			$arrColumns[] = "$t.published=1 and $t.featured='1'";

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.alias";
		}

		return static::findBy($arrColumns, null, $arrOptions);
	}


	/**
	 * Find all published Hvzs by their parent ID
	 *
	 * @param int   $intPid     The parent ID
	 * @param array $arrOptions An optional options array
	 *
	 * @return Model\Collection|HvzModel[]|HvzModel|null A collection of models or null if there are no Hvzs
	 */
	public static function findPublishedByPid($intPid, array $arrOptions=array())
	{
		$t = static::$strTable;
		$arrColumns = array("$t.pid=?");

		if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN)
		{
			$arrColumns[] = "$t.published='1'";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.alias";
		}

		return static::findBy($arrColumns, $intPid, $arrOptions);
	}


	/**
	 * Find all published HVZs by their parent IDs
	 *
	 * @param array $arrPids    An array of HVZ category IDs
	 * @param array $arrOptions An optional options array
	 *
	 * @return Model\Collection|HvzModel[]|HvzModel|null A collection of models or null if there are no Hvzs
	 */
	public static function findPublishedByPids($arrPids, array $arrOptions=array())
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");

		if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN)
		{
			$arrColumns[] = "$t.published='1'";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.alias";
		}

		return static::findBy($arrColumns, null, $arrOptions);
	}
}
