<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */
namespace Chuckki\ContaoHvzBundle;

use Contao\PageModel;

/**
 * Provide methods regarding FAQs.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleFaqReader extends \Contao\ModuleFaqReader
{

/**
	 * Generate the module
	 */
	protected function compile()
	{
		/** @var PageModel $objPage */
		global $objPage;

		$objFaq = \FaqModel::findPublishedByParentAndIdOrAlias(\Input::get('items'), $this->faq_categories);
		if (null !== $objFaq)
		{
            $updateFamus = intval($objFaq->isFamus) + 1;
            $this->import('Database');
            $objUpdate = $this->Database->prepare("UPDATE tl_faq set isFamus = ? where id = ?")
                ->execute($updateFamus, $objFaq->id);
		}
        $buffer = parent::compile();
	}

}
