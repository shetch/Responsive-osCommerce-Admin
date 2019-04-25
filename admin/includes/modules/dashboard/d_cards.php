<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2013 osCommerce
  Released under the GNU General Public License
*/
  class d_cards {
    var $code = 'd_cards';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;
    function __construct() {
      $this->title = MODULE_ADMIN_DASHBOARD_CARDS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_CARDS_DESCRIPTION;
      if ( defined('MODULE_ADMIN_DASHBOARD_CARDS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_CARDS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_CARDS_STATUS == 'True');
      }
    }
    function getOutput() {
      $output = null;
      $orders_query = tep_db_query("select count(*) as count from orders where orders_status = '1'");
      $orders_request = tep_db_fetch_array($orders_query);
      $orders =  $orders_request['count'];
      $stock_query = tep_db_query("select count(*) as count from products where products_quantity = '0'");
      $stock_request = tep_db_fetch_array($stock_query);
      $stock =  $stock_request['count'];
      $inventory_query = tep_db_query("select count(*) as count from products");
      $inventory_request = tep_db_fetch_array($inventory_query);
      $inventory = $inventory_request['count'];
      $low_amount = STOCK_REORDER_LEVEL + 1;
      $low_query = tep_db_query("select count(*) as count from products where products_quantity < '".$low_amount."' and products_quantity <> '0'");
      $low_request = tep_db_fetch_array($low_query);
      $low =  $low_request['count']; 
      $output = '
        <div class="col-xs-12 col-md-6 col-lg-6 col-xl-3">
            <div class="card-box noradius noborder bg-default">
                <i class="fa fa-shopping-basket float-right text-white"></i>
                <h6 class="text-white text-uppercase m-b-20">Orders</h6>
                <h1 class="m-b-20 text-white counter">'.$orders.'</h1>
                <span class="text-white"><a class="btn btn-info btn-sm" href="'.tep_href_link('orders.php').'">View</a></span>
            </div>
        </div>
        <div class="col-xs-12 col-md-6 col-lg-6 col-xl-3">
            <div class="card-box noradius noborder bg-success">
                <i class="fa fa-barcode float-right text-white"></i>
                <h6 class="text-white text-uppercase m-b-20">Products</h6>
                <h1 class="m-b-20 text-white counter">'.$inventory.'</h1>
                <span class="text-white"><a class="btn btn-success btn-sm" href="'.tep_href_link('categories.php').'">Edit</a></span>
            </div>
        </div>
        <div class="col-xs-12 col-md-6 col-lg-6 col-xl-3">
            <div class="card-box noradius noborder bg-warning">
                <i class="fa fa-bar-chart float-right text-white"></i>
                <h6 class="text-white text-uppercase m-b-20">Low Stock</h6>
                <h1 class="m-b-20 text-white counter">'.$low.'</h1>
                <span class="text-white"><a class="btn btn-warning btn-sm" href="'.tep_href_link('index.php', 'action=show').'">View</a></span>
            </div>
        </div>
        <div class="col-xs-12 col-md-6 col-lg-6 col-xl-3">
            <div class="card-box noradius noborder bg-danger">
                <i class="fa fa-bell-o float-right text-white"></i>
                <h6 class="text-white text-uppercase m-b-20">Out Of Stock</h6>
                <h1 class="m-b-20 text-white counter">'.$stock.'</h1>
                <span class="text-white"><a class="btn btn-danger btn-sm" href="'.tep_href_link('index.php', 'action=show').'">View</a></span>
            </div>
        </div>';
      return $output;
    }
    function isEnabled() {
      return $this->enabled;
    }
    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_CARDS_STATUS');
    }
    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Cards Module', 'MODULE_ADMIN_DASHBOARD_CARDS_STATUS', 'True', 'Do you want to show the cards on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_CARDS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }
    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_CARDS_STATUS', 'MODULE_ADMIN_DASHBOARD_CARDS_SORT_ORDER');
    }
  }
?>