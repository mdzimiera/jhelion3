<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$fraza = JRequest::getString('fraza');

if(empty($fraza)) { ?>
<h1>Księgarnia - Wyszukiwarka</h1>
<p>Nie wpisano żadnej frazy. Aby wyszukać książki, skorzystaj z wyszukiwarki obok.</p>
<div class="helion_wyszukiwarka">
    <form action="<?php echo JURI::current(); ?>" method="get">
        <input type="hidden" name="view" value="szukaj" />
        <input type="text" name="fraza" value="<?php echo !empty($fraza) ? $fraza : 'wyszukaj...'; ?>" onclick="this.value = '';"/>
        <input type="submit" value="Szukaj" />
    </form>
</div>
<div class="wyszukiwarka_clear"></div>
<?php return false; }

$db = JFactory::getDBO();

?>
<h1>Księgarnia - Wyszukano frazę: <em>"<?php echo $fraza; ?>"</em></h1>
<div class="helion_wyszukiwarka">
    <form action="<?php echo JURI::current(); ?>" method="get">
        <input type="hidden" name="view" value="szukaj" />
        <input type="text" name="fraza" value="<?php echo !empty($fraza) ? $fraza : 'wyszukaj...'; ?>" onclick="this.value = '';"/>
        <input type="submit" value="Szukaj" />
    </form>
</div>
<div class="wyszukiwarka_clear"></div>
<?php 
$cyfra = "40";

$query = "SELECT value FROM #__helion_config WHERE meta = 'ksiegarnia'";
$db->setQuery($query);
$ksiegarnia = $db->loadResult();

$query = "SELECT value FROM #__helion_config WHERE meta = 'partner_id'";
$db->setQuery($query);
$partner_id = $db->loadResult();

$query = "SELECT * FROM #__helion WHERE ksiegarnia = '" . $ksiegarnia . "' AND (ident LIKE '%" . $fraza . "%' 
    OR tytul LIKE '%" . $fraza . "%' 
    OR opis LIKE '%" . $fraza . "%' 
    OR autor LIKE '%" . $fraza . "%' ) 
    AND cena IS NOT NULL AND opis IS NOT NULL AND cenadetaliczna IS NOT NULL AND status != '0' AND status != '2' ORDER BY bestseller DESC LIMIT 50";
$db->setQuery($query);
$result = $db->loadAssocList();

$wynikow = count($result);

if($wynikow == 1) {
    echo '<p>Znaleziono <strong>1</strong> książkę pasującą do zapytania:</p>';
} else if($wynikow == 2 || $wynikow == 3 || $wynikow == 4) {
    echo '<p>Znaleziono <strong>' . $wynikow . '</strong> książki pasujące do zapytania:</p>';
} else if($wynikow >= 5 && $wynikow < 50) {
    echo '<p>Znaleziono <strong>' . $wynikow . '</strong> książek pasujących do zapytania:</p>';
} else if ($wynikow >= 50) {
    echo '<p>Znaleziono <strong>50+</strong> książek pasujących do zapytania:</p>';
} else {
    echo '<p>Nie znaleziono żadnych książek pasujących do zapytania.</p>';
    return false;
}

foreach($result as $ksiazka) {
    $url = JURI::current() . "?view=ksiazka&ident=" . $ksiazka['ident'] . "&ksiegarnia=" . $ksiazka['ksiegarnia'];
    $koszyk = "http://" . $ksiazka['ksiegarnia'] . ".pl/add/" . $partner_id . "/" . $cyfra . "/" . $ksiazka['ident'];
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
?>

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