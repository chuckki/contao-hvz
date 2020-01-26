<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

/**
 * Class ModuleHvzList.
 *
 * @property string $com_template
 * @property array  $hvz_categories
 *
 * @author Dennis Esken
 */
class ModuleHvzListDropDown extends \Module
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_hvzlistdropdown';

    /**
     * Target pages.
     *
     * @var array
     */
    protected $arrTargets = [];

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### HvzListDropDown ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        if (TL_MODE === 'FE') {
            $GLOBALS['TL_JAVASCRIPT'][] = '/bundles/chuckkicontaohvz/js/typeahead.bundle.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = '/bundles/chuckkicontaohvz/js/searchlist.min.js|static';
            $GLOBALS['TL_JAVASCRIPT'][] = '/bundles/chuckkicontaohvz/js/src/searchlist.js|static';
        }

        $this->hvz_categories = \StringUtil::deserialize($this->hvz_categories);

        // Return if there are no categories
        if (!\is_array($this->hvz_categories) || empty($this->hvz_categories)) {
            return '';
        }

        // Show the HVZ reader if an item has been selected
        if ($this->hvz_readerModule > 0 && (isset($_GET['items']) || ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item'])))) {
            return $this->getFrontendModule($this->hvz_readerModule, $this->strColumn);
        }

        return parent::generate();
    }

    /**
     * Generate the module.
     */
    protected function compile()
    {
        /* @var \PageModel $objPage */
        global $objPage;
        $this->Template->suche = \Input::get('suche');
    }

    /**
     * Create links and remember pages that have been processed.
     *
     * @param object
     * @param mixed $objHvz
     *
     * @return string
     */
    protected function generateHvzLink($objHvz)
    {
        $jumpTo = (int) ($objHvz->getRelated('pid')->jumpTo);

        // Get the URL from the jumpTo page of the category
        if (!isset($this->arrTargets[$jumpTo])) {
            $this->arrTargets[$jumpTo] = ampersand(\Environment::get('request'), true);

            if ($jumpTo > 0) {
                $objTarget = \PageModel::findByPk($jumpTo);

                if (null !== $objTarget) {
                    $this->arrTargets[$jumpTo] = ampersand(
                        $this->generateFrontendUrl(
                            $objTarget->row(),
                            (($GLOBALS['TL_CONFIG']['useAutoItem'] && !$GLOBALS['TL_CONFIG']['disableAlias']) ? '/%s' : '/items/%s')
                        )
                    );
                }
            }
        }

        return sprintf(
            $this->arrTargets[$jumpTo],
            ((!$GLOBALS['TL_CONFIG']['disableAlias'] && '' !== $objHvz->alias) ? $objHvz->alias : $objHvz->id)
        );
    }
}
