<?php

$GLOBALS['TL_DCA']['tl_news']['list']['operations']['share'] = array(
	'label'               => array('',$GLOBALS['TL_LANG']['MSC']['facebookShare']),
	'icon'                => 'assets/contao/images/facebook.gif',
	'attributes'		  => 'onclick="window.open(this.href,\'\',\'width=640,height=380,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\');return false"',
	'button_callback'     => array('tl_news_backendContentShare', 'iconShare')
);


class tl_news_backendContentShare extends News
{
	public function iconShare($row, $href, $label, $title, $icon, $attributes)
	{		
		$objArticle = NewsModel::findByPk($row['id']);
		
		/** @var \PageModel $objPage */
		$objPage = $objArticle->getRelated('pid');
		$jumpTo = $objPage->jumpTo;

		// No jumpTo page set (see #4784)
		if (!$jumpTo)
		{
			return '';
		}

		
		$objParent = \PageModel::findWithDetails($jumpTo);

		// A jumpTo page is set but does no longer exist (see #5781)
		if ($objParent === null)
		{
			$strUrl = false;
		}
		else
		{
			$strUrl = $this->generateFrontendUrl($objParent->row(), ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ?  '/%s' : '/items/%s'), $objParent->language);
		}
		
		// Skip the event if it requires a jumpTo URL but there is none
		if ($strUrl === false && $objArticle->source == 'default')
		{
			return '';
		}
	
		$strLink = $this->getLink($objArticle, $strUrl, \Environment::get('base'));
		
		return '<a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode($strLink).'" title="'.specialchars($title).'" target="_blank"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}
}