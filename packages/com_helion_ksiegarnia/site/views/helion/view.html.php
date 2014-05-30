<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
// jimport('joomla.application.component.view');
 
/**
 * HTML View class for the Helion Component
 */
class HelionViewHelion extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null) 
	{
		// Assign data to the view
		$this->msg = $this->get('Msg');
		
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
 
		// Display the view
		parent::display($tpl);
	}
    
    function trunc($phrase, $max_words) {
       $phrase_array = explode(' ',$phrase);
       if(count($phrase_array) > $max_words && $max_words > 0)
          $phrase = implode(' ', array_slice($phrase_array, 0, $max_words)).'...';
       return $phrase;
    }
}

?>