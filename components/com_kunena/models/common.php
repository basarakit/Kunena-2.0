<?php
/**
 * Kunena Component
 * @package Kunena.Site
 * @subpackage Models
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * Common Model for Kunena
 *
 * @since		2.0
 */
class KunenaModelCommon extends KunenaModel {
	protected function populateState() {
		$params = $this->getParameters();
		$this->setState ( 'params', $params );
	}

	public function getAnnouncement() {
		$items = KunenaForumAnnouncementHelper::getAnnouncements(0, 1, $this->me->isModerator('global'));
		return array_pop($items);
	}
}