<?php // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 
$db =& JFactory::getDbo();

// dodaj css
$document = JFactory::getDocument();
$document->addStyleSheet('modules'.DIRECTORY_SEPARATOR.'mod_helion_bestsellery'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'mod_helion_bestsellery.css');

$ksiegarnia = $params->get('ksiegarnia');
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

$external = "http://" . $ksiegarnia . ".pl/plugins/new/xml/top.cgi";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_URL, $external);
$out = curl_exec($ch);
curl_close($ch);

$xml = simplexml_load_string($out);

$b = array();

foreach($xml->PRODUKT as $produkt) {
	$b[] = strtolower($produkt->attributes()->ID);
}

$ident = $b[array_rand($b)];

//$query = "SELECT * FROM #__helion WHERE ksiegarnia = '" . $ksiegarnia . "' AND ident = '" . $ident . "';";
//$db->setQuery($query);
//$ksiazka = $db->loadAssoc();

$external = "http://" . $ksiegarnia . ".pl/plugins/new/xml/ksiazka.cgi?ident=" . $ident;
    
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_URL, $external);
$out = curl_exec($ch);
curl_close($ch);

$xml = simplexml_load_string($out);
    
$tytul = $xml->tytul[0];
$cena_po_rabacie = $xml->cena;
$autor = $xml->autor;
$cart_status = $xml->status;
$status = modHelionBestselleryHelper::getStatus($xml->status);
$znizka = $xml->znizka;
$cenadetaliczna = $xml->cenadetaliczna;
$isbn = $xml->isbn;
$ean = modHelionBestselleryHelper::ISBNtoEAN($isbn);
$marka = modHelionBestselleryHelper::getBrand($xml->brand);
$typ = modHelionBestselleryHelper::getTypeByIdent($ident);
        
$url = 'http://' . $ksiegarnia . '.pl/view/' . $partner_id . '/' . (($cyfra) ? $cyfra . '/' : '') . $ident . '.htm';
$dokoszyka = 'http://' . $ksiegarnia . '.pl/add/' . $partner_id . '/' . (($cyfra) ? $cyfra . '/' : '') . $ident . '.htm';

?>

<div class="helion_bestseller">
    <div class="info">
        <h4 class="tytul"><a href="<?php echo $url; ?>" title="<?php echo $tytul?>"><?php echo $tytul ?></a></h4>
    </div>
    <div class="okladka" style="width: <?php echo $szerokosc; ?>px;">
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