<?php
/**
 * Kunena Component
 * @package Kunena.Framework
 * @subpackage HTML
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

jimport ( 'joomla.html.pagination' );

/**
 * Pagination Class.  Provides a common interface for content pagination for the
 * Joomla! Framework.
 *
 * @since		2.0
 */
class KunenaHtmlPagination extends JPagination
{
	/**
	 * Prefix used for request variables.
	 *
	 * @var int
	 * @since	Joomla 1.6
	 */
	public $prefix = null;

	/**
	 * Additional URL parameters to be added to the pagination URLs generated by the class.  These
	 * may be useful for filters and extra values when dealing with lists and GET requests.
	 *
	 * @var		array
	 * @since	Joomla 1.6
	 */
	protected $_additionalUrlParams = array();
	protected $_uri = null;

	function __construct($total, $limitstart, $limit, $prefix = '') {
		// If out of range, use last page
		if ($total < (int) $limitstart)
			$limitstart = intval($total / $limit) * $limit;

		parent::__construct($total, $limitstart, $limit, $prefix);
		$this->setDisplay();
		if ($limitstart === false) $this->set('pages.current', -1);
		$this->ktemplate = KunenaFactory::getTemplate();
	}

	function setDisplay($displayedPages = 7, $uri = null) {
		$this->_uri = $uri;
		// From Joomla 1.6:
		// Set the pagination iteration loop values.
		$this->set('pages.start', $this->get('pages.current') - intval($displayedPages / 2));
		if ($this->get('pages.start') < 1) {
			$this->set('pages.start', 1);
		}
		if (($this->get('pages.start') + $displayedPages) > $this->get('pages.total')) {
			$this->set('pages.stop', $this->get('pages.total'));
			if ($this->get('pages.total') < $displayedPages) {
				$this->set('pages.start', 1);
			} else {
				$this->set('pages.start', $this->get('pages.total') - $displayedPages + 1);
			}
		} else {
			$this->set('pages.stop', ($this->get('pages.start') + $displayedPages - 1));
		}
	}

		/**
	 * Create and return the pagination page list string, ie. Previous, Next, 1 2 3 ... x.
	 *
	 * @return	string	Pagination page list string.
	 * @since	2.0
	 */
	public function getPagesLinks()
	{
		if ($this->get('pages.total') <= 1) {
			return;
		}

		// Build the page navigation list.
		$data = $this->_buildDataObject();

		$list = array();
		$list['prefix'] = $this->prefix;

		$list['pages'] = array();
		foreach ($data->pages as $i => $page)
		{
			if ($page->base !== null) {
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data'] = $this->_item_active($page);
			} else {
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data'] = $this->_item_inactive($page);
			}
		}

		return $this->_list_render($list);
	}

	/**
	 * Return the pagination footer.
	 *
	 * @return	string	Pagination footer.
	 * @since	1.0
	 */
	public function getListFooter()
	{
		$app = JFactory::getApplication();

		$list = array();
		$list['prefix']			= $this->prefix;
		$list['limit']			= $this->limit;
		$list['limitstart']		= $this->limitstart;
		$list['total']			= $this->total;
		$list['limitfield']		= $this->getLimitBox();
		$list['pagescounter']	= $this->getPagesCounter();
		$list['pageslinks']		= $this->getPagesLinks();

		return $this->_list_footer($list);
	}

	/**
	 * Creates a dropdown box for selecting how many records to show per page.
	 *
	 * @return	string	The html for the limit # input box.
	 * @since	1.0
	 */
	public function getLimitBox()
	{
		$app = JFactory::getApplication();

		// Initialise variables.
		$limits = array ();

		// Make the option list.
		for ($i = 5; $i <= 30; $i += 5) {
			$limits[] = JHtml::_('select.option', "$i");
		}
		$limits[] = JHtml::_('select.option', '50', JText::_('50'));
		$limits[] = JHtml::_('select.option', '100', JText::_('100'));

		$selected = $this->_viewall ? 0 : $this->limit;

		// Build the select list.
		if ($app->isAdmin()) {
			if (version_compare(JVERSION, '1.6','>')) {
				// Joomla 1.6+
				$html = JHtml::_('select.genericlist',  $limits, $this->prefix . 'limit', 'class="inputbox" size="1" onchange="Joomla.submitform();"', 'value', 'text', $selected);
			} else {
				// Joomla 1.5
				$html = JHTML::_('select.genericlist',  $limits, $this->prefix . 'limit', 'class="inputbox" size="1" onchange="submitform();"', 'value', 'text', $selected);
			}
		}
		else {
			$html = JHtml::_('select.genericlist',  $limits, $this->prefix . 'limit', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', $selected);
		}
		return $html;
	}

	public function _list_footer($list) {
		return $this->ktemplate->getPaginationListFooter($list);
	}

	public function _list_render($list) {
		return $this->ktemplate->getPaginationListRender($list);
	}

	/**
	 * (non-PHPdoc)
	 * @see JPagination::_item_active()
	 */
	public function _item_active(&$item) {
		if (JFactory::getApplication()->isAdmin()) {
			if ($item->base > 0) {
				if (version_compare(JVERSION, '1.6','>')) {
					// Joomla 1.6+
					return "<a title=\"".$item->text."\" onclick=\"javascript: document.adminForm.." . $this->prefix . "limitstart.value=".$item->base."; Joomla.submitform();return false;\">".$item->text."</a>";
				} else {
					// Joomla 1.5
					return "<a title=\"".$item->text."\" onclick=\"javascript: document.adminForm.." . $this->prefix . "limitstart.value=".$item->base."; submitform();return false;\">".$item->text."</a>";
				}
			}
			else {
				return "<a title=\"".$item->text."\" onclick=\"javascript: document.adminForm.." . $this->prefix . "limitstart.value=0; Joomla.submitform();return false;\">".$item->text."</a>";
			}
		}
		else {
			return $this->ktemplate->getPaginationItemActive($item);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see JPagination::_item_inactive()
	 */
	public function _item_inactive(&$item) {
		$app = JFactory::getApplication();
		if ($app->isAdmin()) {
			return "<span>".$item->text."</span>";
		}
		else {
			return $this->ktemplate->getPaginationItemInactive($item);
		}
	}

	/**
	 * Create and return the pagination data object.
	 *
	 * @return	object	Pagination data object.
	 * @since	Joomla 1.6
	 */
	public function _buildDataObject() {
		// Initialise variables.
		$data = new stdClass();

		// Build the additional URL parameters string.
		$uri = KunenaRoute::normalize($this->_uri, true);
		$uri->delVar('start');
		$uri->delVar('limitstart');
		$uri->delVar('limit');
		if (!empty($this->_additionalUrlParams)) {
			foreach($this->_additionalUrlParams as $key => $value) {
				$uri->setVar($key, $value);
			}
		}

		$data->pages = array();
		$range = range($this->get('pages.start'), $this->get('pages.stop'));
		$range[] = 1;
		$range[] = $this->get('pages.total');
		sort($range);
		foreach ($range as $i) {
			$offset = ($i -1) * $this->limit;

			$data->pages[$i] = new JPaginationObject($i, $this->prefix);
			if ($i != $this->get('pages.current') || $this->_viewall) {
				$uri->setVar($this->prefix.'limitstart', $offset);
				$data->pages[$i]->base	= $offset;
				$data->pages[$i]->link	= KunenaRoute::_($uri);
			}
		}
		return $data;
	}
}