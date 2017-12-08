<?php

/**
 * Contao Open Source CMS
 *
 * @package Hvz
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


namespace Chuckki\ContaoHvzBundle;

use Patchwork\Utf8;


/**
 * Class ModuleHvzList
 *
 * @property string   $com_template
 * @property array    $hvz_categories
 *
 * @author Dennis Esken
*/
class ModuleHvzTeaser extends \Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_hvzteaser';

    /**
     * Target pages
     * @var array
     */
    protected $arrTargets = array();


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
			/** @var \BackendTemplate|object $objTemplate */
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['hvzteaser'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        $this->hvz_categories = \StringUtil::deserialize($this->hvz_categories);

        // Return if there are no categories
        if (!is_array($this->hvz_categories) || empty($this->hvz_categories)) {
            return '';
        }

        // Show the HVZ reader if an item has been selected
        if ($this->hvz_readerModule > 0 && (isset($_GET['items']) || ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item'])))) {
            return $this->getFrontendModule($this->hvz_readerModule, $this->strColumn);
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
		/** @var \Model\Collection|\HvzModel[]|\HvzModel $objHvz */
        $objHvz = \HvzModel::findPublishedByFeatured($this->hvz_categories);

        if ($objHvz === null) {
            $this->Template->hvz = array();

            return;
        }

        $arrHvz = array_fill_keys($this->hvz_categories, array());

        $countOdds = 0;

		$jumpTo = intval($objHvz->getRelated('pid')->jumpTo);
		$objTarget = \PageModel::findByPk($jumpTo);

        // Add HVZs
        while ($objHvz->next()) {
            $countOdds++;
            $arrTemp = $objHvz->row();

            $arrTemp['title'] = \StringUtil::specialchars($objHvz->question, true);
            $arrTemp['href'] = $objTarget->getAbsoluteUrl("/" . $objHvz->alias);

            $arrHvz[$objHvz->pid]['items'][] = $arrTemp;
            $arrHvz[$objHvz->pid]['headline'] = $objHvz->getRelated('pid')->headline;
        }

        $arrHvz = array_values(array_filter($arrHvz));

        $cat_count = 0;
        $cat_limit = count($arrHvz);

        // Add classes
        foreach ($arrHvz as $k => $v)
        {
            $count = 0;
            $limit = count($v['items']);

            for ($i = 0; $i < $limit; $i++)
            {
                $arrHvz[$k]['items'][$i]['class'] = trim(((++$count == 1) ? ' first' : '').(($count >= $limit) ? ' last' : '').((($count % 2) == 0) ? ' odd' : ' even'));
            }
            $arrHvz[$k]['class'] = trim(
                ((++$cat_count == 1) ? ' first' : '').(($cat_count >= $cat_limit) ? ' last' : '').((($cat_count % 2) == 0) ? ' odd' : ' even')
            );
        }

        // nur gerade Anzahl an Items
        if (count($v['items']) % 2 != 0) {
            unset($arrHvz[$k]['items'][$limit - 1]);
        }

        $this->Template->hvz = $arrHvz;
    }

}
