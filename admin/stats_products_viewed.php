<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require('includes/template_top.php');
?>
<div class="row">
  <div class="col-sm-12">
        <div class="card mb-3">
                  <div class="card-header">
                    <h3><i class="fa fa-line-chart"></i> <?php echo HEADING_TITLE; ?>
                    <span style="float:right"></span></h3>
                  </div>
                  <div class="card-body">  
                    <div class="table-responsive">
                      <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th scope="col"><?php echo TABLE_HEADING_NUMBER; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
                          <th class="text-right" scope="col"><?php echo TABLE_HEADING_VIEWED; ?></th>
                        </tr>
                      </thead>
                      <tbody>
   
<?php
  if (isset($_GET['page']) && ($_GET['page'] > 1)) $rows = $_GET['page'] * MAX_DISPLAY_SEARCH_RESULTS - MAX_DISPLAY_SEARCH_RESULTS;
  $rows = 0;
  $products_query_raw = "select p.products_id, pd.products_name, pd.products_viewed, l.name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_LANGUAGES . " l where p.products_id = pd.products_id and l.languages_id = pd.language_id order by pd.products_viewed DESC";
  $products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $products_query_raw, $products_query_numrows);
  $products_query = tep_db_query($products_query_raw);
  while ($products = tep_db_fetch_array($products_query)) {
    $rows++;

    if (strlen($rows) < 2) {
      $rows = '0' . $rows;
    }
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?php echo tep_href_link('categories.php', 'action=new_product_preview&read=only&pID=' . $products['products_id'] . '&origin=stats_products_viewed.php?page=' . $_GET['page']); ?>'">
                <td class="dataTableContent"><?php echo $rows; ?>.</td>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link('categories.php', 'action=new_product_preview&read=only&pID=' . $products['products_id'] . '&origin=stats_products_viewed.php?page=' . $_GET['page']) . '">' . $products['products_name'] . '</a> (' . $products['name'] . ')'; ?></td>
                <td class="dataTableContent" align="right"><?php echo $products['products_viewed']; ?>&nbsp;</td>
              </tr>
<?php
  }
?>
      
             
              </tbody>         
                    </table>


<table class="table">
                    <tr>
                <td class="smallText" valign="top"><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
                <td colspan="2" class="smallText" align="right"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
              </tr>

              </table>


                  </div>       
            </div><!-- end card-body-->															
        </div><!-- end card-->	
  </div><!-- end col-->
</div>  
<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
