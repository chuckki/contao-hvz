<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

/**
 * Table tl_plz.
 */
$GLOBALS['TL_DCA']['tl_plz'] = [
    // Config
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'plzS' => 'index',
                'plz' => 'index',
                'ortid' => 'index',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'ortid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'plz' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'plzS' => [
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];
