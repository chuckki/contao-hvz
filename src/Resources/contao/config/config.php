<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

use Chuckki\ContaoHvzBundle\ModuleFaqReader;
use Chuckki\ContaoHvzBundle\ModuleHvz;
use Chuckki\ContaoHvzBundle\ModuleHvzReplaceInsertTag;
use Chuckki\ContaoHvzBundle\ModuleHvzList;
use Chuckki\ContaoHvzBundle\ModuleHvzTeaser;
use Chuckki\ContaoHvzBundle\ModuleHvzListDropDown;
use Chuckki\ContaoHvzBundle\ModuleHvzReader;
use Chuckki\ContaoHvzBundle\ModuleHvzResult;
use Chuckki\ContaoHvzBundle\ModuleHvzPaypal;
use Chuckki\ContaoHvzBundle\ModuleHvzKlarna;
use Chuckki\ContaoHvzBundle\ModulePaymentWidget;
use Chuckki\ContaoHvzBundle\ModulePaymentReceiver;

/**
 * Add back end modules.
 */
array_insert($GLOBALS['BE_MOD'], 0, [
    'Hvz' => [
        'hvz' => [
            'tables' => ['tl_hvz_category', 'tl_hvz'],
            'icon' => 'bundles/chuckkicontaohvz/icon.gif',
        ],
    ],
]);

// Load icon in Contao 4.2 backend
if ('BE' === TL_MODE) {
    $GLOBALS['TL_CSS'][] = 'bundles/chuckkicontaohvz/backend.css|static';
}

$GLOBALS['TL_MODELS']['tl_hvz'] = \Chuckki\ContaoHvzBundle\HvzModel::class;
$GLOBALS['TL_MODELS']['tl_plz'] = \Chuckki\ContaoHvzBundle\HvzPlzModel::class;
$GLOBALS['TL_MODELS']['tl_hvz_orders'] = \Chuckki\ContaoHvzBundle\HvzOrderModel::class;
$GLOBALS['TL_MODELS']['tl_hvz_category'] = \Chuckki\ContaoHvzBundle\HvzCategoryModel::class;

/*
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 3, [
    'hvz' => [
        'hvzlist' => ModuleHvzList::class,
        'hvzteaser' => ModuleHvzTeaser::class,
        'hvzlistdropdown' => ModuleHvzListDropDown::class,
        'hvzreader' => ModuleHvzReader::class,
        'hvzresult' => ModuleHvzResult::class,
        'hvzpaypal' => ModuleHvzPaypal::class,
        'hvzklarna' => ModuleHvzKlarna::class,
        'hvzpayment' => ModulePaymentWidget::class,
        'hvzpaymentreceiver' => ModulePaymentReceiver::class,
    ],
]);

$GLOBALS['FE_MOD']['faq']['faqreader'] = ModuleFaqReader::class;

/*
 * Register hooks
 */

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ModuleHvzReplaceInsertTag::class, 'replaceCuInsertTags'];
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = [ModuleHvz::class, 'getSearchablePages'];

if(!empty($GLOBALS['TL_HOOKS']['processFormData'])){
    array_unshift($GLOBALS['TL_HOOKS']['processFormData'], [ModuleHvz::class, 'saveFormData']);
}else{
    $GLOBALS['TL_HOOKS']['processFormData'][] = [ModuleHvz::class, 'saveFormData'];
}

// contao/config.php

$GLOBALS['TL_CRON']['monthly'][] = [ModuleHvz::class, 'mergeFamus'];

/*
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'hvzs';
$GLOBALS['TL_PERMISSIONS'][] = 'hvzp';
