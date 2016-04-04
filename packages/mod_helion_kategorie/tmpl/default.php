<?php // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

// dodaj css
$document = JFactory::getDocument();
$document->addStyleSheet('modules/mod_helion_kategorie/css/mod_helion_kategorie.css');

// dodaj js
$document->addScript('modules/mod_helion_kategorie/js/mod_helion_kategorie.js');

$ksiegarnia = $params->get('ksiegarnia');
$link = $params->get('ksiegarnia_link');

$db = JFactory::getDbo();

$query = "SELECT value FROM #__helion_config WHERE meta = 'ksiegarnia'";
$db->setQuery($query);
$ksiegarnia = $db->loadResult();

$query = "SELECT value FROM #__helion_config WHERE meta = " . $db->quote($ksiegarnia . '_kategorie') . ";";
$db->setQuery($query);
$kategorie = $db->loadResult();
$kategorie = unserialize($kategorie);
    
if(!empty($kategorie)) {
    
    echo '<ul class="mod_kategorie">';
    
    foreach($kategorie as $id => $nad) {
        if(isset($nad['pod']) && is_array($nad['pod']) && !empty($nad['pod'])){
            echo '<li class="mod_nadkategorie">'. $nad['nad'] .'</li>';
            if(is_array($nad['pod']) && !empty($nad['pod'])){
                echo '<li class="mod_podkategorie_li"><ul class="mod_podkategorie">';
                foreach($nad['pod'] as $pid => $pod){
                    echo '<li><a href="' . $link . "?view=kategoria&id=" . $pid . '" title="'. $pod .'">' . $pod . '</a></li>';
                }
                echo '</ul></li>';
            }
        }else{
            if(isset($nad['nad'])){
                echo '<li class="mod_nadkategorie_li"><a href="' . $link . "?view=kategoria&id=" . $id . '" title="'. $nad['nad'] .'">' . $nad['nad'] . '</a></li>';
            }
        }
    }
    
    echo '<li class="mod_nadkategorie_li"><a href="' . $link . "?view=nowosci" . '">Nowości</a></li>';
    echo '<li class="mod_nadkategorie_li"><a href="' . $link . "?view=bestsellery" . '">Bestsellery</a></li>';
    echo '</ul>';
} else {
    echo '<p>Dane dotyczące kategorii nie są dostępne. </p>';
}
?>