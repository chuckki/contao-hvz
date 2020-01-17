<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

/**
 * Load tl_content language file.
 */

use Contao\StringUtil;

System::loadLanguageFile('tl_content');

/*
 * Table tl_hvz
 */
$GLOBALS['TL_DCA']['tl_hvz'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => 'tl_hvz_category',
        'enableVersioning' => true,
        'onload_callback' => [
            ['tl_hvz', 'checkPermission'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'question' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['question'],
            'flag' => 1,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['question', 'alias'],
            'format' => '%s (%s)',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_hvz']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_hvz']['copy'],
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.gif',
            ],
            'cut' => [
                'label' => &$GLOBALS['TL_LANG']['tl_hvz']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_hvz']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_hvz']['toggle'],
                'icon' => 'visible.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_hvz', 'toggleIcon'],
            ],
            'feature' => [
                'label' => &$GLOBALS['TL_LANG']['tl_hvz']['feature'],
                'icon' => 'featured.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleFeatured(this,%s)"',
                'button_callback' => ['tl_hvz', 'iconFeatured'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_hvz']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],

    // Select
    'select' => [
        'buttons_callback' => [
            ['tl_hvz', 'addAliasButton'],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['addImage', 'addEnclosure'],
        'default' => '{title_legend},question,alias,bundesland,kreis,land,lk,descOrt,seitentitel;{hvzinfo_preise},hvz_single,hvz_double,hvz_single_og,hvz_double_og,hvz_extra_tag,hvz_only;{hvzzusatz},hvzzusatz;{hvzinfo_legend},hvzinfo;{image_legend},addImage;{enclosure_legend:hide},addEnclosure;{publish_legend},published,featured',
    ],
    // Subpalettes
    'subpalettes' => [
        'addImage' => 'singleSRC,alt,size,imagemargin,imageUrl,fullsize,caption,floating',
        'addEnclosure' => 'enclosure',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_hvz_category.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'sorting' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['sorting'],
            'sorting' => true,
            'flag' => 2,
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'question' => [
            'label' => ['Ort', 'Ort für die HVZ'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) COLLATE utf8_bin NOT NULL default ''",
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_hvz']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alias', 'unique' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'save_callback' => [
                ['tl_hvz', 'generateAlias'],
            ],
            'sql' => "varchar(128) COLLATE utf8_bin NOT NULL default ''",
        ],
        'descOrt' => [
            'label' => ['Seitenbeschreibung (max 160 Zeichen)', 'WICHTIG 160 Zeichen'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'seitentitel' => [
            'label' => ['Seitentitel', 'Wenn leer - wird nur der Ort genommen'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'hvzinfo' => [
            'label' => &$GLOBALS['TL_LANG']['tl_hvz']['hvzinfo'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'helpwizard' => true],
            'explanation' => 'insertTags',
            'sql' => 'text NULL',
        ],
        'hvzzusatz' => [
            'label' => ['Zusatzinformationen für HVZ', 'Wieviel Tage vorher Antrag, Extra Tage, etc.'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'helpwizard' => true],
            'explanation' => 'insertTags',
            'sql' => 'text NULL',
        ],
        'hvz_single' => [
            'label' => ['Einfache HVZ', 'Aufstellen einer Halteverbotszone bis ca. 15m Länge incl. Aufstellfrist, Anlieferung, Auf- und Abbau, Abholung und Genehmigung, Gültigkeitsdauer bis zu zwei Tagen.'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => 'int(10) unsigned NULL',
        ],
        'hvz_double' => [
            'label' => ['doppelseitige HVZ', 'Aufstellen einer beidseitigen Halteverbotszone (in schmalen Straßen, zur Erhaltung der Durchfahrtsbreite, das ergibt sich vor Ort oder wird angeordnet.) incl. Aufstellfrist, Anlieferung, Auf- und Abbau, Abholung und Genehmigung, Gültigkeitsdauer bis zu zwei Tagen.'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => 'int(10) unsigned NULL',
        ],
        'hvz_single_og' => [
            'label' => ['Einfache HVZ ohne Genehmigung', 'Aufstellen einer Halteverbotszone bis ca. 15m Länge incl. Aufstellfrist, Anlieferung, Auf- und Abbau, Abholung und Genehmigung, Gültigkeitsdauer bis zu zwei Tagen.'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => 'int(10) unsigned NULL',
        ],
        'hvz_double_og' => [
            'label' => ['doppelseitige HVZ ohne Genehmigung', 'Aufstellen einer beidseitigen Halteverbotszone (in schmalen Straßen, zur Erhaltung der Durchfahrtsbreite, das ergibt sich vor Ort oder wird angeordnet.) incl. Aufstellfrist, Anlieferung, Auf- und Abbau, Abholung und Genehmigung, Gültigkeitsdauer bis zu zwei Tagen.'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => 'int(10) unsigned NULL',
        ],
        'hvz_only' => [
            'label' => ['Genehmigungsservice', 'Genehmigungsservice für internes'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'hvz_extra_tag' => [
            'label' => ['Extra-Tag Kosten', 'Kosten für jeweils einen weiteren Tag'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'isFamus' => [
            'label' => ['isFamus', 'isFamus'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "int(10) NOT NULL default '0'",
        ],
        'bundesland' => [
            'label' => ['Bundesland', 'Für tolle Bundeslandseite'],
            'exclude' => true,
            'inputType' => 'select',
              'options' => ['', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16'],
              'reference' => &$GLOBALS['TL_LANG']['tl_hvz']['bl'],
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "int(10) NOT NULL default '0'",
        ],
        'kreis' => [
            'label' => ['Kreis', 'Städtekreis'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'lk' => [
            'label' => ['Länderkennzeichen', 'ALPHA-2 <a href="https://de.wikipedia.org/wiki/ISO-3166-1-Kodierliste">https://de.wikipedia.org/wiki/ISO-3166-1-Kodierliste</a>'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 2, 'tl_class' => 'w50'],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'land' => [
            'label' => ['Land', 'Land'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'plz' => [
            'label' => ['PLZ', 'Postleitzahl'],
            'inputType' => 'listWizard',
            'exclude' => true,
            'sql' => 'text NULL',
        ],
        'addImage' => [
            'label' => &$GLOBALS['TL_LANG']['tl_hvz']['addImage'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'singleSRC' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['filesOnly' => true, 'extensions' => $GLOBALS['TL_CONFIG']['validImageTypes'], 'fieldType' => 'radio', 'mandatory' => true],
            'sql' => 'binary(16) NULL',
        ],
        'alt' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['alt'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'long'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'size' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['size'],
            'exclude' => true,
            'inputType' => 'imageSize',
            'options' => $GLOBALS['TL_CROP'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['rgxp' => 'digit', 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'imagemargin' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['imagemargin'],
            'exclude' => true,
            'inputType' => 'trbl',
            'options' => ['px', '%', 'em', 'rem', 'ex', 'pt', 'pc', 'in', 'cm', 'mm'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'imageUrl' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['imageUrl'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50 wizard'],
            'wizard' => [
                ['tl_hvz', 'pagePicker'],
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'fullsize' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['fullsize'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'caption' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['caption'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'allowHtml' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'floating' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['floating'],
            'default' => 'above',
            'exclude' => true,
            'inputType' => 'radioTable',
            'options' => ['above', 'left', 'right', 'below'],
            'eval' => ['cols' => 4, 'tl_class' => 'w50'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'sql' => "varchar(12) NOT NULL default ''",
        ],
        'addEnclosure' => [
            'label' => &$GLOBALS['TL_LANG']['tl_hvz']['addEnclosure'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'enclosure' => [
            'label' => &$GLOBALS['TL_LANG']['tl_hvz']['enclosure'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'filesOnly' => true, 'mandatory' => true],
            'sql' => 'blob NULL',
        ],
        'featured' => [
            'label' => ['Hervorheben', 'Wird in der Liste mit der Landkarte angezeigt'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_hvz']['published'],
            'exclude' => true,
            'filter' => true,
            'flag' => 2,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_hvz.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_hvz extends Backend
{
    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Automatically generate the folder URL aliases.
     *
     * @param array
     * @param mixed $arrButtons
     *
     * @return array
     */
    public function addAliasButton($arrButtons): array
    {
        // Generate the aliases
        if (isset($_POST['alias']) && 'tl_select' === Input::post('FORM_SUBMIT')) {
            $session = $this->Session->getData();
            $ids = $session['CURRENT']['IDS'];

            $objHvz = $this->Database->prepare('SELECT id FROM tl_hvz')
                ->execute();

            $ids = [];
            while ($objHvz->next()) {
                $ids[] = $objHvz->id;
            }

            foreach ($ids as $id) {
                if ($id < 12576) {
                    continue;
                }
                $objPage = HvzModel::findWithDetails($id);

                if (null === $objPage || $objPage->id < 12537) {
                    continue;
                }

                $bLand = ['', 'Baden-Württemberg', 'Bayern', 'Berlin', 'Brandenburg', 'Bremen', 'Hamburg', 'Hessen', 'Mecklenburg-Vorpommern', 'Niedersachsen', 'Nordrhein-Westfalen', 'Saarland', 'Sachsen', 'Sachsen-Anhalt', 'Schleswig-Holstein', 'Thüringen'];

                $objAlias = $this->Database->prepare('SELECT id,question,kreis,bundesland FROM tl_hvz WHERE alias=?')
                    ->execute($strAlias);

                if ($objAlias->numRows > 1) {
                    while ($objAlias->next()) {
                        $strAlias = StringUtil::standardize(StringUtil::restoreBasicEntities($objAlias->question.' ('.$bLand[$objAlias->bundesland].')'));
                        $this->Database->prepare('UPDATE tl_hvz SET alias=? WHERE id=?')
                            ->execute($strAlias, $id);
                    }

                    $objAlias = $this->Database->prepare('SELECT id,question,kreis,bundesland FROM tl_hvz WHERE alias=?')
                        ->execute($strAlias);

                    if ($objAlias->numRows > 1) {
                        while ($objAlias->next()) {
                            $strAlias = StringUtil::standardize(StringUtil::restoreBasicEntities($objAlias->question.' ('.$objAlias->kreis.')'));
                            $this->Database->prepare('UPDATE tl_hvz SET alias=? WHERE id=?')
                                ->execute($strAlias, $id);
                        }

                        $objAlias = $this->Database->prepare('SELECT id,question,kreis,bundesland FROM tl_hvz WHERE alias=?')
                            ->execute($strAlias);

                        if ($objAlias->numRows > 1) {

                            die();
                        }
                    }
                } else {
                    // Set the new alias
                    //$strAlias = StringUtil::standardize(String::restoreBasicEntities($objPage->question));
                    $strAlias = StringUtil::standardize(StringUtil::restoreBasicEntities($objPage->question));
                    // Store the new alias
                    $this->Database->prepare('UPDATE tl_hvz SET alias=? WHERE id=?')
                                       ->execute($strAlias, $id);
                }

                if ($strAlias === $objPage->alias) {
                    continue;
                }
            }
        }

        // Add the button
        $arrButtons['alias'] = '<input type="submit" name="alias" id="alias" class="tl_submit" accesskey="a" value="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['aliasSelected']).'"> ';

        return $arrButtons;
    }

    /**
     * Check permissions to edit table tl_hvz.
     */
    public function checkPermission(): void
    {
        // HOOK: comments extension required
        if (0 and (!\in_array('comments', ModuleLoader::getActive(), true))) {
            $key = array_search('allowComments', $GLOBALS['TL_DCA']['tl_hvz']['list']['sorting']['headerFields'], true);
            unset($GLOBALS['TL_DCA']['tl_hvz']['list']['sorting']['headerFields'][$key]);
        }
    }

    /**
     * Auto-generate the HVZ alias if it has not been set yet.
     *
     * @param mixed
     * @param \DataContainer
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function generateOrtJS(DataContainer $dc)
    {
        /*
                        $objFile = new \File('share/locations.json', true);


                        $objDatabase = \Database::getInstance();
                        $objLocations = $objDatabase->execute("SELECT question, alias FROM tl_hvz WHERE published=1");
                        // Return if there are no pages
                        if ($objLocations->numRows < 1)
                        {
                            return;
                        }

                        $objFile->truncate();
                        $content = '[';
                        $counter = 0;
                        while ($objLocations->next())
                        {
                            if($counter++ != 0){
                                $content .= ',';
                            }
                            $content .= '{"ort":"'.$objLocations->question.'","alias":"'.$objLocations->alias.'"}';

                        }

                        $content .= ']';
                        $objFile->append($content);
                        $objFile->close();
                        $objFile2 = \File::putContent('share/locations.json.gz', gzencode(file_get_contents(TL_ROOT . '/share/locations.json'), 9));

                        // Add a log entry
                        $this->log('Generated Locations JS from tl_hvz', __METHOD__, TL_CRON);
        */
    }

    /**
     * Auto-generate the HVZ alias if it has not been set yet.
     *
     * @param mixed
     * @param \DataContainer
     * @param mixed $varValue
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function generateAlias($varValue, DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ('' === $varValue) {
            $autoAlias = true;
            $varValue = StringUtil::standardize(StringUtil::restoreBasicEntities($dc->activeRecord->question));
        }

        $objAlias = $this->Database->prepare('SELECT id FROM tl_hvz WHERE alias=?')
            ->execute($varValue);

        // Check whether the news alias exists
        if ($objAlias->numRows > 1 && !$autoAlias) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias) {
            $varValue .= '-'.$dc->id;
        }

        return $varValue;
    }

    /**
     * Add the type of input field.
     *
     * @param array
     * @param mixed $arrRow
     *
     * @return string
     */
    public function listQuestions($arrRow)
    {
        $key = $arrRow['published'] ? 'published' : 'unpublished';
        $date = Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $arrRow['tstamp']);

        return '
<div class="cte_type '.$key.'"><strong>'.$arrRow['question'].'</strong> - '.$date.'</div>
<div class="limit_height'.(!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h52' : '').'">
'.$arrRow['hvzinfo'].'
</div>'."\n";
    }

    /**
     * Return the link picker wizard.
     *
     * @param \DataContainer
     *
     * @return string
     */
    public function pagePicker(DataContainer $dc)
    {
        return ' <a href="contao/page.php?do='.Input::get('do').'&amp;table='.$dc->table.'&amp;field='.$dc->field.'&amp;value='.str_replace(['{{link_url::', '}}'], '', $dc->value).'" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':765,\'title\':\''.StringUtil::specialchars(str_replace("'", "\\'", $GLOBALS['TL_LANG']['MOD']['page'][0])).'\',\'url\':this.href,\'id\':\''.$dc->field.'\',\'tag\':\'ctrl_'.$dc->field.(('editAll' === Input::get('act')) ? '_'.$dc->id : '').'\',\'self\':this});return false">'.Image::getHtml('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top;cursor:pointer"').'</a>';
    }

    /**
     * Return the "feature/unfeature element" button.
     *
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param mixed $row
     * @param mixed $href
     * @param mixed $label
     * @param mixed $title
     * @param mixed $icon
     * @param mixed $attributes
     *
     * @return string
     */
    public function iconFeatured($row, $href, $label, $title, $icon, $attributes)
    {
        if (\strlen(Input::get('fid'))) {
            $this->toggleFeatured(Input::get('fid'), (1 === Input::get('state')));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;fid='.$row['id'].'&amp;state='.($row['featured'] ? '' : 1);

        if (!$row['featured']) {
            $icon = 'featured_.gif';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'" '.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (\strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (1 === Input::get('state')));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_hvz::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '" ' . $attributes
               . '>' . Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Feature/unfeature a news item.
     *
     * @param int
     * @param bool
     * @param mixed $intId
     * @param mixed $blnVisible
     *
     * @return string
     */
    public function toggleFeatured($intId, $blnVisible)
    {
        $objVersions = new Versions('tl_hvz', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_hvz']['fields']['featured']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_hvz']['fields']['featured']['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
                } elseif (\is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare('UPDATE tl_hvz SET tstamp='.time().", featured='".($blnVisible ? 1 : '')."' WHERE id=?")
                       ->execute($intId);

        $objVersions->create();
        $this->log('A new version of record "tl_hvz.id='.$intId.'" has been created'.$this->getParentEntries('tl_hvz', $intId), __METHOD__, TL_GENERAL);
    }

    /**
     * Disable/enable a user group.
     *
     * @param int
     * @param bool
     * @param mixed $intId
     * @param mixed $blnVisible
     */
    public function toggleVisibility($intId, $blnVisible): void
    {
        // Check permissions to publish
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_hvz::published', 'alexf')) {
            $this->log('Not enough permissions to publish/unpublish HVZ ID "'.$intId.'"', __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $objVersions = new Versions('tl_hvz', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_hvz']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_hvz']['fields']['published']['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
                } elseif (\is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare('UPDATE tl_hvz SET tstamp='.time().", published='".($blnVisible ? 1 : '')."' WHERE id=?")
                       ->execute($intId);

        $objVersions->create();
        $this->log('A new version of record "tl_hvz.id='.$intId.'" has been created'.$this->getParentEntries('tl_hvz', $intId), __METHOD__, TL_GENERAL);
    }
}
