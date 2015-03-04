<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// dodaj css
$document = JFactory::getDocument();
$document->addStyleSheet('components'.DIRECTORY_SEPARATOR.'com_helion'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'serie'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'serie.css');

$seria = JRequest::getInt('id');

$db =& JFactory::getDbo();

$cyfra = "8";
$query = "SELECT value FROM #__helion_config WHERE meta = 'ksiegarnia'";
$db->setQuery($query);
$ksiegarnia = $db->loadResult();

$query = "SELECT value FROM #__helion_config WHERE meta = 'partner_id'";
$db->setQuery($query);
$partner_id = $db->loadResult();

$query = "SELECT update_time FROM #__helion_status WHERE ksiegarnia = " . $db->quote($ksiegarnia . '_serie') . ";";
$db->setQuery($query);
$last_update = $db->loadResult();

if(!$last_update || $last_update <= (time() - 86400)) {
    $query = "DELETE FROM #__helion_config WHERE meta = " . $db->quote($ksiegarnia . '_serie') . ";";
    $db->setQuery($query);
    $db->query();
    
    $external = "http://" . $ksiegarnia . ".pl/plugins/new/xml/lista-serie.cgi";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $external);
    $out = curl_exec($ch);
    curl_close($ch);

    if(($xml = simplexml_load_string($out)) !== false){

            $lista = array();

            foreach($xml->item as $item) {
                $lista[(int)$item->attributes()->id_seria] = (string)$item->attributes()->seria;
            }
        $k = new stdClass();
        $k->id = null;
        $k->meta = $ksiegarnia . "_serie";
        $k->value = serialize($lista);
        $db->insertObject('#__helion_config', $k, 'id');
    }
    
    $query = "UPDATE #__helion_status SET update_time = " .$db->quote(time()) . " WHERE ksiegarnia = " . $db->quote($ksiegarnia . '_serie') . ";";
    $db->setQuery($query);
    $db->query();
}
    
if($seria) {
    $query = "SELECT value FROM #__helion_config WHERE meta = " . $db->quote($ksiegarnia . '_serie') . ";";
    $db->setQuery($query);
    $serie = $db->loadResult();
    $serie = unserialize($serie);
    if(!empty($serie)) {
        
        if(array_key_exists($seria, $serie)) {
            $seria_name = '<a href="' . JURI::current() . '">Księgarnia</a> &gt; ' . ucfirst($serie[$seria]);
        } else {
            $seria_name = 'Nieznana seria';
        } 
        
        
?>
<h1><?php echo $seria_name; ?></h1>
<?php
$query = "SELECT value FROM #__helion_config WHERE meta = 'wyszukiwarka_w_tresci'";
$db->setQuery($query);
$wyszukiwarka_w_tresci = $db->loadResult();

if($wyszukiwarka_w_tresci) {
?>
<div class="helion_wyszukiwarka">
    <form action="<?php echo JURI::current(); ?>" method="get">
        <input type="hidden" name="view" value="szukaj" />
        <input type="text" name="fraza" value="<?php echo !empty($fraza) ? $fraza : 'wyszukaj...'; ?>" onclick="this.value = '';"/>
        <input type="submit" value="Szukaj" />
    </form>
</div>
<div class="wyszukiwarka_clear"></div>
<?php 
}

$query = "SELECT * FROM #__helion WHERE ksiegarnia = '" . $ksiegarnia . "' "
        . "AND seriewydawnicze LIKE '%," . $seria . ",%' AND cena LIMIT 10";

$db->setQuery($query);
$result = $db->loadAssocList();

foreach($result as $ksiazka) {
    $url = JURI::current() . "?view=ksiazka&ident=" . $ksiazka['ident'] . "&ksiegarnia=" . $ksiazka['ksiegarnia'];
    $koszyk = "http://" . $ksiazka['ksiegarnia'] . ".pl/add/" . $partner_id . "/" . $cyfra . "/" . $ksiazka['ident'];
    
    if(!empty($ksiazka['tytul'])){
?>
<div class="helion_ksiazka">
    <div class="ksiazka_info">
    <table width="100%">
        <tr>
            <td rowspan="5" class="okladka"><a href="<?php echo $url; ?>" title="<?php echo $ksiazka['tytul']?>"><img src="http://<?php echo $ksiegarnia; ?>.pl/okladki/125x163/<?php echo preg_replace('/\_ebook$/i', '', $ksiazka['ident']); ?>.jpg" /></a></td>
            <td><h3><a href="<?php echo $url; ?>" title="<?php echo $ksiazka['tytul']?>"><?php echo $ksiazka['tytul']; ?></a></h3></td>
        </tr>
        <tr>
            <td class="autor">Autor: <?php echo $ksiazka['autor']; ?></td>
        </tr>
        <tr>
            <td class="format">Format: <?php if(preg_match('/\_ebook$/i', $ksiazka['ident'])):?>eBook<?php else:?>Druk<?php endif?></td>
        </tr>
        <tr>
            <td class="pcena">
            <span class="cena">Cena: <?php echo $ksiazka['cena']; ?> zł</span> 
            <?php if($ksiazka['znizka'] > 0) echo ' <span class="znizka">(-' . $ksiazka['znizka'] . "%)</span>"; ?>
        </td>
        </tr>
        <tr>
            <td><p class="kupteraz"><a href="<?php echo $koszyk; ?>" target="_blank"><img src="http://helion.pl/img/koszyk/koszszary.jpg"/></a></p></td>
        </tr>
    </table>
    </div>
</div>
<?php
    }
}
?>

<?php
    } else {
        echo '<p>Brak danych o seriach.</p>';
    }
} else {
    echo "<h1>Niepoprawna wartość parametru seria.</h1>";
}
?>

<p><a href="<?php echo JURI::current(); ?>">Powrót do strony głównej księgarni</a></p>