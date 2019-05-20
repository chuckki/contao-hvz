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
$GLOBALS['TL_DCA']['tl_hvz_request'] = [
    // Config
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'anfrage' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'msg' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'hits' => [
            'sql' => "int(10) NOT NULL default '0'",
        ],
        're_ip' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'agent' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'isbot' => [
            'sql' => "int(10) NOT NULL default '0'",
        ],
        'user_id' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'ts' => [
            'sql' => 'timestamp NOT NULL default CURRENT_TIMESTAMP',
        ],
    ],
];
