<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  tep_db_query("update " . TABLE_PRODUCTS . " set products_date_available = '' where to_days(now()) > to_days(products_date_available)");

  require('includes/template_top.php');
?>
<div class="row">
  <div class="col-sm-9">
        <div class="card mb-3">
                  <div class="card-header">
                    <h3><i class="fa fa-calendar-o"></i> <?php echo HEADING_TITLE; ?>
                    <span style="float:right"></span></h3>
                  </div>
                  <div class="card-body">
                  <table class="table table-sm table-hover table-striped">
                  <thead>
                    <tr>
                      <th scope="col"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
                      <th scope="col"><?php echo TABLE_HEADING_DATE_EXPECTED; ?></th>
                      <th scope="col"><?php echo TABLE_HEADING_ACTION; ?></th>
                    </tr>
                  </thead>
                  <tbody>                    
        
<?php
  $products_query_raw = "select pd.products_id, pd.products_name, p.products_date_available from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p where p.products_id = pd.products_id and p.products_date_available != '' and pd.language_id = '" . (int)$languages_id . "' order by p.products_date_available DESC";
  $products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $products_query_raw, $products_query_numrows);
  $products_query = tep_db_query($products_query_raw);
  while ($products = tep_db_fetch_array($products_query)) {
    if ((!isset($_GET['pID']) || (isset($_GET['pID']) && ($_GET['pID'] == $products['products_id']))) && !isset($pInfo)) {
      $pInfo = new objectInfo($products);
    }

    if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id)) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('categories.php', 'pID=' . $products['products_id'] . '&action=new_product') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('products_expected.php', 'page=' . $_GET['page'] . '&pID=' . $products['products_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $products['products_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_date_short($products['products_date_available']); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id)) { echo '<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>'; } else { echo '<a href="' . tep_href_link('products_expected.php', 'page=' . $_GET['page'] . '&pID=' . $products['products_id']) . '">' . '<i class="fa fa-info-circle" aria-hidden="true"></i>' . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              
              </tbody>         
                </table>            
              
              <table class="table"></table>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS_EXPECTED); ?></td>
                    <td class="smallText" align="right"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
              </table>
            </div><!-- end card-body-->															
        </div><!-- end card-->	
  </div><!-- end col-->
<?php
  $heading = array();
  $contents = array();

  if (isset($pInfo) && is_object($pInfo)) {
    $heading[] = array('text' => '<strong>' . $pInfo->products_name . '</strong>');

    $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('categories.php', 'pID=' . $pInfo->products_id . '&action=new_product')));
    $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_EXPECTED . ' ' . tep_date_short($pInfo->products_date_available));
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
