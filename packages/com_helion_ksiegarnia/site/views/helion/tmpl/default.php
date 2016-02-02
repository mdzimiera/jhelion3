<?php

/**
 * @todo spis tresci
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$db = JFactory::getDbo();

$cyfra = "10";

$query = "SELECT value FROM #__helion_config WHERE meta = 'ksiegarnia'";
$db->setQuery($query);
$ksiegarnia = $db->loadResult();

$query = "SELECT value FROM #__helion_config WHERE meta = 'partner_id'";
$db->setQuery($query);
$partner_id = $db->loadResult();

$query = "SELECT update_time FROM #__helion_status WHERE ksiegarnia = " . $db->quote($ksiegarnia) . ";";
$db->setQuery($query);
$last_update = $db->loadResult();

if(!last_update || $last_update <= (time() - 86400)) {
    $query = "DELETE FROM #__helion WHERE ksiegarnia = " . $db->quote($ksiegarnia) . ";";
    $db->setQuery($query);
    $db->query();
    
	
    $external = "http://" . $ksiegarnia . ".pl/xml/produkty-" . $ksiegarnia . ".xml";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $external);
    $out = curl_exec($ch);
    curl_close($ch);
    
    /*
    $spisy = "http://" . $ksiegarnia . ".pl/xml/spisy.xml";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $spisy);
    $sp = curl_exec($ch);
    curl_close($ch);

    $toc = simplexml_load_string($sp);
    */
    if(($xml = simplexml_load_string($out, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE)) !== false){
        foreach($xml->lista->ksiazka as $ksiazka) {
            $ks = new stdClass();
            $ks->id = null;
            $ks->ksiegarnia = $ksiegarnia;
            $ks->ident = strtolower($ksiazka->ident);
            $ks->isbn = (string) $ksiazka->isbn;
            $ks->link = (string) $ksiazka->link;
            $ks->autor = (string) $ksiazka->autor;
            $ks->tlumacz = (string) $ksiazka->tlumacz;
            $ks->status = (string) $ksiazka->status;
            $ks->cena = (string) $ksiazka->cena;
            $ks->cenadetaliczna = (string) $ksiazka->cenadetaliczna;
            $ks->znizka = (string) $ksiazka->znizka;
            $ks->marka = (string) $ksiazka->marka;
            $ks->nazadanie = (string) $ksiazka->nazadanie;
            $ks->format = (string) $ksiazka->format;
            $ks->liczbastron = (string) $ksiazka->liczbastron;
            $ks->oprawa = (string) $ksiazka->oprawa;
            $ks->bestseller = (string) $ksiazka->bestseller;
            $ks->nowosc = (string) $ksiazka->nowosc;
            $ks->opis = (string) $ksiazka->opis;
            $ks->datawydania = (string) $ksiazka->datawydania;
            /*
            foreach($toc->lista->ksiazka as $t){

                if(strtolower($t->ident) == strtolower($ksiazka->ident)){
                    $ks->spis_tresci = (string) $t->spis;
                    break;
                }

            }*/

            foreach($ksiazka->tytul as $tytul) {
                if($tytul->attributes()->language == "polski") {
                    $ks->tytul = (string) $tytul;
                } else {
                    $ks->tytul_orig = (string) $tytul;
                }
            }

            $kategorie = array();
            $ids = array();

            if($ksiazka->serietematyczne->seriatematyczna){
                foreach($ksiazka->serietematyczne->seriatematyczna as $kategoria) {
                    $ids[] = (int) $kategoria->attributes()->id;
                }
            }

            $ks->kategorie = "," . join(",", $ids) . ",";

            $db->insertObject('#__helion', $ks, 'id');
        }
    }
    
    $query = "UPDATE #__helion_status SET update_time = " .$db->quote(time()) . " WHERE ksiegarnia = " . $db->quote($ksiegarnia) . ";";
    $db->setQuery($query);
    $db->query();
}

$query = "SELECT update_time FROM #__helion_status WHERE ksiegarnia = " . $db->quote($ksiegarnia . '_kategorie') . ";";
$db->setQuery($query);
$last_update = $db->loadResult();

if(!last_update || $last_update <= (time() - 86400)) {
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
    
    $lista = array("nad" => array(), "pod" => array());
    
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

?>
<h1>Księgarnia <?php echo ucfirst($ksiegarnia); ?></h1>
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
$query = "SELECT value FROM #__helion_config WHERE meta = 'kategorie_w_tresci'";
$db->setQuery($query);
$kategorie_w_tresci = $db->loadResult();

if($kategorie_w_tresci) {
    $query = "SELECT value FROM #__helion_config WHERE meta = " . $db->quote($ksiegarnia . '_kategorie') . ";";
    $db->setQuery($query);
    $kategorie = $db->loadResult();
    $kategorie = unserialize($kategorie);

    if(!empty($kategorie)) {
        $nadrzedne = $kategorie['nad'];

        echo '<ul class="helion_kategorie"><li>Kategorie:</li>';

        foreach($nadrzedne as $id => $nazwa) {
            echo '<li><a href="' . JURI::current() . "?view=kategoria&id=" . $id . '">' . $nazwa . '</a></li>';
        }

        echo '<li><a href="' . JURI::current() . "?view=nowosci" . '">Nowości</a></li>';
        echo '<li><a href="' . JURI::current() . "?view=bestsellery" . '">Bestsellery</a></li>';
        echo '</ul>';
    } else {
        echo '<p>Dane dotyczące kategorii nie są dostępne. </p>';
    }
}
?>

<div>
<h2>Bestsellery</h2>
<ul class="bestsellers">
<?php 
$query = "SELECT * FROM #__helion WHERE ksiegarnia = '" . $ksiegarnia . "' AND bestseller = '1' AND cena LIMIT 4";
$db->setQuery($query);
$result = $db->loadAssocList();

foreach($result as $ksiazka) {
    $url = JURI::current() . "?view=ksiazka&ident=" . $ksiazka['ident'] . "&ksiegarnia=" . $ksiazka['ksiegarnia'];
    $koszyk = "http://" . $ksiazka['ksiegarnia'] . ".pl/add/" . $partner_id . "/" . $cyfra . "/" . $ksiazka['ident'];
	
	if(preg_match("/_ebook$/", $ksiazka['ident'])) {
		$temp_ident = explode("_ebook", $ksiazka['ident']);
		$ident = $temp_ident[0];
	} else {
		$ident = $ksiazka['ident'];
	}
?>
    <li>    
        <div class="helion_ksiazka">
            <a href="<?php echo $url; ?>" title="<?php echo $ksiazka['title']?>"><img src="http://helion.pl/okladki/90x119/<?php echo preg_replace('/\_ebook$/i', '', $ident); ?>.jpg" /></a>
            <div class="ksiazka_info">
                <h3><a href="<?php echo $url; ?>" title="<?php echo $ksiazka['tytul']?>"><?php echo $this->trunc($ksiazka['tytul'], 5); ?></a></h3>
                <p class="autor">Autor: <?php $a = explode(",", $ksiazka['autor']); if(count($a) == 1) { echo $a[0]; } else { echo $a[0] . " i in."; }; ?></p>
                <p>
                    <span class="cena">Cena: <?php echo $ksiazka['cena']; ?> zł</span> 
                    <?php if($ksiazka['znizka'] > 0) echo ' <span class="znizka">(-' . $ksiazka['znizka'] . "%)</span>"; ?>
                </p>
                <p class="kupteraz"><a href="<?php echo $koszyk; ?>" target="_blank"><img src="http://helion.pl/img/koszyk/koszszary.jpg"/></a></p>
            </div>
        </div>
    </li>
<?php
}
?>
</ul>
<p><a href="<?php echo JURI::current() . "?view=bestsellery"; ?>" title="Wszystkie bestsellery">Wszystkie bestsellery</a></p>
</div>


<div>
<h2>Nowości</h2>
<ul class="novelties">
<?php 
$query = "SELECT * FROM #__helion WHERE ksiegarnia = '" . $ksiegarnia . "' AND nowosc = '1' AND cena LIMIT 4";
$db->setQuery($query);
$result = $db->loadAssocList();

foreach($result as $ksiazka) {
    $url = JURI::current() . "?view=ksiazka&ident=" . $ksiazka['ident'] . "&ksiegarnia=" . $ksiazka['ksiegarnia'];
    $koszyk = "http://" . $ksiazka['ksiegarnia'] . ".pl/add/" . $partner_id . "/" . $cyfra . "/" . $ksiazka['ident'];
	
	if(preg_match("/_ebook$/", $ksiazka['ident'])) {
		$temp_ident = explode("_ebook", $ksiazka['ident']);
		$ident = $temp_ident[0];
	} else {
		$ident = $ksiazka['ident'];
	}
?>
    <li>
        <div class="helion_ksiazka">
            <a href="<?php echo $url; ?>"><img src="http://helion.pl/okladki/90x119/<?php echo preg_replace('/\_ebook$/i', '', $ident); ?>.jpg" /></a>
            <div class="ksiazka_info">
                <h3><a href="<?php echo $url; ?>" title="<?php echo $ksiazka['tytul']?>"><?php echo $this->trunc($ksiazka['tytul'], 5); ?></a></h3>
                <p class="autor">Autor: <?php $a = explode(",", $ksiazka['autor']); if($a == $ksiazka['autor']) { echo $a; } else { echo $a[0] . " i in."; }; ?></p>
                <p>
                    <span class="cena">Cena: <?php echo $ksiazka['cena']; ?> zł</span> 
                    <?php if($ksiazka['znizka'] > 0) echo ' <span class="znizka">(-' . $ksiazka['znizka'] . "%)</span>"; ?>
                </p>
                <p class="kupteraz"><a href="<?php echo $koszyk; ?>" target="_blank"><img src="http://helion.pl/img/koszyk/koszszary.jpg"/></a></p>
            </div>
        </div>
    </li>
<?php
}
?>

<p><a href="<?php echo JURI::current() . "?view=nowosci"; ?>" title="Wszystkie nowości">Wszystkie nowości</a></p>
</div>
