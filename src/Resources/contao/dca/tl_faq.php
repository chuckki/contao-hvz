<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

$GLOBALS['TL_DCA']['tl_faq']['fields']['isFamus'] = [
            'label' => ['isFamus', 'isFamus'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "int(10) NOT NULL default '0'",
        ];
