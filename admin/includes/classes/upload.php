<?php

/* -----------------------------------------------------------------
 * 	$Id: upload.php 420 2013-06-19 18:04:39Z akausch $
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

class upload {

    var $file, $filename, $destination, $permissions, $extensions, $tmp_filename;

    // function __construct() {
    // }

    function upload($file = '', $destination = '', $permissions = '777', $extensions = '') {

        $this->set_file($file);
        $this->set_destination($destination);
        $this->set_permissions($permissions);
        $this->set_extensions($extensions);

        if (xtc_not_null($this->file) && xtc_not_null($this->destination)) {
            if (($this->parse() == true) && ($this->save() == true)) {
                return true;
            } else {
                return false;
            }
        }
    }

    function parse() {
        global $messageStack;
        $file = array();
        if (isset($_FILES[$this->file])) {
            $file = array('name' => $_FILES[$this->file]['name'],
                'type' => $_FILES[$this->file]['type'],
                'size' => $_FILES[$this->file]['size'],
                'tmp_name' => $_FILES[$this->file]['tmp_name']);
        }

        if (isset($file['tmp_name']) && !empty($file['tmp_name']) && ($file['tmp_name'] != 'none') && is_uploaded_file($file['tmp_name'])) {
            if (sizeof($this->extensions) > 0) {
                if (!in_array(strtolower(substr($file['name'], strrpos($file['name'], '.') + 1)), $this->extensions)) {
                    $messageStack->add(ERROR_FILETYPE_NOT_ALLOWED, 'error');

                    return false;
                }
            }
            //Verhindern, dass PHP oder HTML Dateien statt Bilder hoch geladen werden
            if (in_array(strtolower(substr($file['name'], strrpos($file['name'], '.') + 1)), array('php', 'php3', 'php4', 'php5', 'phtml', 'ini'))) {
                $messageStack->add(ERROR_FILETYPE_NOT_ALLOWED, 'error');
                return false;
            }
            if ($file['name'] == '.htaccess' || $file['name'] == '.htpasswd') {
                $messageStack->add(ERROR_FILETYPE_NOT_ALLOWED, 'error');
                return false;
            }

            $this->set_file($file);
            $this->set_filename($file['name']);
            $this->set_tmp_filename($file['tmp_name']);

            return $this->check_destination();
        } else {

            if ($file['tmp_name'] == 'none')
                $messageStack->add(WARNING_NO_FILE_UPLOADED, 'warning');

            return false;
        }
    }

    function save() {
        global $messageStack;

        if (substr($this->destination, -1) != '/')
            $this->destination .= '/';

        // GDlib check
        if (!function_exists(imagecreatefromgif)) {

            // check if uploaded file = gif
            if ($this->destination == DIR_FS_CATALOG_ORIGINAL_IMAGES) {
                // check if merge image is defined .gif
                if (strstr(PRODUCT_IMAGE_THUMBNAIL_MERGE, '.gif') ||
                        strstr(PRODUCT_IMAGE_INFO_MERGE, '.gif') ||
                        strstr(PRODUCT_IMAGE_POPUP_MERGE, '.gif')) {

                    $messageStack->add(ERROR_GIF_MERGE, 'error');
                    return false;
                }
                // check if uploaded image = .gif
                if (strstr($this->filename, '.gif')) {
                    $messageStack->add(ERROR_GIF_UPLOAD, 'error');
                    return false;
                }
            }
        }



        if (move_uploaded_file($this->file['tmp_name'], $this->destination . $this->filename)) {
            chmod($this->destination . $this->filename, $this->permissions);

            $messageStack->add(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');

            return true;
        } else {
            $messageStack->add(ERROR_FILE_NOT_SAVED, 'error');

            return false;
        }
    }

    function set_file($file) {
        $this->file = $file;
    }

    function set_destination($destination) {
        $this->destination = $destination;
    }

    function set_permissions($permissions) {
        $this->permissions = octdec($permissions);
    }

    function set_filename($filename) {
        $this->filename = $filename;
    }

    function set_tmp_filename($filename) {
        $this->tmp_filename = $filename;
    }

    function set_extensions($extensions) {
        if (xtc_not_null($extensions)) {
            if (is_array($extensions)) {
                $this->extensions = $extensions;
            } else {
                $this->extensions = array($extensions);
            }
        } else {
            $this->extensions = array();
        }
    }

    function check_destination() {
        global $messageStack;

        if (!is_writeable($this->destination)) {
            if (is_dir($this->destination)) {
                $messageStack->add_session(sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
            } else {
                $messageStack->add_session(sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
            }

            return false;
        } else {
            return true;
        }
    }

}
