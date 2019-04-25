<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2010 osCommerce
  Released under the GNU General Public License
*/
  class d_quick_links {
    var $code = 'd_quick_links';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;
    function __construct() {
      $this->title = MODULE_ADMIN_DASHBOARD_QUICK_LINKS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_QUICK_LINKS_DESCRIPTION;
      if ( defined('MODULE_ADMIN_DASHBOARD_QUICK_LINKS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_QUICK_LINKS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_QUICK_LINKS_STATUS == 'True');
      }
    }
    function getOutput() {
      $output = '<div class="container"><div class="card mb-3">
      <div class="card-header">
        <h6><i class="fa fa-bolt"></i> ' . MODULE_ADMIN_DASHBOARD_QUICK_LINKS_TITLE . '
        <span style="float:right"></span></h6>
      </div>
      <div class="card-body quick-links card-bg">';
      $output .= '<div class="row">';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Edit Products" class="col text-center"><a class="quick-link" href="'.tep_href_link('categories.php').'"><i class="fa fa-barcode fa-3x"></i></a></div>';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Edit Attributes" class="col text-center"><a class="quick-link" href="'.tep_href_link('products_attributes.php').'"><i class="fa fa-plug fa-3x"></i></a></div>';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Edit Options" class="col text-center"><a class="quick-link" href="'.tep_href_link('products_attributes.php','p=2').'"><i class="fa fa-key fa-3x"></i></a></div>';      
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Edit Option Values" class="col text-center"><a class="quick-link" href="'.tep_href_link('products_attributes.php','p=3').'"><i class="fa fa-sitemap fa-3x"></i></a></div>';         
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Specials" class="col text-center"><a class="quick-link" href="'.tep_href_link('specials.php').'"><i class="fa fa-tag fa-3x"></i></a></div>';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Orders" class="col text-center"><a class="quick-link" href="'.tep_href_link('orders.php').'"><i class="fa fa-shopping-basket fa-3x"></i></a></div>';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Customers" class="col text-center"><a class="quick-link" href="'.tep_href_link('customers.php').'"><i class="fa fa-user-o fa-3x"></i></a></div>';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Shipping Modules" class="col text-center"><a class="quick-link" href="'.tep_href_link('modules.php', 'set=shipping').'"><i class="fa fa-truck fa-3x"></i></a></div>';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Payment Modules" class="col text-center"><a class="quick-link" href="'.tep_href_link('modules.php', 'set=payment').'"><i class="fa fa-credit-card fa-3x"></i></a></div>';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Banner Manager" class="col text-center"><a class="quick-link" href="'.tep_href_link('banner_manager.php').'"><i class="fa fa-image fa-3x"></i></a></div>';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Content Modules" class="col text-center"><a class="quick-link" href="'.tep_href_link('modules_content.php').'"><i class="fa fa-th-large fa-3x"></i></a></div>';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Store Settings" class="col text-center"><a class="quick-link" href="'.tep_href_link('configuration.php', 'gID=1').'"><i class="fa fa-cogs fa-3x"></i></a></div>';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Email" class="col text-center"><a class="quick-link" href="'.tep_href_link('mail.php').'"><i class="fa fa-envelope-o fa-3x" style="position:relative;top:-4px;"></i></a></div>';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Newsletters" class="col text-center"><a class="quick-link" href="'.tep_href_link('newsletters.php').'"><i class="fa fa-newspaper-o fa-3x"></i></a></div>';
      $output .= '<div data-toggle="tooltip" data-placement="top" title="Backup" class="col text-center"><a class="quick-link" href="'.tep_href_link('backup.php').'"><i class="fa fa-database fa-3x"></i></a></div>';
      $output .= '</div>';
      $output .= '</div>       
      </div>															
      </div>';
      return $output;
    }
    function isEnabled() {
      return $this->enabled;
    }
    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_QUICK_LINKS_STATUS');
    }
    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Quick Links Module', 'MODULE_ADMIN_DASHBOARD_QUICK_LINKS_STATUS', 'True', 'Do you want to show the quick links on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_QUICK_LINKS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }
    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_QUICK_LINKS_STATUS', 'MODULE_ADMIN_DASHBOARD_QUICK_LINKS_SORT_ORDER');
    }
  }
?>
