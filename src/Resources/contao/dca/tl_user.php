<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

/**
 * Extend default palette.
 */
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('formp;', 'formp;{hvz_legend},hvzs,hvzp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('formp;', 'formp;{hvz_legend},hvzs,hvzp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);

/*
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['hvzs'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['hvzs'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_hvz_category.title',
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['hvzp'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['hvzp'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];
