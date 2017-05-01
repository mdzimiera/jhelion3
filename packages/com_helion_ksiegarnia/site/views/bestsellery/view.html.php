<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
// jimport('joomla.application.component.view');
 
/**
 * HTML View class for the Helion Component
 */
class HelionViewBestsellery extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
            $document = JFactory::getDocument();
            $document->addStyleSheet('components'.DIRECTORY_SEPARATOR.'com_helion'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'bestsellery'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'bestsellery.css');
            parent::display($tpl);
	}
}

?>