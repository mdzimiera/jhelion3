<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
//jimport('joomla.application.component.view');
 
/**
 * HTML View class for the Helion Component
 */
class HelionViewSerie extends JViewLegacy
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
 
		// Display the view
		parent::display($tpl);
	}
}

?>