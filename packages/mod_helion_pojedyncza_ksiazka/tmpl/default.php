<?php // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

// dodaj css
$document = JFactory::getDocument();
$document->addStyleSheet('modules'.DIRECTORY_SEPARATOR.'mod_helion_pojedyncza_ksiazka'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'mod_helion_pojedyncza_ksiazka.css');

$ksiegarnia = $params->get('ksiegarnia');
$ident = $params->get('ident');
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

$db = JFactory::getDbo();

switch($ksiegarnia){
    case 'videopoint':
        $ident .= !preg_match('/\_w$/i', $ident) ? '_w' : '';
        break;
default:
    break;
}

$tytul = $cena_po_rabacie = $autor = $cart_status = 
        $status = $znizka = $cenadetaliczna = 
        $isbn = $ean = $marka = $typ = $xml = null;

$external = "http://" . $ksiegarnia . ".pl/plugins/new/xml/ksiazka.cgi?ident=" . $ident;
    
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_URL, $external);
$out = curl_exec($ch);
curl_close($ch);

if(!preg_match("/^nie znaleziono/i", $out)){
    $xml = simplexml_load_string($out);

    if($xml){
        
        $tytul = $xml->tytul[0];
        $cena_po_rabacie = $xml->cena;
        $autor = $xml->autor;
        $cart_status = $xml->status;
        $status = modHelionPojedynczaKsiazkaHelper::getStatus($xml->status);
        $znizka = $xml->znizka;
        $cenadetaliczna = $xml->cenadetaliczna;
        $isbn = $xml->isbn;
        $ean = modHelionPojedynczaKsiazkaHelper::ISBNtoEAN($isbn);
        $marka = modHelionPojedynczaKsiazkaHelper::getBrand($xml->brand);
        $typ = modHelionPojedynczaKsiazkaHelper::getTypeByIdent($ident);
        
    }

    $url = 'http://' . $ksiegarnia . '.pl/view/' . $partner_id . '/' . (($cyfra) ? $cyfra . '/' : '') . $ident . '.htm';
    $dokoszyka = 'http://' . $ksiegarnia . '.pl/add/' . $partner_id . '/' . (($cyfra) ? $cyfra . '/' : '') . $ident . '.htm';

    ?>

    <div class="helion_pojedyncza_ksiazka">
        <div class="info">
            <h4 class="tytul"><a href="<?php echo $url; ?>" title="<?php echo $tytul?>"><?php echo $tytul ?></a></h4>
        </div>
        <div class="okladka" style="width: <?php echo (is_array($szerokosc) && isset($szerokosc[0])) ? $szerokosc[0] : $szerokosc; ?>px;">
            <a href="<?php echo $url; ?>" target="_blank" title="<?php echo $tytul?>">
                <img src="https://static01.helion.com.pl/global/okladki/<?php echo $okladka; ?>/<?php echo $ident; ?>.jpg" alt="<?php echo $tytul; ?>" />
            </a>
        </div>
        <div class="info">
            <ul>
                <?php if($czy_autor):?>
                    <li class="autor"><b>Autor:</b> 
                        <span class="help-block"><?php echo $autor; ?></span>
                    </li>
                <?php endif;?>
                <?php if($czy_znizka && $znizka > 0):?>
                    <li class="znizka"><b>Zniżka:</b> 
                        <span class="help-block"><?php echo $znizka; ?>%</span>
                    </li>
                <?php endif;?>
                <?php if($czy_cena):?>
                    <li class="cena"><b>Cena:</b> 
                        <span class="help-block">
                            <?php if($znizka > 0):?>
                                <strike><?php echo $cenadetaliczna; ?></strike> <b><?php echo $cena_po_rabacie?> zł</b>
                            <?php else:?>    
                                <?php echo $cenadetaliczna; ?> zł
                            <?php endif;?>
                        </span>
                    </li>
                <?php endif;?>
                <?php if($czy_marka):?>
                    <li class="marka"><b>Marka:</b> 
                        <span class="help-block"><?php echo $marka; ?></span>
                    </li>
                <?php endif;?>
                <?php if($czy_status):?>
                    <li class="status"><b>Status:</b> 
                        <span class="help-block"><?php echo $status; ?></span>
                    </li>
                <?php endif;?>
                <?php if($czy_typ):?>
                    <li class="typ"><b>Typ:</b> 
                        <span class="help-block"><?php echo $typ; ?></span>
                    </li>
                <?php endif;?>
                <?php if($czy_ean):?>
                    <li class="ean"><b>EAN:</b> 
                        <span class="help-block"><?php echo $ean; ?></span>
                    </li>
                <?php endif;?>
                <?php if($czy_isbn):?>
                    <li class="isbn"><b>ISBN:</b> 
                        <span class="help-block"><?php echo $isbn; ?></span>
                    </li>
                <?php endif;?>
            </ul>
        </div>
        <?php if($cart_status != 0) { ?>
        <div class="helion-box">
            <a href="<?php echo $dokoszyka; ?>" title="Dodaj <?php echo $tytul; ?> do koszyka" rel="nofollow" target="_blank">Kup teraz</a>
        </div>
        <?php } else { ?>
        <p>Książka chwilowo niedostępna.</p>
        <?php } ?>
    </div>
<?php }else{?>
    <div class="helion_pojedyncza_ksiazka">
        <div class="dokoszyka">
            <p>Nieprawidłowy identyfikator pozycji.</p>
        </div>
    </div>
<?php }?>