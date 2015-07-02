<?php

$GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['share'] = array(
	'label'               => array('',$GLOBALS['TL_LANG']['MSC']['facebookShare']),
	'icon'                => 'assets/contao/images/facebook.gif',
	'attributes'		  => 'onclick="window.open(this.href,\'\',\'width=640,height=380,modal=yes,left=100,top=50,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\');return false"',
	'button_callback'     => array('tl_calendar_events_backendContentShare', 'iconShare')
);


class tl_calendar_events_backendContentShare extends Backend
{
	
	public function iconShare($row, $href, $label, $title, $icon, $attributes)
	{		
		$objEvent = CalendarEventsModel::findByPk($row['id']);
				
		/** @var \PageModel $objPage */
		$objPage = $objEvent->getRelated('pid');
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
		if ($strUrl === false && $objEvent->source == 'default')
		{
			return '';
		}
	
		switch ($objEvent->source)
		{
			// Link to an external page
			case 'external':
				if (substr($objEvent->url, 0, 7) == 'mailto:')
				{
					return \String::encodeEmail($objEvent->url);
				}
				else
				{
					return ampersand($objEvent->url);
				}
				break;

			// Link to an internal page
			case 'internal':
				if (($objTarget = $objEvent->getRelated('jumpTo')) !== null)
				{
					return ampersand($this->generateFrontendUrl($objTarget->row()));
				}
				break;

			// Link to an article
			case 'article':
				if (($objArticle = \ArticleModel::findByPk($objEvent->articleId, array('eager'=>true))) !== null && ($objPid = $objArticle->getRelated('pid')) !== null)
				{
					return ampersand($this->generateFrontendUrl($objPid->row(), '/articles/' . ((!\Config::get('disableAlias') && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id)));
				}
				break;
		}

		// Link to the default page
		$strLink =  \Environment::get('base').ampersand(sprintf($strUrl, ((!\Config::get('disableAlias') && $objEvent->alias != '') ? $objEvent->alias : $objEvent->id)));
		
		return '<a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode($strLink).'" title="'.specialchars($title).'" target="_blank"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}

}