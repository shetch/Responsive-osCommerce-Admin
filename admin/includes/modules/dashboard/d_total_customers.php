<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class d_total_customers {
    var $code = 'd_total_customers';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_STATUS == 'True');
      }
    }

    function getOutput() {
      $days2 = array();
      for($i = 0; $i < 30; $i++) {
        $days2[date('Y-m-d', strtotime('-'. $i .' days'))] = 0;
      }

      $customer_query = tep_db_query("select date_format(customers_info_date_account_created, '%Y-%m-%d') as dateday1, count(*) as total1 from " . TABLE_CUSTOMERS_INFO . " where date_sub(curdate(), interval 30 day) <= customers_info_date_account_created group by dateday1");
      while ($customer = tep_db_fetch_array($customer_query)) {
        $days2[$customer['dateday1']] = $customer['total1'];
      }
      $days2 = array_reverse($days2, true);
      $cs_array = ''; 
      $label_array = '';
      foreach ($days2 as $date => $total1) {
         $cs_array .= $total1 . ', ';
         $label_array .=  '"'.substr($date, 8, 2) . '/'.substr($date, 5, 2) . '", ';
      }
      if (!empty($cs_array)) {
        $cs_array = substr($cs_array, 0, -1);
      }
      if (!empty($label_array)) {
        $label_array = substr($label_array, 0, -1);
      } 
      $output = <<<EOD
      <div class="col-sm-6">						
            <div class="card mb-3">
              <div class="card-header">
                <h6><i class="fa fa-user"></i> Total Customers</h6>
              </div>
              <div class="card-body">
                <canvas id="barChart1"></canvas>
              </div>							
            </div>				
          </div> 
                    
<script type="text/javascript">
var ctx2 = document.getElementById("barChart1").getContext('2d');
var barChart1 = new Chart(ctx2, {
  type: 'bar',
  data: {
    labels: [$label_array],
    datasets: [{
      label: 'Total Customers',
      data: [$cs_array],
      backgroundColor: 'rgba(255, 99, 132, 0.2)',
      borderColor: 'rgba(255,99,132,1)',
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
      return defined('MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Total Customers Module', 'MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_STATUS', 'True', 'Do you want to show the total customers chart on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_STATUS', 'MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_SORT_ORDER');
    }
  }
?>