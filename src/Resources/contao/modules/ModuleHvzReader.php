<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

use Patchwork\Utf8;
use Psr\Log\LogLevel;

/**
 * Class ModuleHvzReader.
 *
 * @property string $com_template
 * @property array  $hvz_categories
 *
 * @author Dennis Esken
 */
class ModuleHvzReader extends \Module
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_hvzreader';

    private $lkz = '';
    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['hvzreader'][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        // Set the item from the auto_item parameter
        if (!isset($_GET['items']) && \Config::get('useAutoItem') && isset($_GET['auto_item'])) {
            \Input::setGet('items', \Input::get('auto_item'));
        }

        // loookup via lkz
        $lkz = key($_GET);

        // no lkz found - redirect to standard = /de/
        if($lkz === 'auto_item'){
            $url = 'halteverbot/de/'.\Input::get('auto_item').'.html';
            $this->redirect($url, 301);

        }

        // check lkz
        if(strlen($lkz) !== 2){
            /* @var \PageModel $objPage */
            global $objPage;

            $objPage->noSearch = 1;
            $objPage->cache = 0;

            return '';
        }

        \Input::setGet('items', \Input::get($lkz));
        $hvzCat = HvzCategoryModel::findOneBy('lkz', $lkz);
        $this->hvz_categories = \StringUtil::deserialize($this->hvz_categories);

        if(!$hvzCat or !in_array($hvzCat->id, $this->hvz_categories)){
            /* @var \PageModel $objPage */
            global $objPage;

            $objPage->noSearch = 1;
            $objPage->cache = 0;
            return '';
        }

        $this->lkz = $hvzCat->id;

        if (TL_MODE === 'FE') {
            $GLOBALS['TL_JAVASCRIPT'][] = '/bundles/chuckkicontaohvz/js/pikaday.min.js|static';
            $GLOBALS['TL_JAVASCRIPT'][] = '/bundles/chuckkicontaohvz/js/validateForm.min.js|static';
        }

        // Do not index or cache the page if there are no categories
        if (!\is_array($this->hvz_categories) || empty($this->hvz_categories)) {
            /* @var \PageModel $objPage */
            global $objPage;

            $objPage->noSearch = 1;
            $objPage->cache = 0;

            return '';
        }

        return parent::generate();
    }

    /**
     * Generate the module.
     */
    protected function compile()
    {
        /* @var \PageModel $objPage */
        global $objPage;

        $objPage->cssClass .= ' hvzForm';

        $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
        $this->Template->referer = 'javascript:history.go(-1)';

        /** @var HvzModel $objHvz */
        $objHvz = HvzModel::findPublishedByParentAndIdOrAlias(\Input::get('items'), [$this->lkz]);

        if (null === $objHvz) {
            // Do not index or cache the page
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            $name = \Input::get('items');

            $request = trim($name);
            $request = mb_strtolower($request, 'UTF-8');

            $hvzResult = new ModuleHvzResult();
            $myResults = $hvzResult->searchMe($request, $lkz);
            $myResults = array_unique($myResults, SORT_REGULAR);

            if (1 === \count($myResults)) {
                $url = 'halteverbot/'.$myResults[0]['alias'].'.html';
                $this->redirect($url, 301);
                exit;
            }
            $this->redirect('halteverbot-suche.html?suche='.$name.'&lkz='.$this->lkz, 302);
            exit;
        }

        $updateFamus = (int) ($objHvz->isFamus) + 1;
        $this->import('Database');
        $objUpdate = $this->Database->prepare('UPDATE tl_hvz set isFamus = ? where id = ?')
            ->execute($updateFamus, $objHvz->id);
        $this->Template->hvz_id = $objHvz->id;
        $this->Template->hvz_land = $objHvz->land;
        $this->Template->lkz = $objHvz->lk;
        $this->Template->hvz_single = $objHvz->hvz_single;
        $this->Template->hvz_double = $objHvz->hvz_double;
        $this->Template->hvz_single_og = $objHvz->hvz_single_og;
        $this->Template->hvz_double_og = (!empty($objHvz->hvz_double_og)) ? $objHvz->hvz_double_og : (int) ($objHvz->hvz_single_og) + ((int) ($objHvz->hvz_double) - (int) ($objHvz->hvz_single));

        $this->Template->MwSt = HvzOrderModel::MWST_INTL_GERMANY;

        $vorlauf = $objHvz->hvz_min_vorlauf ?:HvzModel::MINTAGEVORLAUF;
        $this->Template->hvz_min_vorlauf = $vorlauf;
        $this->Template->hvz_extra_tag = $objHvz->hvz_extra_tag;
        $this->Template->hvzzusatz = $objHvz->hvzzusatz;
        $this->Template->hvzinfo = $objHvz->hvzinfo;
        $this->Template->isUser = false;
        $this->Template->isKlarnaPaymentActive = $GLOBALS['TL_CONFIG']['isAktive_klarna'];
        $this->Template->isPaypalPaymentActive = $GLOBALS['TL_CONFIG']['isAktive_paypal'];
        $this->Template->hasOtherPaymentsThanInvoice = ($GLOBALS['TL_CONFIG']['isAktive_klarna'] or $GLOBALS['TL_CONFIG']['isAktive_paypal']);

        $this->Template->isInvoicePaymentActive = $GLOBALS['TL_CONFIG']['invoice_for_all'];

        // import FrontEndUser Data
        $this->import('FrontendUser', 'user');
        if (FE_USER_LOGGED_IN) {

            $this->Template->isInvoicePaymentActive = $this->user->isAktive_invoice || $GLOBALS['TL_CONFIG']['invoice_for_all'];

            $this->Template->hasOtherPaymentsThanInvoice = $this->user->paymentAllowed || ($GLOBALS['TL_CONFIG']['isAktive_klarna'] or $GLOBALS['TL_CONFIG']['isAktive_paypal']);
            $this->Template->isKlarnaPaymentActive = $this->user->paymentAllowed || $GLOBALS['TL_CONFIG']['isAktive_klarna'];
            $this->Template->isPaypalPaymentActive = $this->user->paymentAllowed || $GLOBALS['TL_CONFIG']['isAktive_paypal'];


            $this->Template->isUser = true;
            $this->Template->userGender = $this->user->gender;
            $this->Template->userStreNum = $this->user->postal.' '.$this->user->city;
            $this->Template->hasUmstid = '';
            if ('' !== $this->user->umstid) {
                $this->Template->hasUmstid = 'show';
            }
        }

        // Overwrite the page title and description (see #2853 and #4955)
        if ('' !== $objHvz->question) {
            $smallestPrice = (!empty($objHvz->hvz_single_og)) ? $objHvz->hvz_single_og : $objHvz->hvz_single;
            if (!empty($objHvz->seitentitel)) {
                $objPage->pageTitle = strip_tags(\StringUtil::stripInsertTags($objHvz->seitentitel));
            } else {
                $objPage->pageTitle = 'Halteverbot in '.strip_tags(\StringUtil::stripInsertTags($objHvz->question)).' beantragen';
            }
            $newDescription = 'Ein Halteverbot in '.$objHvz->question.' ab '.$smallestPrice.',00 € bestellen. Beantragen Sie ein Parkverbot in '.$objHvz->question.' im Sorglospaket bei den Halteverbot Profis.';

            if (!empty($objHvz->descOrt)) {
                $objPage->description = $this->prepareMetaDescription($objHvz->descOrt);
            } else {
                $objPage->description = $newDescription;
            }
            $GLOBALS['TL_KEYWORDS'] = 'Halteverbot '.$objHvz->question.', Halteverbotszone '.$objHvz->question.', Haltverbot beantragen '.$objHvz->question.', Halteverbot bestellen '.$objHvz->question;
        }

        $this->Template->question = $objHvz->question;

        // Clean RTE output
        $objHvz->hvzinfo = \StringUtil::toHtml5($objHvz->hvzinfo);
        $this->Template->hvzinfo = \StringUtil::encodeEmail($objHvz->hvzinfo);
    }
}
