<?php

/* -----------------------------------------------------------------
 * 	$Id: blog_info_images.php 884 2014-03-27 13:00:52Z akausch $
 * 	Copyright (c) 2011-2021 commerce:SEO by Webdesign Erfurt
 * 	http://www.commerce-seo.de
 * ------------------------------------------------------------------
 * 	based on:
 * 	(c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
 * 	(c) 2002-2003 osCommerce - www.oscommerce.com
 * 	(c) 2003     nextcommerce - www.nextcommerce.org
 * 	(c) 2005     xt:Commerce - www.xt-commerce.com
 * 	Released under the GNU General Public License
 * --------------------------------------------------------------- */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$a = new image_manipulation(DIR_FS_CATALOG_IMAGES . 'blog_image/original_images/' . $products_image_name, PRODUCT_IMAGE_INFO_WIDTH, PRODUCT_IMAGE_INFO_HEIGHT, DIR_FS_CATALOG_IMAGES . 'blog_image/info_images/'  . $products_image_name, IMAGE_QUALITY, '');

$array = clear_string(PRODUCT_IMAGE_INFO_SMOTH);
if (PRODUCT_IMAGE_INFO_SMOTH != '') {
    $a->smoth($array[0]);
}

$array = clear_string(PRODUCT_IMAGE_INFO_BEVEL);
if (PRODUCT_IMAGE_INFO_BEVEL != '') {
    $a->bevel($array[0], $array[1], $array[2]);
}

$array = clear_string(PRODUCT_IMAGE_INFO_GREYSCALE);
if (PRODUCT_IMAGE_INFO_GREYSCALE != '') {
    $a->greyscale($array[0], $array[1], $array[2]);
}

$array = clear_string(PRODUCT_IMAGE_INFO_ELLIPSE);
if (PRODUCT_IMAGE_INFO_ELLIPSE != '') {
    $a->ellipse($array[0]);
}

$array = clear_string(PRODUCT_IMAGE_INFO_ROUND_EDGES);
if (PRODUCT_IMAGE_INFO_ROUND_EDGES != '') {
    $a->round_edges($array[0], $array[1], $array[2]);
}

$string = str_replace("'", '', PRODUCT_IMAGE_INFO_MERGE);
$string = str_replace(')', '', $string);
$string = str_replace('(', DIR_FS_CATALOG_IMAGES, $string);
$array = explode(',', $string);
//$array=clear_string();
if (PRODUCT_IMAGE_INFO_MERGE != '') {
    $a->merge($array[0], $array[1], $array[2], $array[3], $array[4]);
}

$array = clear_string(PRODUCT_IMAGE_INFO_FRAME);
if (PRODUCT_IMAGE_INFO_FRAME != '') {
    $a->frame($array[0], $array[1], $array[2], $array[3]);
}

$array = clear_string(PRODUCT_IMAGE_INFO_DROP_SHADOW);
if (PRODUCT_IMAGE_INFO_DROP_SHADOW != '') {
    $a->drop_shadow($array[0], $array[1], $array[2]);
}

$array = clear_string(PRODUCT_IMAGE_INFO_MOTION_BLUR);
if (PRODUCT_IMAGE_INFO_MOTION_BLUR != '') {
    $a->motion_blur($array[0], $array[1]);
}

$a->create();
