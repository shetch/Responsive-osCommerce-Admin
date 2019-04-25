<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class d_reviews {
    var $code = 'd_reviews';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = MODULE_ADMIN_DASHBOARD_REVIEWS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_REVIEWS_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_REVIEWS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS == 'True');
      }
    }

    function getOutput() {
      global $languages_id;

      $output = '<div class="col-sm-12">
      <div class="card mb-3">
                <div class="card-header">
                  <h6><i class="fa fa-user"></i> ' . MODULE_ADMIN_DASHBOARD_REVIEWS_TITLE . '
                  <span style="float:right"></span></h6>
                </div>
                <div class="card-body"><div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                <thead>
                  <tr>
                    <th scope="col"> ' . MODULE_ADMIN_DASHBOARD_REVIEWS_TITLE . '</th>
                    <th scope="col">' . MODULE_ADMIN_DASHBOARD_REVIEWS_DATE . '</th>
                    <th scope="col"> ' . MODULE_ADMIN_DASHBOARD_REVIEWS_REVIEWER . '</th>
                    <th scope="col">' . MODULE_ADMIN_DASHBOARD_REVIEWS_RATING . '</th>                    
                    <th scope="col"> ' . MODULE_ADMIN_DASHBOARD_REVIEWS_REVIEW_STATUS . '</th>
                  </tr>
                </thead>
                <tbody>';

      $reviews_query = tep_db_query("select r.reviews_id, r.date_added, pd.products_name, r.customers_name, r.reviews_rating, r.reviews_status from " . TABLE_REVIEWS . " r, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = r.products_id and pd.language_id = '" . (int)$languages_id . "' order by r.date_added desc limit 6");
      while ($reviews = tep_db_fetch_array($reviews_query)) {
        $status_icon = ($reviews['reviews_status'] == '1') ? tep_image('images/icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) : tep_image('images/icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
        
        $rating_str = '';
        for ($x = 1; $x <= $reviews['reviews_rating']; $x++) {
          $rating_str .= '<i class="fa fa-star" aria-hidden="true"></i>';
        } 
        
        
        
        $output .= '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
                   '    <td class="dataTableContent"><a href="' . tep_href_link('reviews.php', 'rID=' . (int)$reviews['reviews_id'] . '&action=edit') . '">' . $reviews['products_name'] . '</a></td>' .
                   '    <td class="dataTableContent">' . tep_date_short($reviews['date_added']) . '</td>' .
                   '    <td class="dataTableContent">' . tep_output_string_protected($reviews['customers_name']) . '</td>' .
                   '    <td class="dataTableContent">' . $rating_str . '</td>' .
                   '    <td class="dataTableContent">' . $status_icon . '</td>' .
                   '  </tr>';
      }

      $output .= '</tbody>         
      </table>
    </div>       
</div>															
</div>	
</div>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Reviews Module', 'MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS', 'True', 'Do you want to show the latest reviews on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_REVIEWS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS', 'MODULE_ADMIN_DASHBOARD_REVIEWS_SORT_ORDER');
    }
  }
?>