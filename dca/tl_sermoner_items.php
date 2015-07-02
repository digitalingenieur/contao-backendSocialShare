<?php

	$GLOBALS['TL_DCA']['tl_sermoner_items']['list']['operations']['share'] = array(
		'label'               => &$GLOBALS['TL_LANG']['tl_sermoner_items']['share'],
		'icon'                => 'assets/contao/images/facebook.gif',
		'button_callback'     => array('tl_sermoner_items_backendContentShare', 'iconShare')
	);



class tl_sermoner_items_backendContentShare extends Backend
{
	public function iconShare($row, $href, $label, $title, $icon, $attributes)
	{		
		$objSermon = SermonerItemsModel::findByPk($row['id']);
		
		/** @var \PageModel $objPage */
		$objPage = $objSermon->getRelated('pid');
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
			
		$strLink = \Environment::get('base').sprintf($strUrl, (($objSermon->alias != '' && !$GLOBALS['TL_CONFIG']['disableAlias']) ? $objSermon->alias : $objSermon->id));
		
		return '<a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode($strLink).'" title="'.specialchars($title).'" target="_blank"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}
}