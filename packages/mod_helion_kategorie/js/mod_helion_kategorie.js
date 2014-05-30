/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function() {
   
    jQuery(".mod_nadkategorie").click(function() {
        jQuery(this).next().find('ul.mod_podkategorie').slideToggle(300);
        return false;
    });
    
});
