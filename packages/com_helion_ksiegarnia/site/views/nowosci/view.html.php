<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
// jimport('joomla.application.component.view');
 
/**
 * HTML View class for the Helion Component
 */
class HelionViewNowosci extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
		// Assign data to the view
		//$this->msg = $this->get('Msg');
		
		//if (count($errors = $this->get('Errors'))) 
		//{
			//JError::raiseError(500, implode('<br />', $errors));
			//return false;
		//}
 
                $document = JFactory::getDocument();
                $document->addStyleSheet('components'.DIRECTORY_SEPARATOR.'com_helion'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'nowosci'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'nowosci.css');
		// Display the view
		parent::display($tpl);
	}
}

?>