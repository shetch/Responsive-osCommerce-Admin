<?php
  require('includes/application_top.php');
  $languages = tep_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $language) {
      $languages_selected = $languages[$i]['code'];
    }
  }
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  require('includes/template_top.php');
  if (tep_not_null($action)) {
    switch ($action) {
      case 'show':
      $low_string = '';
      $low_amount = STOCK_REORDER_LEVEL;
      $low_query = tep_db_query("select distinct p.products_id, pd.products_name, p.products_quantity from products p, products_description pd where p.products_id = pd.products_id and p.products_quantity < '" . (int)$low_amount . "' and p.products_quantity > '0' order by p.products_quantity");
      while ($low = tep_db_fetch_array($low_query)) {
          $low_string .= '<tr onclick="document.location.href=\'' . tep_href_link('categories.php', 'pID='.$low['products_id'].'&action=new_product') . '\'"><td>'.$low['products_name'].'</td><td align="center">'.$low['products_quantity'].'</td><td align="right"><i class="fa fa-chevron-right" aria-hidden="true"></i></td></tr>';
      }
      $out_string = '';
      $out_query = tep_db_query("select distinct p.products_id, pd.products_name, p.products_quantity from products p, products_description pd where p.products_id = pd.products_id and p.products_quantity <= '0' order by p.products_quantity");
      while ($out = tep_db_fetch_array($out_query)) {
          $out_string .= '<tr onclick="document.location.href=\'' . tep_href_link('categories.php', 'pID='.$out['products_id'].'&action=new_product') . '\'"><td>'.$out['products_name'].'</td><td align="right"><i class="fa fa-chevron-right" aria-hidden="true"></i></td></tr>';
      }
  ?>
  <div class="row">
      <div class="col-sm-6">
            <div class="card mb-3">
                      <div class="card-header">
                        <h3><i class="fa fa-battery-quarter"></i> Low Stock Products
                        <span style="float:right"></span></h3>
                      </div>
                      <div class="card-body">  
                        <div class="table-responsive">
                          <table class="table table-sm table-hover table-striped">
                          <thead>
                            <tr>
                              <th scope="col">Product Name</th>
                              <th scope="col" colspan="2">Product Quantity</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php echo $low_string; ?>
                          </tbody>         
                        </table>
                      </div>       
                </div>														
            </div>
      </div>
      <div class="col-sm-6">
            <div class="card mb-3">
                      <div class="card-header">
                        <h3><i class="fa fa-battery-empty"></i> Out Of Stock Products
                        <span style="float:right"></span></h3>
                      </div>
                      <div class="card-body">  
                        <div class="table-responsive">
                          <table class="table table-sm table-hover table-striped">
                          <thead>
                            <tr>
                              <th scope="col" colspan="2">Product Name</th>
                            </tr>
                          </thead>
                          <tbody>
                          <?php echo $out_string; ?>                     
                          </tbody>         
                        </table>
                      </div>       
                </div>													
            </div>
      </div>
  </div>
<?php
        break;
    }
  } else {
?>
<script src="assets/js/Chart.min.js"></script>
<?php
    if ( defined('MODULE_ADMIN_DASHBOARD_INSTALLED') && tep_not_null(MODULE_ADMIN_DASHBOARD_INSTALLED) ) {
      $adm_array = explode(';', MODULE_ADMIN_DASHBOARD_INSTALLED);
      $col = 0;
      echo '          <div class="row">' . "\n";
      for ( $i=0, $n=sizeof($adm_array); $i<$n; $i++ ) {
        $adm = $adm_array[$i];
        $class = substr($adm, 0, strrpos($adm, '.'));
        if ( !class_exists($class) ) {
          include('includes/languages/' . $language . '/modules/dashboard/' . $adm);
          include('includes/modules/dashboard/' . $class . '.php');
        }
        $ad = new $class();
        if ( $ad->isEnabled() ) {
          echo $ad->getOutput();
        }
      }
    }
    echo '  </div>' . "\n";
  }
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>