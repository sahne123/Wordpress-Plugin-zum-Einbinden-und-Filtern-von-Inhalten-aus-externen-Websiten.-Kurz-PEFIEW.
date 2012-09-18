<?php
/*
Plugin Name: Wordpress Plugin zum Einbinden und Filtern von Inhalten aus externen Websiten. Kurz PEFIEW.
Plugin URI: http://lalala.de
Description: Ein Shortcode erlaubt es externe Websiten mittels regex zu parsen und anschließend auszugeben
Version: 0.1
Author: @sahne123
Author URI: http://zeit-zu-handeln.net
*/

// Uncomment for assistance from WordPress in debugging.
define('WP_DEBUG', true);

class pefiew {

    /**
     * Constructor.
     */
    function pefiew () {
        // empty for now
    }

   
    function displayShortcode ($atts, $content = null) {
        extract( shortcode_atts( array(
			'pattern' => '#(.*)#s',
			'before' => '',
			'after' => '',
		), $atts ) );
		
		
        if( $websitecontent = @file($content) )
        {
			$data = join("", $websitecontent);
		}	
		
		$before = str_replace('{', '<', $before);
		$before = str_replace('}', '>', $before);
		$before = str_replace('°', '"', $before);
		$after = str_replace('{', '<', $after);
		$after = str_replace('}', '>', $after);
		$after = str_replace('°', '"', $after);
		$pattern = str_replace('{', '<', $pattern);
		$pattern = str_replace('}', '>', $pattern);
		$pattern = str_replace('°', '"', $pattern);
		
		$ID=md5($pattern.$content);
		$db = get_option($ID);
		
		if( (time()-$db[0]) > (7*24*60*60) && $websitecontent == false && $db[2] != true)
		{
			wp_mail( get_option("admin_email"), "Warning: veralteter Content - Website nicht erreichbar", "Dies ist eine Mail des Wordpress Plugin zum Einbinden und Filtern von Inhalten aus externen Websiten (Kurz PEFIEW). Der betroffene Blog ist: ".get_option("blogname")." (".get_option("siteurl")."). Die Website ".$content." ist seit über einer Woche nicht mehr erreichbar. Die gecachte Version ist u.U. veraltet!");
			update_option( $ID, array($db[0],$db[1],true) );
		}
		
		if( ( !$db || $db[0]+24*60*60 < time() ) && $websitecontent != false)
		{
			preg_match($pattern, $data, $matches);
			
			preg_match('#(https?://[^/]*)/#', $content, $matches2);
			$base_url = $matches2[1]."/";
			$matches[1] = preg_replace('#href="\.?/#', 'href="'.$base_url, $matches[1]);
			
			preg_match('#(.*/)#', $content, $matches1);
			$url = $matches1[1];
			$matches[1] = preg_replace('#href="(?!https?://|ftp://|mailto:|news:|\#)([^"]*)"#', 'href="'.$url.'${1}"', $matches[1]);
			
			if(!$db)
			{
				add_option( $ID, array(time(),$matches[1]) );
			} else {
				update_option( $ID, array(time(),$matches[1]) );
			}
			
		} else {
			$matches[1] = $db[1];
		}
	
		return $before.$matches[1].$after;
		
   
    }
}

$pefiew = new pefiew();
add_shortcode('PEFIEW', array($pefiew, 'displayShortcode'));

?>
