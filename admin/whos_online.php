<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $xx_mins_ago = (time() - 900);

  require('includes/application_top.php');

  require('includes/classes/currencies.php');
  $currencies = new currencies();

// remove entries that have expired
  tep_db_query("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

  require('includes/template_top.php');
?>
<div class="row">
  <div class="col-sm-9">
        <div class="card mb-3">
                  <div class="card-header">
                    <h3><i class="fa fa-binoculars"></i> <?php echo HEADING_TITLE; ?>
                    <span style="float:right"></span></h3>
                  </div>
                  <div class="card-body">
                  <div class="table-responsive">
                      <table class="table table-hover table-sm table-striped">
                      <thead>
                        <tr>
                          <th scope="col"><?php echo TABLE_HEADING_ONLINE; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_CUSTOMER_ID; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_FULL_NAME; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_IP_ADDRESS; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_ENTRY_TIME; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_LAST_CLICK; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_LAST_PAGE_URL; ?></th>
                        </tr>
                      </thead>
                      <tbody>
<?php
  $whos_online_query = tep_db_query("select customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id from " . TABLE_WHOS_ONLINE);
  while ($whos_online = tep_db_fetch_array($whos_online_query)) {
    $time_online = (time() - $whos_online['time_entry']);
    if ((!isset($_GET['info']) || (isset($_GET['info']) && ($_GET['info'] == $whos_online['session_id']))) && !isset($info)) {
      $info = new ObjectInfo($whos_online);
    }

    if (isset($info) && ($whos_online['session_id'] == $info->session_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('whos_online.php', tep_get_all_get_params(array('info', 'action')) . 'info=' . $whos_online['session_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo gmdate('H:i:s', $time_online); ?></td>
                <td class="dataTableContent" align="center"><?php echo $whos_online['customer_id']; ?></td>
                <td class="dataTableContent"><?php echo $whos_online['full_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $whos_online['ip_address']; ?></td>
                <td class="dataTableContent"><?php echo date('H:i:s', $whos_online['time_entry']); ?></td>
                <td class="dataTableContent" align="center"><?php echo date('H:i:s', $whos_online['time_last_click']); ?></td>
                <td class="dataTableContent"><?php if (preg_match('/^(.*)osCsid=[A-Z0-9,-]+[&]*(.*)/i', $whos_online['last_page_url'], $array)) { echo $array[1] . $array[2]; } else { echo $whos_online['last_page_url']; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td class="smallText" colspan="7"><?php echo sprintf(TEXT_NUMBER_OF_CUSTOMERS, tep_db_num_rows($whos_online_query)); ?></td>
              </tr>
              </tbody>         
                    </table>
                  </div>       
            </div><!-- end card-body-->															
        </div><!-- end card-->	
  </div><!-- end col-->
<?php
  $heading = array();
  $contents = array();

  if (isset($info)) {
    $heading[] = array('text' => '<strong>' . TABLE_HEADING_SHOPPING_CART . '</strong>');

    if ( $info->customer_id > 0 ) {
      $products_query = tep_db_query("select cb.customers_basket_quantity, cb.products_id, pd.products_name from " . TABLE_CUSTOMERS_BASKET . " cb, " . TABLE_PRODUCTS_DESCRIPTION . " pd where cb.customers_id = '" . (int)$info->customer_id . "' and cb.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'");

      if ( tep_db_num_rows($products_query) ) {
        $shoppingCart = new shoppingCart();

        while ( $products = tep_db_fetch_array($products_query) ) {
          $contents[] = array('text' => $products['customers_basket_quantity'] . ' x ' . $products['products_name']);

          $attributes = array();

          if ( strpos($products['products_id'], '{') !== false ) {
            $combos = array();
            preg_match_all('/(\{[0-9]+\}[0-9]+){1}/', $products['products_id'], $combos);

            foreach ( $combos[0] as $combo ) {
              $att = array();
              preg_match('/\{([0-9]+)\}([0-9]+)/', $combo, $att);

              $attributes[$att[1]] = $att[2];
            }
          }

          $shoppingCart->add_cart(tep_get_prid($products['products_id']), $products['customers_basket_quantity'], $attributes);
        }

        $contents[] = array('text' => tep_draw_separator('pixel_black.gif', '100%', '1'));
        $contents[] = array('align' => 'right', 'text'  => TEXT_SHOPPING_CART_SUBTOTAL . ' ' . $currencies->format($shoppingCart->show_total()));
      } else {
        $contents[] = array('text' => '&nbsp;');
      }
    } else {
      $contents[] = array('text' => 'N/A');
    }
  }

 
  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
?>

    <div class="col-sm-3">
    <div class="card mb-3">
        <div class="card-header">
          <h3><?php echo $heading[0]['text']; ?></h3>
        </div>
        <div class="card-body card-bg">  
            <?php
              $box = new box;
              $heading[0]['text'] = '';
              echo $box->infoBox($heading, $contents);
            ?>
          </div><!-- end card-body-->															
        </div><!-- end card-->	
  </div><!-- end col-->

  <?php
  }
?>
</div>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
