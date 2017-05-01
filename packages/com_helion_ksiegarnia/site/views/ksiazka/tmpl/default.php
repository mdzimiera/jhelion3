<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$db = JFactory::getDBO();

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
$nowosc = $bestseller = "";

if(!$ksiazka) {
    echo "<p>Nie można było pobrać danych na temat tej książki.</p>";
    return false;
}

$url = "http://" . $ksiegarnia . ".pl/add/" . $partner_id . "/" . $cyfra . "/" . $ksiazka['ident'];

?>
<h2><?php echo $ksiazka['autor']; ?> - <?php echo $ksiazka['tytul']; ?></h2>
<?php
$query = "SELECT value FROM #__helion_config WHERE meta = 'wyszukiwarka_w_tresci'";
$db->setQuery($query);
$wyszukiwarka_w_tresci = $db->loadResult();

if($wyszukiwarka_w_tresci) {
?>
<div class="helion_wyszukiwarka">
    <form action="<?php echo JURI::current(); ?>" method="get">
        <input type="hidden" name="view" value="szukaj" />
        <input type="text" name="fraza" class="input-small" value="<?php echo !empty($fraza) ? $fraza : 'wyszukaj...'; ?>" onclick="this.value = '';"/>
        <input type="submit" value="Szukaj" class="btn btn-primary btn-small" />
    </form>
</div>
<div class="wyszukiwarka_clear"></div>
<?php } ?>

<div class="helion_ksiazka">
    <div class="helion-cover">
        <a href="<?php echo $url; ?>" title="<?php echo $ksiazka['tytul']?>">
            <img src="https://static01.helion.com.pl/global/okladki/181x236/<?php echo preg_replace('/\_ebook$/i', '', $ksiazka['ident']); ?>.jpg" />
        </a>
    </div>
    <div class="ksiazka_info">
        <h3 class="tytul"><a href="<?php echo $url; ?>" title="<?php echo $ksiazka['tytul']?>"><?php echo $ksiazka['tytul']; ?></a></h3>
        <p class="autor"><b>Autor:</b> 
            <span class="help-block helion-help-block"><strong><?php echo $ksiazka['autor']; ?></strong></span>
        </p>
        <p class="format"><b>Format:</b> <span class="help-block helion-help-block"><strong><?php echo HelionHelper::getTypeByIdent($ksiazka['ident'])?></strong></span></p>
        <p class="datawydania"><b>Data wydania:</b> <span class="help-block helion-help-block"><strong><?php echo $ksiazka['datawydania']; ?></strong></span></p>
        <?php if ($ksiazka['liczbastron']):?>
        <p class="stron"><b>Stron:</b> <span class="help-block helion-help-block"><strong><?php echo $ksiazka['liczbastron']; ?></strong></span></p>
        <?php endif;?>
        <p class="dostawa">Dostawa: 0,00 zł</p>
        <p class="wysylka">Wysyłka w 24h</p>
        <ul class="tags">
            <?php if($ksiazka['nowosc'] == "1"):?>
            <li class="tag-new">Nowość</li>
            <?php endif;?>
            <?php if($ksiazka['bestseller'] == "1"):?>
            <li class="tag-bestseller">Bestseller</li>
            <?php endif;?>
        </ul>
    </div>
    <?php if($ksiazka['status'] != "0") { ?>
    <div class="helion_kup_teraz">
        <p>
            <span class="cena">Cena: <?php echo $ksiazka['cena']; ?> zł</span> 
            <?php if($ksiazka['znizka'] > 0) echo '<br /><span class="znizka">Zniżka: ' . $ksiazka['znizka'] . "%</span>"; ?>
        </p>
        <p>
            <div class="helion-box">
                <a href="<?php echo $url; ?>" title="Dodaj '<?php echo $ksiazka['tytul']; ?>' do koszyka" rel="nofollow" target="_blank">Kup teraz</a>
            </div>
        </p>
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
                <p>
                    <div class="helion-box">
                        <a href="<?php echo $url; ?>" title="Dodaj '<?php echo $ksiazka['tytul']; ?>' do koszyka" rel="nofollow" target="_blank">Kup teraz</a>
                    </div>
                </p>
            </div>
            <?php } else { ?>
                <div class="helion_kup_teraz">
                    <p class="niedostepna">Książka chwilowo niedostępna.</p>
                </div>
            <?php } ?>
        <?php echo JHtml::_('bootstrap.endTab');?> 

        <?php echo JHtml::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'tab2_j31_id', JText::_('Spis treści')); ?> 
        <p></p> 
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