<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$db =& JFactory::getDBO();

$cyfra = "82";

$query = "SELECT value FROM #__helion_config WHERE meta = 'partner_id'";
$db->setQuery($query);
$partner_id = $db->loadResult();

$ksiegarnia = JRequest::getString('ksiegarnia');
$ident = JRequest::getString('ident');

if(!$ksiegarnia || !$ident) {
    echo "<p>Nie podano żadnych parametrów określających książkę.</p>";
    return false;
}

$query = "SELECT * FROM #__helion WHERE ksiegarnia = '" . $ksiegarnia . "' AND ident = '" . $ident . "'";
$db->setQuery($query);
$ksiazki = $db->loadAssocList();

$ksiazka = $ksiazki[0];

if(!$ksiazka) {
    echo "<p>Nie można było pobrać danych na temat tej książki.</p>";
    return false;
}

$url = "http://" . $ksiegarnia . ".pl/add/" . $partner_id . "/" . $cyfra . "/" . $ksiazka['ident'];

if($ksiazka['nowosc'] == "1") {
    $nowosc = '<img alt="Nowość" src="http://helion.pl/img/nowosc.gif">';
}

if($ksiazka['bestseller'] == "1") {
    $bestseller = '<img alt="Bestseller" src="http://helion.pl/img/bestseller.gif">';
}

?>
<h1><?php echo $ksiazka['autor']; ?> - <?php echo $ksiazka['tytul']; ?></h1>
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
<?php } ?>

<div class="helion_ksiazka">
    <a href="<?php echo $url; ?>" title="<?php echo $ksiazka['tytul']?>"><img src="http://helion.pl/okladki/181x236/<?php echo preg_replace('/\_ebook$/i', '', $ksiazka['ident']); ?>.jpg" /></a>
    <div class="ksiazka_info">
        <h3 class="tytul"><a href="<?php echo $url; ?>" title="<?php echo $ksiazka['tytul']?>"><?php echo $ksiazka['tytul']; ?></a></h3>
        <p class="autor">Autor: <strong><?php echo $ksiazka['autor']; ?></strong></p>
        <p class="format">Format: <?php if(preg_match('/\_ebook$/i', $ksiazka['ident'])):?>eBook<?php else:?>Druk<?php endif?></p>
        <p class="datawydania">Data wydania: <?php echo $ksiazka['datawydania']; ?></p>
        <p class="stron">Stron: <?php echo $ksiazka['liczbastron']; ?></p>
        <p class="dostawa">Dostawa: 0,00 zł</p>
        <p class="wysylka">Wysyłka w 24h</p>
        <p class="nowosc_bestseller"><?php echo $nowosc . " " . $bestseller; ?></p>
    </div>
    <?php if($ksiazka['status'] != "0") { ?>
    <div class="helion_kup_teraz">
        <p>
            <span class="cena">Cena: <?php echo $ksiazka['cena']; ?> zł</span> 
            <?php if($ksiazka['znizka'] > 0) echo '<br /><span class="znizka">Zniżka: ' . $ksiazka['znizka'] . "%</span>"; ?>
        </p>
        <p><a href="<?php echo $url; ?>" target="_blank"><img src="http://helion.pl/img/koszyk/koszszary.jpg"/></a></p>
    </div>
    <?php } else { ?>
        <div class="helion_kup_teraz">
            <p class="niedostepna">Książka chwilowo niedostępna.</p>
        </div>
    <?php } ?>
    <br /><br />
    <?php echo JHtml::_('bootstrap.startTabSet', 'ID-Tabs-J31-Group', array('active' => 'tab1_j31_id'));?> 

        <?php echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'tab1_j31_id', JText::_('Opis książki')); ?> 
            <?php echo $ksiazka['opis']; ?>
            <?php if($ksiazka['status'] != "0") { ?>
            <div class="helion_kup_teraz">
                <p>
                    <span class="cena">Cena: <?php echo $ksiazka['cena']; ?> zł</span> 
                    <?php if($ksiazka['znizka'] > 0) echo '<br /><span class="znizka">Zniżka: ' . $ksiazka['znizka'] . "%</span>"; ?>
                </p>
                <p><a href="<?php echo $url; ?>" target="_blank"><img src="http://helion.pl/img/koszyk/koszszary.jpg"/></a></p>
            </div>
            <?php } else { ?>
                <div class="helion_kup_teraz">
                    <p class="niedostepna">Książka chwilowo niedostępna.</p>
                </div>
            <?php } ?>
        <?php echo JHtml::_('bootstrap.endTab');?> 
    
        <?php echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'tab2_j31_id', JText::_('Szczegóły')); ?> 
            <ul class="helion_details">
                <?php if($ksiazka['tytul_orig']):?><li><b>Tytuł oryginału:</b> <?php echo $ksiazka['tytul_orig']?></li><?php endif;?>
                <?php if($ksiazka['tlumacz']):?><li><b>Tłumaczenie:</b> <?php echo $ksiazka['tlumacz']?></li><?php endif;?>
                <?php if($ksiazka['isbn']):?><li><b>Isbn <?php if(preg_match('/\_ebook/i', $ksiazka['ident'])):?>ebooka<?php else:?>książki drukowanej<?php endif;?>:</b> <?php echo $ksiazka['isbn']?></li><?php endif;?>
                <?php if($ksiazka['datawydania']):?><li><b>Data wydania <?php if(preg_match('/\_ebook/i', $ksiazka['ident'])):?>ebooka<?php else:?>książki drukowanej<?php endif;?>:</b> <?php echo $ksiazka['datawydania']?></li><?php endif;?>
                <li><b>Czytaj:</b> <a href="http://<?php echo $ksiegarnia?>.pl/ksiazki/<?php echo str_replace('_ebook', '', $ksiazka['ident'])?>.htm#spis-tresci" target="_blank" title="">spis treści</a></li>
            </ul>
        <?php echo JHtml::_('bootstrap.endTab');?> 
    <?php echo JHtml::_('bootstrap.endTabSet');?>
      
</div>

<p><a href="<?php echo JURI::current(); ?>">Powrót do strony głównej księgarni</a></p>

<?php

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