<?php
/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */
$GLOBALS['TL_DCA']['tl_member']['palettes']['default'] = str_replace(
    'gender',
    //'gender,gutschein,zusatzinfo',
    'gender,gutschein,zusatzinfo,token,umstid;{pay_option},paymentAllowed,isAktive_invoice',
    $GLOBALS['TL_DCA']['tl_member']['palettes']['default']
);


$GLOBALS['TL_DCA']['tl_member']['fields']['street']['label'] = ['Strasse und Hausnummer', ''];
$GLOBALS['TL_DCA']['tl_member']['fields']['zusatzinfo']      = [
    'label'     => ['Zusatzinfo', 'Dies wird immer angezeigt'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
    'sql'       => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_member']['fields']['paymentAllowed'] = [
    'exclude'   => true,
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => ['feEditable' => false, 'feViewable' => false, 'feGroup' => 'personal', 'tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];


$GLOBALS['TL_DCA']['tl_member']['fields']['isAktive_invoice'] = [
    'label'     => ['Rechnungen erlaubt','Wenn aktiv - Bezahlen auf Rechnung aktiv'],
    'exclude'   => false,
    'filter'    => true,
    'inputType' => 'checkbox',
    'eval'      => ['feEditable' => false, 'feViewable' => false, 'feGroup' => 'personal', 'tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];


$GLOBALS['TL_DCA']['tl_member']['fields']['gutschein'] = [
    'label'     => ['Gutschein', ''],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
    'sql'       => "varchar(20) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member']['fields']['umstid']    = [
    'label'     => ['USt-IdNr.', ''],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
    'sql'       => "varchar(20) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_member']['fields']['gender']    = [
    'label'     => ['Anrede'],
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['Herr', 'Frau'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => [
        'includeBlankOption' => true,
        'feEditable'         => true,
        'feViewable'         => true,
        'feGroup'            => 'personal',
        'tl_class'           => 'w50',
    ],
    'sql'       => "varchar(32) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_member']['fields']['token']    = [
    'label'     => ['Token', 'Authentifizierungstoken für die Anbindung an Partner Software. z.B. NeoMetrik'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['feEditable' => true, 'feViewable' => true, 'readonly' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'],
    'sql'       => "varchar(32) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_member']['fields']['password']['save_callback'][] = array('cu_tl_member', 'generateToken');


$GLOBALS['TL_DCA']['tl_member']['config']['onsubmit_callback'][] = array('cu_tl_member', 'generateToken');

class cu_tl_member extends tl_member
{
    public function generateToken($strPassword, $user)
	{
		if (!$user instanceof FrontendUser)
		{
			return;
		}

		$token = bin2hex(openssl_random_pseudo_bytes(16));

		$this->Database->prepare("UPDATE tl_member SET token=? WHERE id=?")
					   ->execute($token, $user->id);

        return $strPassword;
	}
}
