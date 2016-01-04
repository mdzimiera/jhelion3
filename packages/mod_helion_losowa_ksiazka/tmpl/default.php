<?php // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 
$db = JFactory::getDbo();

// dodaj css
$document = JFactory::getDocument();
$document->addStyleSheet('modules'.DIRECTORY_SEPARATOR.'mod_helion_losowa_ksiazka'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'mod_helion_losowa_ksiazka.css');

$helion = $params->get('helion');
$onepress = $params->get('onepress');
$sensus = $params->get('sensus');
$septem = $params->get('septem');
$ebookpoint = $params->get('ebookpoint');
$bezdroza = $params->get('bezdroza');
$videopoint = $params->get('videopoint');

$okladka = $params->get('okladka');
$szerokosc = explode("x", $okladka);
$partner_id = $params->get('partner');
$cyfra = $params->get('cyfra');
$czy_autor = $params->get('czy_autor');
$czy_znizka = $params->get('czy_znizka');
$czy_marka = $params->get('czy_marka');
$czy_status = $params->get('czy_status');
$czy_typ = $params->get('czy_typ');
$czy_ean = $params->get('czy_ean');
$czy_isbn = $params->get('czy_isbn');
$czy_cena = $params->get('czy_cena');

if(!empty($helion))
    $ksiegarnie[] = "helion";

if(!empty($onepress))
    $ksiegarnie[] = "onepress";

if(!empty($sensus))
    $ksiegarnie[] = "sensus";
	
if(!empty($septem))
    $ksiegarnie[] = "septem";
	
if(!empty($ebookpoint))
    $ksiegarnie[] = "ebookpoint";

if(!empty($bezdroza))
    $ksiegarnie[] = "bezdroza";

if(!empty($videopoint))
    $ksiegarnie[] = "videopoint";
	
if(!empty($ksiegarnie)) {
	$ksiegarnia = $ksiegarnie[array_rand($ksiegarnie)];
	
	if(strstr(${$ksiegarnia}, " ")) {
		$identy = explode(" ", ${$ksiegarnia});
	} else if (strstr(${$ksiegarnia}, ";")) {
		$identy = explode(";", ${$ksiegarnia});
	} else if (strstr(${$ksiegarnia}, ",")) {
		$identy = explode(",", ${$ksiegarnia});
	} else {
		echo '<p>Nieprawidłowy format identyfikatorów książek podany podczas konfiguracji.</p>';
		return false;
	}
        
        $identy = array_filter($identy);
        
	$ident = $identy[array_rand($identy)];
        
        switch($ksiegarnia){
            case 'videopoint':
                $ident .= !preg_match('/\_w$/i', $ident) ? '_w' : '';
                break;
        default:
            break;
        }
        
        $typ = modHelionLosowaKsiazkaHelper::getTypeByIdent($ident);
	
	$external = "http://" . $ksiegarnia . ".pl/plugins/new/xml/ksiazka.cgi?ident=" . $ident;
		
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_URL, $external);
	$out = curl_exec($ch);
	curl_close($ch);

	$xml = simplexml_load_string($out);
		
	$tytul = $xml->tytul[0];
	$cenadetaliczna = $xml->cenadetaliczna;
	$autor = $xml->autor;
        $cart_status = $xml->status;
	$status = modHelionLosowaKsiazkaHelper::getStatus($xml->status);
        $znizka = $xml->znizka;
        $cena_po_rabacie = $xml->cena;
        $isbn = $xml->isbn;
        $ean = modHelionLosowaKsiazkaHelper::ISBNtoEAN($isbn);
        $marka = modHelionLosowaKsiazkaHelper::getBrand($xml->brand);

	$url = 'http://' . $ksiegarnia . '.pl/view/' . $partner_id . '/' . (($cyfra) ? $cyfra.'/' : '') . $ident . '.htm';
	$dokoszyka = 'http://' . $ksiegarnia . '.pl/add/' . $partner_id . '/' . (($cyfra) ? $cyfra.'/' : '') . $ident . '.htm';

	} else {
		$query = "SELECT * FROM #__helion WHERE cena ORDER BY RAND() LIMIT 1;";
		$db->setQuery($query);
		$ksiazka = $db->loadAssoc();

		if(!empty($ksiazka)) {
			$tytul = $ksiazka['tytul'];
			$cenadetaliczna = $ksiazka['cenadetaliczna'];
			$autor = $ksiazka['autor'];
                        $cart_status = $ksiazka['status'];
			$status = modHelionLosowaKsiazkaHelper::getStatus($ksiazka['status']);
			$ident = $ksiazka['ident'];
			$ksiegarnia = $ksiazka['ksiegarnia'];
                        $znizka = $ksiazka['znizka'];
                        $cena_po_rabacie = $ksiazka['cena'];
                        $ean = modHelionLosowaKsiazkaHelper::ISBNtoEAN($ksiazka['isbn']);
                        $isbn = $ksiazka['isbn'];
                        $marka = modHelionLosowaKsiazkaHelper::getBrand($ksiazka['marka']);
		} else {
			echo '<p>Nie udało się pobrać danych o książce.</p>';
			return false;
		}

		$url = 'http://' . $ksiegarnia . '.pl/view/' . $partner_id . '/' . (($cyfra) ? $cyfra.'/' : '') . $ident . '.htm';
		$dokoszyka = 'http://' . $ksiegarnia . '.pl/add/' . $partner_id . '/' . (($cyfra) ? $cyfra.'/' : '') . $ident . '.htm';

	}
?>

<div class="helion_losowa_ksiazka">
    <div class="info">
        <h4 class="tytul"><a href="<?php echo $url; ?>" title="<?php echo $tytul?>"><?php echo $tytul ?></a></h4>
    </div>
    <div class="okladka" style="width: <?php echo (is_array($szerokosc) && isset($szerokosc[0])) ? $szerokosc[0] : $szerokosc; ?>px;">
        <a href="<?php echo $url; ?>" target="_blank" title="<?php echo $tytul?>"><img src="http://<?php echo $ksiegarnia; ?>.pl/okladki/<?php echo $okladka; ?>/<?php echo $ident; ?>.jpg" alt="<?php echo $tytul; ?>" /></a>
    </div>
    <div class="info">
        <ul>
            <?php if($czy_autor):?><li class="autor"><b>Autor:</b> <?php echo $autor; ?></li><?php endif;?>
            <?php if($czy_znizka && $znizka > 0):?><li class="znizka"><b>Zniżka:</b> <?php echo $znizka; ?>%</li><?php endif;?>
            <?php if($czy_cena):?><li class="cena"><b>Cena:</b> 
                <?php if($znizka > 0):?>
                    <strike><?php echo $cenadetaliczna; ?></strike> <b><?php echo $cena_po_rabacie?> zł</b></li>
                <?php else:?>    
                    <?php echo $cenadetaliczna; ?> zł
                <?php endif;?>
            <?php endif;?>
            <?php if($czy_marka):?><li class="marka"><b>Marka:</b> <?php echo $marka; ?></li><?php endif;?>
            <?php if($czy_status):?><li class="status"><b>Status:</b> <?php echo $status; ?></li><?php endif;?>
            <?php if($czy_typ):?><li class="typ"><b>Typ:</b> <?php echo $typ; ?></li><?php endif;?>
            <?php if($czy_ean):?><li class="ean"><b>EAN:</b> <?php echo $ean; ?></li><?php endif;?>
            <?php if($czy_isbn):?><li class="isbn"><b>ISBN:</b> <?php echo $isbn; ?></li><?php endif;?>
        </ul>
    </div>
    <?php if($cart_status != 0) { ?>
    <div class="dokoszyka">
        <a href="<?php echo $dokoszyka; ?>" target="_blank"><img src="http://helion.pl/img/koszyk/koszszary.jpg" alt="Dodaj <?php echo $tytul; ?> do koszyka" /></a>
    </div>
    <?php } else { ?>
    <p>Książka chwilowo niedostępna.</p>
    <?php } ?>
</div>