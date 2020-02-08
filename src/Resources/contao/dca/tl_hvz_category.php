<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

/**
 * Table tl_hvz_category.
 */
$GLOBALS['TL_DCA']['tl_hvz_category'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'ctable' => ['tl_hvz'],
        'switchToEdit' => true,
        'enableVersioning' => true,
        'onload_callback' => [
            ['tl_hvz_category', 'checkPermission'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['title'],
            'flag' => 1,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
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
                'label' => &$GLOBALS['TL_LANG']['tl_hvz_category']['edit'],
                'href' => 'table=tl_hvz',
                'icon' => 'edit.gif',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_hvz_category']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.gif',
                'button_callback' => ['tl_hvz_category', 'editHeader'],
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_hvz_category']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
                'button_callback' => ['tl_hvz_category', 'copyCategory'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_hvz_category']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{title_legend},title,headline,lkz,jumpTo',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_hvz_category']['title'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'lkz' => [
            'label' => &$GLOBALS['TL_LANG']['tl_hvz_category']['lkz'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 2, 'tl_class' => 'w50'],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'headline' => [
            'label' => &$GLOBALS['TL_LANG']['tl_hvz_category']['headline'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'jumpTo' => [
            'label' => &$GLOBALS['TL_LANG']['tl_hvz_category']['jumpTo'],
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'sortOrder' => [
            'label' => &$GLOBALS['TL_LANG']['tl_hvz_category']['sortOrder'],
            'default' => 'ascending',
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['ascending', 'descending'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(12) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_hvz_category.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 */
class tl_hvz_category extends Backend
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
     * Check permissions to edit table tl_news_archive.
     */
    public function checkPermission()
    {
        // HOOK: comments extension required
        if (!\in_array('comments', ModuleLoader::getActive(), true)) {
            unset($GLOBALS['TL_DCA']['tl_hvz_category']['fields']['allowComments']);
        }

        if ($this->User->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($this->User->hvzs) || empty($this->User->hvzs)) {
            $root = [0];
        } else {
            $root = $this->User->hvzs;
        }

        $GLOBALS['TL_DCA']['tl_hvz_category']['list']['sorting']['root'] = $root;

        // Check permissions to add HVZ categories
        if (!$this->User->hasAccess('create', 'hvzp')) {
            $GLOBALS['TL_DCA']['tl_hvz_category']['config']['closed'] = true;
        }

        // Check current action
        switch (Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!\in_array(Input::get('id'), $root, true)) {
                    $arrNew = $this->Session->get('new_records');

                    if (\is_array($arrNew['tl_hvz_category']) && \in_array(Input::get('id'), $arrNew['tl_hvz_category'], true)) {
                        // Add permissions on user level
                        if ('custom' === $this->User->inherit || !$this->User->groups[0]) {
                            $objUser = $this->Database->prepare('SELECT hvzs, hvzp FROM tl_user WHERE id=?')
                                                       ->limit(1)
                                                       ->execute($this->User->id);

                            $arrhvzp = deserialize($objUser->hvzp);

                            if (\is_array($arrhvzp) && \in_array('create', $arrhvzp, true)) {
                                $arrhvzs = deserialize($objUser->hvzs);
                                $arrhvzs[] = Input::get('id');

                                $this->Database->prepare('UPDATE tl_user SET hvzs=? WHERE id=?')
                                               ->execute(serialize($arrhvzs), $this->User->id);
                            }
                        }

                        // Add permissions on group level
                        elseif ($this->User->groups[0] > 0) {
                            $objGroup = $this->Database->prepare('SELECT hvzs, hvzp FROM tl_user_group WHERE id=?')
                                                       ->limit(1)
                                                       ->execute($this->User->groups[0]);

                            $arrhvzp = deserialize($objGroup->hvzp);

                            if (\is_array($arrhvzp) && \in_array('create', $arrhvzp, true)) {
                                $arrhvzs = deserialize($objGroup->hvzs);
                                $arrhvzs[] = Input::get('id');

                                $this->Database->prepare('UPDATE tl_user_group SET hvzs=? WHERE id=?')
                                               ->execute(serialize($arrhvzs), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = Input::get('id');
                        $this->User->hvzs = $root;
                    }
                }
                // no break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!\in_array(Input::get('id'), $root, true) || ('delete' === Input::get('act') && !$this->User->hasAccess('delete', 'hvzp'))) {
                    $this->log('Not enough permissions to '.Input::get('act').' HVZ category ID "'.Input::get('id').'"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if ('deleteAll' === Input::get('act') && !$this->User->hasAccess('delete', 'hvzp')) {
                    $session['CURRENT']['IDS'] = [];
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (\strlen(Input::get('act'))) {
                    $this->log('Not enough permissions to '.Input::get('act').' HVZ categories', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }
    }

    /**
     * Return the edit header button.
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
    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || \count(preg_grep('/^tl_hvz_category::/', $this->User->alexf)) > 0) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }

    /**
     * Return the copy category button.
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
    public function copyCategory($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('create', 'hvzp')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }

    /**
     * Return the delete category button.
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
    public function deleteCategory($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('delete', 'hvzp')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }
}
