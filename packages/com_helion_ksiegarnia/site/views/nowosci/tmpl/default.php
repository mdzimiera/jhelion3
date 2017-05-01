<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$db = JFactory::getDBO();

?>
<h1>Księgarnia - Nowości</h1>
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

$cyfra = "40";

$query = "SELECT value FROM #__helion_config WHERE meta = 'ksiegarnia'";
$db->setQuery($query);
$ksiegarnia = $db->loadResult();

$query = "SELECT value FROM #__helion_config WHERE meta = 'partner_id'";
$db->setQuery($query);
$partner_id = $db->loadResult();

$query = "SELECT * FROM #__helion WHERE ksiegarnia = '" . $ksiegarnia . "' AND nowosc = '1' AND cena ORDER BY bestseller DESC LIMIT 20";
$db->setQuery($query);
$result = $db->loadAssocList();

foreach($result as $ksiazka) {
    $url = JURI::current() . "?view=ksiazka&ident=" . $ksiazka['ident'] . "&ksiegarnia=" . $ksiazka['ksiegarnia'];
    $koszyk = "http://" . $ksiazka['ksiegarnia'] . ".pl/add/" . $partner_id . "/" . $cyfra . "/" . $ksiazka['ident'];
?>
<div class="helion_ksiazka">
    <div class="ksiazka_info">
        <table width="100%">
        <tr>
            <td rowspan="5" class="okladka"><a href="<?php echo $url; ?>" title="<?php echo $ksiazka['tytul']?>">
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