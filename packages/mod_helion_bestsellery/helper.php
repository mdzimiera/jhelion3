<?php

class modHelionBestselleryHelper
{
    
    private static $_status = array(
                        0 => 'Niedostępna',
                        1 => 'Dostępna',
                        2 => 'W przygotowaniu',
                        5 => 'Druk na żądanie',
                        7 => 'Przedsprzedaż',
                        );
    
    private static $_brand = array(
                        1 => 'Helion',
                        2 => 'Onepress',
                        3 => 'Editio',
                        4 => 'Sensus',
                        5 => 'Septem',
                        6 => 'Bezdroża',
                        7 => 'Bezdroża',
                        8 => 'Helion edukacja',
                        9 => 'Sensus',
                        11 => 'Bezdroża obce',
                        13 => 'Ebookpoint',
                        );
    
    private static $_type = array(
                        1 => 'Książka',
                        2 => 'Ebook',
                        3 => 'Audiobook mp3',
                        4 => 'Audiocd mp3',
                        );
    
    public static function getStatus($status){
        
        return self::$_status["$status"];
        
    }

    public static function getBrand($brand){
        
        return self::$_brand["$brand"];
        
    }
    
    public static function getType($type){
        
        return self::$_type["$type"];
        
    }
    
    public static function getTypeByIdent($ident){
        
        $type = 1;
        if(preg_match('/\_ebook$/i', $ident)){
            $type = 2;
        }elseif(preg_match('/\_a$/i', $ident)){
            $type = 3;
        }elseif(preg_match('/\_3$/i', $ident)){
            $type = 4;
        }else{
            $type = 1;
        }
        
        return self::$_type["$type"];
        
    }
    
    public static function ISBNtoEAN($isbn){

        $ean = str_replace('-', '', $isbn);

        if (strpos($ean, '978') === 0) {
            return $ean;
        }

        $ean = '978'.substr($ean, 0, 9);

        $pow = array(1, 3, 1, 3, 1, 3, 1, 3, 1, 3, 1, 3);
        $sum = 0;

        for ($i=0; $i<12; $i++) {
            $sum += $pow[$i] * intval(substr($ean, $i, 1));
        }

        $sum = $sum % 10;

        if ($sum > 0) {
            $sum = 10-$sum;
        }
        $ean .= $sum;

        return $ean;
    
    }
     
}
?>
