<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Hvz
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['hvzlist']   = '{title_legend},name,headline,type;{config_legend},hvz_categories,hvz_readerModule;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['hvzteaser']   = '{title_legend},name,headline,type;{config_legend},hvz_categories,hvz_readerModule;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['hvzlistdropdown']   = '{title_legend},name,headline,type;{config_legend},hvz_categories,hvz_readerModule;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['hvzreader'] = '{title_legend},name,headline,type;{config_legend},hvz_categories;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['hvzpage']   = '{title_legend},name,headline,type;{config_legend},hvz_categories;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['hvz_categories'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['hvz_categories'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'foreignKey'              => 'tl_hvz_category.title',
	'eval'                    => array('multiple'=>true, 'mandatory'=>true),
	'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['hvz_readerModule'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['hvz_readerModule'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_module_hvz', 'getReaderModules'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
	'eval'                    => array('includeBlankOption'=>true),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);


/**
 * Class tl_module_hvz
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @package    Hvz
 */
class tl_module_hvz extends Backend
{

	/**
	 * Get all HVZ reader modules and return them as array
	 * @return array
	 */
	public function getReaderModules()
	{
		$arrModules = array();
		$objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type='hvzreader' ORDER BY t.name, m.name");

		while ($objModules->next())
		{
			$arrModules[$objModules->theme][$objModules->id] = $objModules->name . ' (ID ' . $objModules->id . ')';
		}

		return $arrModules;
	}
}
