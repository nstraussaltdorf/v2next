<?php

/* -----------------------------------------------------------------
 * 	$Id: ot_ps_fee.php 420 2013-06-19 18:04:39Z akausch $
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

class ot_ps_fee {

    var $title, $output;

    function ot_ps_fee() {
        global $xtPrice;
        $this->code = 'ot_ps_fee';
        $this->title = MODULE_ORDER_TOTAL_PS_FEE_TITLE;
        $this->description = MODULE_ORDER_TOTAL_PS_FEE_DESCRIPTION;
        $this->enabled = ((MODULE_ORDER_TOTAL_PS_FEE_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_PS_FEE_SORT_ORDER;
        $this->output = array();
    }

    function process() {
        global $order, $xtPrice, $ps_cost, $ps_country, $shipping;
        $customer_id = $_SESSION['customer_id'];

        if (MODULE_ORDER_TOTAL_PS_FEE_STATUS == 'true') {

            $ps_country = false;


            $count_query = xtc_db_query("select count(*) as count from " . TABLE_CUSTOMERS_BASKET . " cb, " . TABLE_PRODUCTS . " p  where cb.customers_id = '" . $customer_id . "' and cb.products_id = p.products_id and p.products_fsk18 = '1'");
            $num = xtc_db_fetch_array($count_query);

            $age = $num['count'];


            if ($age > '0') {
                //process installed shipping modules
                if ($_SESSION['shipping']['id'] == 'flat_flat')
                    $ps_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_PS_FEE_FLAT);
                if ($_SESSION['shipping']['id'] == 'item_item')
                    $ps_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_PS_FEE_ITEM);
                if ($_SESSION['shipping']['id'] == 'table_table')
                    $ps_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_PS_FEE_TABLE);
                if ($_SESSION['shipping']['id'] == 'zones_zones')
                    $ps_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_PS_FEE_ZONES);
                if ($_SESSION['shipping']['id'] == 'ap_ap')
                    $ps_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_PS_FEE_AP);
                if ($_SESSION['shipping']['id'] == 'dp_dp')
                    $ps_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_PS_FEE_DP);

                for ($i = 0; $i < count($ps_zones); $i++) {
                    if ($ps_zones[$i] == $order->billing['country']['iso_code_2']) {
                        $ps_cost = $ps_zones[$i + 1];
                        $ps_country = true;
                        //print('match' . $ps_zones[$i] . ': ' . $ps_cost);
                        break;
                    } elseif ($ps_zones[$i] == '00') {
                        $ps_cost = $ps_zones[$i + 1];
                        $ps_country = true;
                        //print('match' . $i . ': ' . $ps_cost);
                        break;
                    } else {
                        //print('no match');
                    }
                    $i++;
                }
            } else {
                //PS selected, but no shipping module which offers PS
            }

            if ($ps_country) {

                $ps_tax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_PS_FEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
                $ps_tax_description = xtc_get_tax_description(MODULE_ORDER_TOTAL_PS_FEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
                if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1) {
                    $order->info['tax'] += xtc_add_tax($ps_cost, $ps_tax) - $ps_cost;
                    $order->info['tax_groups'][TAX_ADD_TAX . "$ps_tax_description"] += xtc_add_tax($ps_cost, $ps_tax) - $ps_cost;
                    $order->info['total'] += $ps_cost + (xtc_add_tax($ps_cost, $ps_tax) - $ps_cost);
                    $ps_cost_value = xtc_add_tax($ps_cost, $ps_tax);
                    $ps_cost = $xtPrice->xtcFormat($ps_cost_value, true);
                }
                if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
                    $order->info['tax'] += xtc_add_tax($ps_cost, $ps_tax) - $ps_cost;
                    $order->info['tax_groups'][TAX_NO_TAX . "$ps_tax_description"] += xtc_add_tax($ps_cost, $ps_tax) - $ps_cost;
                    $ps_cost_value = $ps_cost;
                    $ps_cost = $xtPrice->xtcFormat($ps_cost, true);
                    $order->info['subtotal'] += $ps_cost_value;
                    $order->info['total'] += $ps_cost_value;
                }
                if (!$ps_cost_value) {
                    $ps_cost_value = $ps_cost;
                    $ps_cost = $xtPrice->xtcFormat($ps_cost, true);
                    $order->info['total'] += $ps_cost_value;
                }
                $this->output[] = array('title' => $this->title . ':',
                    'text' => $ps_cost,
                    'value' => $ps_cost_value);
            }
        }
    }

    function check() {
        if (!isset($this->_check)) {
            $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_PS_FEE_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function keys() {
        return array('MODULE_ORDER_TOTAL_PS_FEE_STATUS', 'MODULE_ORDER_TOTAL_PS_FEE_SORT_ORDER', 'MODULE_ORDER_TOTAL_PS_FEE_FLAT', 'MODULE_ORDER_TOTAL_PS_FEE_ITEM', 'MODULE_ORDER_TOTAL_PS_FEE_TABLE', 'MODULE_ORDER_TOTAL_PS_FEE_ZONES', 'MODULE_ORDER_TOTAL_PS_FEE_AP', 'MODULE_ORDER_TOTAL_PS_FEE_DP', 'MODULE_ORDER_TOTAL_PS_FEE_TAX_CLASS');
    }

    function install() {
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_PS_FEE_STATUS', 'true', '6', '0', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_PS_FEE_SORT_ORDER', '35', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_PS_FEE_FLAT', 'AT:3.00,DE:3.58,00:9.99', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_PS_FEE_ITEM', 'AT:3.00,DE:3.58,00:9.99', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_PS_FEE_TABLE', 'AT:3.00,DE:3.58,00:9.99', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_PS_FEE_ZONES', 'CA:4.50,US:3.00,00:9.99', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_PS_FEE_AP', 'AT:3.63,00:9.99', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_PS_FEE_DP', 'DE:4.00,00:9.99', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_ORDER_TOTAL_PS_FEE_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
    }

    function remove() {
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

}

