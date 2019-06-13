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
$GLOBALS['TL_DCA']['tl_hvz_orders'] = [
    // Config
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'hash' => 'key'
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
        'type' => [
            'sql' => 'smallint(5) unsigned NOT NULL',
        ],
        'hvz_type' => [
            'sql' => "smallint(5) unsigned NOT NULL default '0'",
        ],
        'hvz_type_name' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'hvz_preis' => [
            'sql' => "smallint(5) unsigned NOT NULL default '0'",
        ],
        'hvzTagesPreis' => [
            'sql' => "smallint(6) unsigned NOT NULL default '0'",
        ],
        'hvz_ge_vorhanden' => [
            'sql' => "char(1) NOT NULL default ''",
        ],
        'hvz_ort' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'hvz_plz' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'hvz_strasse_nr' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'hvz_vom' => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'hvz_bis' => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'hvz_vom_time' => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'hvz_vom_bis' => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'hvz_anzahl_tage' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'user_id' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'hvz_meter' => [
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'hvz_fahrzeugart' => [
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'hvz_zusatzinfos' => [
            'sql' => 'text NOT NULL',
        ],
        'hvz_gutscheincode' => [
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'hvz_rabatt' => [
            'sql' => "int(11) unsigned NOT NULL default '0'",
        ],
        'hvz_grund' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        're_anrede' => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        're_firma' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        're_umstid' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        're_name' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        're_vorname' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        're_strasse_nr' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        're_ort_plz' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        're_email' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        're_telefon' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        're_agb_akzeptiert' => [
            'sql' => "char(1) NOT NULL default ''",
        ],
        're_ip' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'ts' => [
            'sql' => 'timestamp NOT NULL default CURRENT_TIMESTAMP',
        ],
        'orderNumber' => [
            'sql' => "varchar(255) NOT NULL default '0'",
        ],

        'paypal_paymentId' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'paypal_token' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'paypal_PayerID' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'paypal_approvalLink' => [
            'sql' => "text NOT NULL default ''",
        ],
        'klarna_client_token' => [
            'sql' => "text NOT NULL default ''",
        ],
        'klarna_auth_token' => [
            'sql' => "text NOT NULL default ''",
        ],
        'klarna_session_id' => [
            'sql' => "text NOT NULL default ''",
        ],
        'klarna_order_id' => [
            'sql' => "text NOT NULL default ''",
        ],
        'choosen_payment' => [
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'hvz_id' =>[
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'hvz_solo_price' =>[
            'sql' => "decimal(8,2) DEFAULT NULL" ,
        ],
        'hvz_extra_tag' =>[
            'sql' => "decimal(8,2) DEFAULT NULL" ,
        ],
        'hvz_rabatt_percent' =>[
            'sql' => "TINYINT NOT NULL default '0'",
        ],
        'hash' => [
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'payment_status' => [
            'sql' => "varchar(128) NOT NULL default ''",
        ],
    ],
];
