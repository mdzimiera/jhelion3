<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$kategoria = JRequest::getInt('id');

$db = JFactory::getDbo();

$cyfra = "8";
$query = "SELECT value FROM #__helion_config WHERE meta = 'ksiegarnia'";
$db->setQuery($query);
$ksiegarnia = $db->loadResult();

$query = "SELECT value FROM #__helion_config WHERE meta = 'partner_id'";
$db->setQuery($query);
$partner_id = $db->loadResult();

$query = "SELECT update_time FROM #__helion_status WHERE ksiegarnia = " . $db->quote($ksiegarnia . '_kategorie') . ";";
$db->setQuery($query);
$last_update = $db->loadResult();

if(!$last_update || $last_update <= (time() - 86400)) {
    $query = "DELETE FROM #__helion_config WHERE meta = " . $db->quote($ksiegarnia . '_kategorie') . ";";
    $db->setQuery($query);
    $db->query();
    
    $external = "http://" . $ksiegarnia . ".pl/plugins/new/xml/lista-katalog.cgi";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $external);
    $out = curl_exec($ch);
    curl_close($ch);

    $xml = simplexml_load_string($out);
   
    $lista = array();

        foreach($xml->item as $item) {
            $grupa_nad = (string) $item->attributes()->grupa_nad;
            $id_nad = (string) $item->attributes()->id_nad;

            $grupa_pod = (string) $item->attributes()->grupa_pod;
            $id_pod = (string) $item->attributes()->id_pod;

            if(!isset($lista[$id_nad])){
                $lista[$id_nad] = array('nad' => $grupa_nad);
            }
            if($grupa_pod != 'eBooki' && !empty($grupa_pod)){
                $lista[$id_nad]['pod'][$id_pod] = $grupa_pod;
            }
        }
    
    $k = new stdClass();
    $k->id = null;
    $k->meta = $ksiegarnia . "_kategorie";
    $k->value = serialize($lista);
    $db->insertObject('#__helion_config', $k, 'id');
    
    $query = "UPDATE #__helion_status SET update_time = " .$db->quote(time()) . " WHERE ksiegarnia = " . $db->quote($ksiegarnia . '_kategorie') . ";";
    $db->setQuery($query);
    $db->query();
}
if($kategoria) {
    $query = "SELECT value FROM #__helion_config WHERE meta = " . $db->quote($ksiegarnia . '_kategorie') . ";";
    $db->setQuery($query);
    $kategorie = $db->loadResult();
    $kategorie = unserialize($kategorie);
    if(!empty($kategorie)) {
        
        if(array_key_exists($kategoria, $kategorie)) {
            $cat_name = '<a href="' . JURI::current() . '">Księgarnia</a> &gt; ' . $kategorie[$kategoria]['nad'];
        } else {
            foreach($kategorie as $key => $value){
                if(isset($value['pod']) && is_array($value['pod']) && array_key_exists($kategoria, $value['pod'])){
                    $cat_name = '<a href="' . JURI::current() . '">Księgarnia</a> &gt; <a href="' . JURI::current() . '?view=kategoria&id=' . $key . '">' . $value['nad'] . '</a> &gt; ' . $value['pod'][$kategoria];
                    break;
                }
            }
            //$cat_name = '<a href="' . JURI::current() . '">Księgarnia</a> &gt; <a href="' . JURI::current() . '?view=kategoria&id=' . key($kategorie['pod'][$kategoria]) . '">' . $kategorie['nad'][key($kategorie['pod'][$kategoria])] . '</a> &gt; ' . reset($kategorie['pod'][$kategoria]);
        } 
        /*else {
            $cat_name = 'Nieznana Kategoria';
        }*/
?>
<h1><?php echo $cat_name; ?></h1>
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

$subkategorie = array();
$ebooki = false; 
if(isset($kategorie[$kategoria]) && $kategorie[$kategoria]['nad'] == 'eBooki'){
    
    $ebooki = true;
    
}

if(array_key_exists($kategoria, $kategorie) && !empty($kategorie[$kategoria]['pod']) && !$ebooki) {
    echo '<ul class="helion_kategorie"><li>Subkategorie:</li>';
    foreach($kategorie[$kategoria]['pod'] as $k_id => $pod) {
        //if(key($pod) == $kategoria) {
            
            echo '<li><a href="' . JURI::current() . '?view=kategoria&id=' . $k_id . '">' . $pod . '</a></li>';
            $subkategorie[] = $k_id;
            
            # czy kategoria ebooki
        //    if($pod[key($pod)] == 'eBooki'){
        //        $ebooki = true;
        //    }
            
        //}
    }
    echo '</ul>';
}

if(!empty($subkategorie)) {
    
    if($ebooki){
        
        $query = "SELECT * FROM #__helion WHERE ksiegarnia = '" . $ksiegarnia . "' AND ident like '%_ebook' AND cena LIMIT 10";
        
    }else{
        
        $regexp = "," . join(",|,", $subkategorie) . ",";
        $query = "SELECT * FROM #__helion WHERE ksiegarnia = '" . $ksiegarnia . "' AND kategorie REGEXP '" . $regexp . "' AND cena LIMIT 10";
    
        
    }
    
} else {

    if($ebooki){

        $query = "SELECT * FROM #__helion WHERE ksiegarnia = '" . $ksiegarnia . "' AND ident like '%_ebook' AND cena";
        
    }else{
    
        $query = "SELECT * FROM #__helion WHERE ksiegarnia = '" . $ksiegarnia . "' AND kategorie LIKE '%," . $kategoria . ",%' AND cena";
        
    }
}

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
            <td rowspan="5" class="okladka">
                <a href="<?php echo $url; ?>" title="<?php echo $ksiazka['tytul']?>">
                    <img src="https://static01.helion.com.pl/global/okladki/125x163/<?php echo preg_replace('/\_ebook$/i', '', $ksiazka['ident']); ?>.jpg" />
                </a>
            </td>
            <td><h3><a href="<?php echo $url; ?>" title="<?php echo $ksiazka['tytul']?>"><?php echo $ksiazka['tytul']; ?></a></h3></td>
        </tr>
        <tr>
            <td class="autor"><b>Autor:</b> <?php echo $ksiazka['autor']; ?></td>
        </tr>
        <tr>
            <td class="format"><b>Format:</b> 
                <?php echo HelionHelper::getTypeByIdent($ksiazka['ident'])?>
            </td>
        </tr>
        <tr>
            <td class="pcena">
                <span class="cena"><b>Cena:</b> <?php echo $ksiazka['cena']; ?> zł</span> 
            <?php if($ksiazka['znizka'] > 0) echo ' <span class="znizka">(-' . $ksiazka['znizka'] . "%)</span>"; ?>
        </td>
        </tr>
        <tr>
            <td>
                <div class="helion-box">
                    <a href="<?php echo $koszyk; ?>" title="Dodaj '<?php echo $ksiazka['tytul']; ?>' do koszyka" rel="nofollow" target="_blank">Kup teraz</a>
                </div>
            </td>
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
        echo '<p>Brak danych o kategoriach.</p>';
    }
} else {
    echo "<h1>Niepoprawna wartość parametru kategoria.</h1>";
}
?>

<p><a href="<?php echo JURI::current(); ?>">Powrót do strony głównej księgarni</a></p>