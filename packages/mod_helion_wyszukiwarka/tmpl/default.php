<?php // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

// dodaj css
$document = JFactory::getDocument();
$document->addStyleSheet('modules/mod_helion_wyszukiwarka/css/mod_helion_wyszukiwarka.css');

$link_ksiegarnia = $params->get('link_ksiegarnia');

if(empty($link_ksiegarnia)) {
	echo '<p>Nie podano w konfiguracji linku do księgarni.</p>';
	return false;
}

?>

    <form action="<?php echo $link_ksiegarnia; ?>" method="get" class="helion-search">
        <input type="text" name="fraza" class="input-small" value="wyszukaj książki..." onclick="this.value=''" /> 
        <input type="submit" value="Szukaj" class="helion-search-submit btn btn-primary btn-small"/>
	<input type="hidden" name="view" value="szukaj" />
    </form>
