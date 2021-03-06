CREATE TABLE IF NOT EXISTS `#__helion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
    `ident` text NULL,
    `ksiegarnia` text NULL,
    `isbn` text NULL,
    `tytul` text NULL,
    `tytul_orig` text NULL,
    `link` text NULL,
    `autor` text NULL,
    `tlumacz` text NULL,
    `cena` text NULL,
    `cenadetaliczna` text NULL,
    `znizka` text NULL,
    `status` text NULL,
    `marka` text NULL,
    `nazadanie` bool NULL,
    `format` text NULL,
    `liczbastron` text NULL,
    `oprawa` text NULL,
    `nosnik` text NULL,
    `datawydania` text NULL,
    `issueurl` text NULL,
    `online` text NULL,
    `bestseller` bool NULL,
    `nowosc` bool NULL,
    `videos` text NULL,
    `powiazane` text NULL,
    `opis` text NULL,
    `kategorie` text NULL,
    `seriewydawnicze` text NULL,
    `serietematyczne` text NULL,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__helion_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ksiegarnia` varchar(25) NOT NULL,
  `update_time` int(16) NOT NULL,
   PRIMARY KEY  (`id`),
   UNIQUE (`ksiegarnia`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__helion_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meta` varchar(25) NOT NULL,
  `value` text NOT NULL,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__helion_status` (`ksiegarnia`, `update_time`) VALUES ('helion', '1'), ('onepress', '1'), ('sensus', '1'), ('septem', '1'), ('bezdroza', '1'), ('ebookpoint', '1');
INSERT IGNORE INTO `#__helion_status` (`ksiegarnia`, `update_time`) VALUES ('helion_kategorie', '1'), ('onepress_kategorie', '1'), ('sensus_kategorie', '1'), ('septem_kategorie', '1'), ('bezdroza_kategorie', '1'), ('ebookpoint_kategorie', '1');
