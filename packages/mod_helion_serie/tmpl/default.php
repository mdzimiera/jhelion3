<?php // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

// dodaj css
$document = JFactory::getDocument();
$document->addStyleSheet('modules/mod_helion_serie/css/mod_helion_serie.css');

// dodaj js
$document->addScript('modules/mod_helion_serie/js/mod_helion_serie.js');

$ksiegarnia = $params->get('ksiegarnia');
$link = $params->get('ksiegarnia_link');

$db = JFactory::getDbo();

$query = "SELECT value FROM #__helion_config WHERE meta = 'ksiegarnia'";
$db->setQuery($query);
$ksiegarnia = $db->loadResult();

$query = "SELECT value FROM #__helion_config WHERE meta = " . $db->quote($ksiegarnia . '_serie') . ";";
$db->setQuery($query);
$serie = $db->loadResult();
$serie = unserialize($serie);

if(!empty($serie)) {
    
    echo '<ul class="mod_serie">';
    
    foreach($serie as $id => $value) {
        echo '<li><a href="' . $link . "?view=serie&id=" . $id . '" title="'. ucfirst($value) .'">' . ucfirst($value) . '</a></li>';
    }
    
    echo '</ul>';
} else {
    echo '<p>Dane dotyczące serii nie są dostępne. </p>';
}
?>