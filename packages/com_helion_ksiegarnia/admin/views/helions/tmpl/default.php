<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

JToolBarHelper::title(   JText::_( 'Helion - Program Partnerski' ), 'generic.png' );

$db =& JFactory::getDBO();

switch(JRequest::getVar('action')) {
    case 'save':
        $partner_id = JRequest::getString('partner_id');
        $ksiegarnia = JRequest::getString('ksiegarnia');
        $kategorie_w_tresci = JRequest::getString('kategorie_w_tresci');
        $wyszukiwarka_w_tresci = JRequest::getString('wyszukiwarka_w_tresci');
        
        // walidatory
        $query = "DELETE FROM #__helion_config WHERE meta = 'partner_id';";
        $db->setQuery($query);
        $db->query();
        
        $query = "INSERT INTO #__helion_config (meta, value) VALUES ('partner_id', '" . $partner_id . "');";
        $db->setQuery($query);
        $db->query();
        
        $query = "DELETE FROM #__helion_config WHERE meta = 'ksiegarnia';";
        $db->setQuery($query);
        $db->query();
        
        $query = "INSERT INTO #__helion_config (meta, value) VALUES ('ksiegarnia', '" . $ksiegarnia . "');";
        $db->setQuery($query);
        $db->query();
		
	$query = "DELETE FROM #__helion_config WHERE meta = 'kategorie_w_tresci';";
        $db->setQuery($query);
        $db->query();
        
        $query = "INSERT INTO #__helion_config (meta, value) VALUES ('kategorie_w_tresci', '" . $kategorie_w_tresci . "');";
        $db->setQuery($query);
        $db->query();
        
        $query = "DELETE FROM #__helion_config WHERE meta = 'wyszukiwarka_w_tresci';";
        $db->setQuery($query);
        $db->query();
        
        $query = "INSERT INTO #__helion_config (meta, value) VALUES ('wyszukiwarka_w_tresci', '" . $wyszukiwarka_w_tresci . "');";
        $db->setQuery($query);
        $db->query();
        
        /**
         * start - dodaj na nowo pozycje
         */
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

        $xml = simplexml_load_string($out);

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

            foreach($ksiazka->tytul as $tytul) {
                if($tytul->attributes()->language == "polski") {
                    $ks->tytul = (string) $tytul;
                } else {
                    $ks->tytul_orig = (string) $tytul;
                }
            }

            $kategorie = array();
            $ids = array();

            foreach($ksiazka->serietematyczne->seriatematyczna as $kategoria) {
                $ids[] = (int) $kategoria->attributes()->id;
            }

            $ks->kategorie = "," . join(",", $ids) . ",";

            $db->insertObject('#__helion', $ks, 'id');
        }

        $query = "UPDATE #__helion_status SET update_time = " .$db->quote(time()) . " WHERE ksiegarnia = " . $db->quote($ksiegarnia) . ";";
        $db->setQuery($query);
        $db->query();
        /**
         * end - dodaj na nowo pozycje
         */
        
        /**
         * start - dodaj kategorie
         */
        $query = "DELETE FROM #__helion_config WHERE meta = ".$db->quote($ksiegarnia.'_kategorie').";";
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
        
        /**
         * end - dodaj kategorie
         */
        
        break;
    case 'reset':
        
        break;
    default:
        break;
}

$query = "SELECT value FROM #__helion_config WHERE meta = 'ksiegarnia'";
$db->setQuery($query);
$ksiegarnia = $db->loadResult();

$query = "SELECT value FROM #__helion_config WHERE meta = 'partner_id'";
$db->setQuery($query);
$partner_id = $db->loadResult();

$query = "SELECT value FROM #__helion_config WHERE meta = 'kategorie_w_tresci'";
$db->setQuery($query);
$kategorie_w_tresci = $db->loadResult();

$query = "SELECT value FROM #__helion_config WHERE meta = 'wyszukiwarka_w_tresci'";
$db->setQuery($query);
$wyszukiwarka_w_tresci = $db->loadResult();

?>
<style type="text/css">
    input.helion_sb {
        background: #21759B; 
        border-color: #298CBA; 
        color: #FFFFFF; 
        font-weight: bold; 
        -webkit-border-radius: 4px; 
        -moz-border-radius: 4px; 
        border-radius: 4px; 
        letter-spacing: 1px; 
        font-size: 12px; 
        padding: 6px 12px;
    }
    
    input.helion_sb:hover { 
        cursor: pointer;
        background: #fff;
        color: #21759B;
    }
</style>
<form action="<?php echo JURI::current(); ?>" method="get">
    <table class="adminlist">
        <tr>
            <td>Numer partnera Programu Partnerskiego:</td>
            <td><input type="text" name="partner_id" value="<?php echo $partner_id; ?>" /></td>
        </tr>
        <tr>
            <td>Księgarnia do skopiowania:</td>
            <td>
                <select name="ksiegarnia">
                    <option value="helion" <?php if($ksiegarnia == 'helion') echo 'selected="selected"'; ?>>Helion</option>
                    <option value="onepress" <?php if($ksiegarnia == 'onepress') echo 'selected="selected"'; ?>>Onepress</option>
                    <option value="sensus" <?php if($ksiegarnia == 'sensus') echo 'selected="selected"'; ?>>Sensus</option>
                    <option value="septem" <?php if($ksiegarnia == 'septem') echo 'selected="selected"'; ?>>Septem</option>
                    <option value="ebookpoint" <?php if($ksiegarnia == 'ebookpoint') echo 'selected="selected"'; ?>>Ebookpoint</option>
                    <option value="bezdroza" <?php if($ksiegarnia == 'bezdroza') echo 'selected="selected"'; ?>>Bezdroża</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Wyświetlać kategorie w treści księgarni?</td>
            <td><input type="checkbox" name="kategorie_w_tresci" <?php if($kategorie_w_tresci) echo 'checked="checked"'; ?> /></td>
        </tr>
        <tr>
            <td>Wyświetlać wyszukiwarkę w treści księgarni?</td>
            <td><input type="checkbox" name="wyszukiwarka_w_tresci" <?php if($wyszukiwarka_w_tresci) echo 'checked="checked"'; ?> /></td>
        </tr>
    </table>
    <br/>
    <p><input type="submit" value="Zapisz ustawienia" class="helion_sb" /></p>
    <input type="hidden" name="option" value="com_helion" />
    <input type="hidden" name="action" value="save" />
</form>

<h4>Instrukcja obsługi Księgarni Programu Partnerskiego</h4>

<p>Komponent PP Helion należy dodać do jednego z menu na stronie (najlepiej wybrać widok domyślny).</p>
<p>Numer partnera to numer w formacie <code>1234a</code>, przydzielony każdej osobie przystępującej do programu. Jeżeli nie masz jeszcze takego numeru, przejdź na <a href="http://program-partnerski.helion.pl/" target="_blank">stronę Programu Partnerskiego Helion</a> i zarejestruj się.</p>
<p>Możesz zainstalować osobny moduł mod_helion_kategorie, który umożliwia wyświetlanie listy kategorii np. na pasku bocznym. Warto wtedy wyłączyć wyświetlanie kategorii w treści.</p>
<p>Więcej informacji na temat Programu Partnerskiego znajdziesz na <a href="http://program-partnerski.helion.pl/" target="_blank">oficjalnej stronie</a></p>.
<p>Zauważyłeś błąd? Chcesz zgłosić problem? Masz propozycję nowej funkcjonalności dla rozszerzenia? Napisz nam o tym na <a href="http://program-partnerski.helion.pl/forum/" target="_blank">Forum Programu Partnerskiego</a>!</p>
