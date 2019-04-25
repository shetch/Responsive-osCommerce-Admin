<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class d_total_revenue {
    var $code = 'd_total_revenue';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_STATUS == 'True');
      }
    }

    function getOutput() {
      $days = array();
      for($i = 0; $i < 30; $i++) {
        $days[date('Y-m-d', strtotime('-'. $i .' days'))] = 0;
      }
      $orders_query = tep_db_query("select date_format(o.date_purchased, '%Y-%m-%d') as dateday, sum(ot.value) as total from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot where date_sub(curdate(), interval 30 day) <= o.date_purchased and o.orders_id = ot.orders_id and ot.class = 'ot_total' group by dateday");
      while ($orders = tep_db_fetch_array($orders_query)) {
        $days[$orders['dateday']] = $orders['total'];
      }
      $days = array_reverse($days, true);
      $js_array = ''; 
      $label_array = '';
      foreach ($days as $date => $total) {
         $js_array .= $total . ', ';
         $label_array .=  '"'.substr($date, 8, 2) . '/'.substr($date, 5, 2) . '", ';
      }
      if (!empty($js_array)) {
        $js_array = substr($js_array, 0, -1);
      }
      if (!empty($label_array)) {
        $label_array = substr($label_array, 0, -1);
      }    

      $output = <<<EOD
      <div class="col-sm-6">						
            <div class="card mb-3">
              <div class="card-header">
              <h6><i class="fa fa-credit-card"></i> Total Revenue</h6>
              </div>
              <div class="card-body">
                <canvas id="barChart2"></canvas>
              </div>							
            </div>				
          </div> 
                    
<script type="text/javascript">
var ctx2 = document.getElementById("barChart2").getContext('2d');
var barChart2 = new Chart(ctx2, {
  type: 'bar',
  data: {
    labels: [$label_array],
    datasets: [{
      label: 'Total Revenue',
      data: [$js_array],
      backgroundColor: 'rgba(54, 162, 235, 0.2)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 1
    }]
  },
  options: {
    scales: {
      yAxes: [{
        ticks: {
          beginAtZero:true
        }
      }]
    }
  }
});
</script>
EOD;

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Total Revenue Module', 'MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_STATUS', 'True', 'Do you want to show the total revenue chart on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_STATUS', 'MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_SORT_ORDER');
    }
  }
?>
