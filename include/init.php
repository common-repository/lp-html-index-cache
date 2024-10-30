<?php
/**
* @author Le.VanPhu
* @since 31/01/2018
* @see http://levanphu.info
* @category LP HTML Index Cache
* @description: #Tang Phung.Kien tool generate cache html eny website to fast website. This tool just make only index.html
*/
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * @description: Make html file to cache
 * @param [type] $data [description]
 */
function LPSaveCache($homepage, $path, $cacheName)
{
  $filename = $path . $cacheName . '.html';
  $handle = fopen($filename,"w");
  fwrite($handle,gzcompress($homepage,9));
  fclose($handle);
  return true;
}
?>