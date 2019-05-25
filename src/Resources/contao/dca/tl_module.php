<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

/**
 * Add palettes to tl_module.
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['hvzlist'] = '{title_legend},name,headline,type;{config_legend},hvz_categories,hvz_readerModule;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['hvzteaser'] = '{title_legend},name,headline,type;{config_legend},hvz_categories,hvz_readerModule;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['hvzlistdropdown'] = '{title_legend},name,headline,type;{config_legend},hvz_categories,hvz_readerModule;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['hvzreader'] = '{title_legend},name,headline,type;{config_legend},hvz_categories;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['hvzpage'] = '{title_legend},name,headline,type;{config_legend},hvz_categories;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

/*
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['hvz_categories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['hvz_categories'],
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'foreignKey' => 'tl_hvz_category.title',
    'eval' => ['multiple' => true, 'mandatory' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hvz_readerModule'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['hvz_readerModule'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => ['tl_module_hvz', 'getReaderModules'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval' => ['includeBlankOption' => true],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

/**
 * Class tl_module_hvz.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_module_hvz extends Backend
{
    /**
     * Get all HVZ reader modules and return them as array.
     *
     * @return array
     */
    public function getReaderModules()
    {
        $arrModules = [];
        $objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type='hvzreader' ORDER BY t.name, m.name");

        while ($objModules->next()) {
            $arrModules[$objModules->theme][$objModules->id] = $objModules->name.' (ID '.$objModules->id.')';
        }

        return $arrModules;
    }
}
