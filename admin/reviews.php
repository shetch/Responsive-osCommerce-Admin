<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2016 osCommerce
  Released under the GNU General Public License
*/
  require('includes/application_top.php');
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
          if (isset($_GET['rID'])) {
            tep_set_review_status($_GET['rID'], $_GET['flag']);
          }
        }
        tep_redirect(tep_href_link('reviews.php', 'page=' . $_GET['page'] . '&rID=' . $_GET['rID']));
        break;
      case 'update':
        $reviews_id = tep_db_prepare_input($_GET['rID']);
        $reviews_rating = tep_db_prepare_input($_POST['reviews_rating']);
        $reviews_text = tep_db_prepare_input($_POST['reviews_text']);
        $reviews_status = tep_db_prepare_input($_POST['reviews_status']);
        tep_db_query("update " . TABLE_REVIEWS . " set reviews_rating = '" . tep_db_input($reviews_rating) . "', reviews_status = '" . tep_db_input($reviews_status) . "', last_modified = now() where reviews_id = '" . (int)$reviews_id . "'");
        tep_db_query("update " . TABLE_REVIEWS_DESCRIPTION . " set reviews_text = '" . tep_db_input($reviews_text) . "' where reviews_id = '" . (int)$reviews_id . "'");
        tep_redirect(tep_href_link('reviews.php', 'page=' . $_GET['page'] . '&rID=' . $reviews_id));
        break;
      case 'deleteconfirm':
        $reviews_id = tep_db_prepare_input($_GET['rID']);
        tep_db_query("delete from " . TABLE_REVIEWS . " where reviews_id = '" . (int)$reviews_id . "'");
        tep_db_query("delete from " . TABLE_REVIEWS_DESCRIPTION . " where reviews_id = '" . (int)$reviews_id . "'");
        tep_redirect(tep_href_link('reviews.php', 'page=' . $_GET['page']));
        break;
      case 'addnew':
        $products_id = tep_db_prepare_input($_POST['products_id']);
        $customers_id = tep_db_prepare_input($_POST['customer_id']);
        $review = tep_db_prepare_input($_POST['reviews_text']);
        $rating = tep_db_prepare_input($_POST['rating']);
        tep_db_query("insert into " . TABLE_REVIEWS . " (products_id, customers_id, customers_name, reviews_rating, date_added, reviews_status) values ('" . (int)$products_id . "', '" . (int)$customers_id . "', '" . tep_customers_name($customers_id) . "', '" . (int)$rating . "', now(), 1)");
        $insert_id = tep_db_insert_id();
        tep_db_query("insert into " . TABLE_REVIEWS_DESCRIPTION . " (reviews_id, languages_id, reviews_text) values ('" . (int)$insert_id . "', '" . (int)$languages_id . "', '" . $review . "')");
        tep_redirect(tep_href_link('reviews.php', tep_get_all_get_params(array('action'))));
        break;   
    }
  }
  require('includes/template_top.php');
  if (($action == 'edit') || ($action == 'preview') || ($action == 'new')) {
?>
<div class="row">
  <div class="col-sm-12">
<?php
  } else {
?>
    <div class="row">
    <div class="col-sm-9">
<?php    
  }
?>
    <div class="card mb-3">
      <div class="card-header">
        <h3><i class="fa fa-film"></i> <?php echo HEADING_TITLE; ?>
        <span style="float:right"></span></h3>
      </div>
      <div class="card-body">  
        <div class="table-responsive">
<?php
  if ($action == 'edit') {
    $rID = tep_db_prepare_input($_GET['rID']);
    $reviews_query = tep_db_query("select r.reviews_id, r.products_id, r.customers_name, r.date_added, r.last_modified, r.reviews_read, rd.reviews_text, r.reviews_rating, r.reviews_status from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.reviews_id = '" . (int)$rID . "' and r.reviews_id = rd.reviews_id");
    $reviews = tep_db_fetch_array($reviews_query);
    $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$reviews['products_id'] . "'");
    $products = tep_db_fetch_array($products_query);
    $products_name_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$reviews['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
    $products_name = tep_db_fetch_array($products_name_query);
    $rInfo_array = array_merge($reviews, $products, $products_name);
    $rInfo = new objectInfo($rInfo_array);
    if (!isset($rInfo->reviews_status)) $rInfo->reviews_status = '1';
    switch ($rInfo->reviews_status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
    }
?>
     <table class="table">
          <tbody>
     <?php echo tep_draw_form('review', 'reviews.php', 'page=' . $_GET['page'] . '&rID=' . $_GET['rID'] . '&action=preview'); ?>
          <tr>
            <td class="main" valign="top"><strong><?php echo ENTRY_PRODUCT; ?></strong> <?php echo $rInfo->products_name; ?><br /><strong><?php echo ENTRY_FROM; ?></strong> <?php echo $rInfo->customers_name; ?><br /><br /><strong><?php echo ENTRY_DATE; ?></strong> <?php echo tep_date_short($rInfo->date_added); ?></td>
            <td class="main" align="right" valign="top"><?php echo tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"'); ?></td>
          </tr>
          <tr>
            <td class="main" colspan="2"><strong><?php echo TEXT_INFO_REVIEW_STATUS; ?></strong> <?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_bs_radio_field('reviews_status', '1', $in_status, '', TEXT_REVIEW_PUBLISHED, '') . tep_draw_bs_radio_field('reviews_status', '0', $out_status,'',TEXT_REVIEW_NOT_PUBLISHED,''); ?></td>
          </tr>
          <tr>
            <td colspan="2" class="main" valign="top"><strong><?php echo ENTRY_REVIEW; ?></strong><br /><br /><?php echo tep_draw_textarea_field('reviews_text', 'soft', '60', '15', $rInfo->reviews_text); ?></td>
          </tr>
          <tr>
            <td colspan="2" class="smallText" align="right"><?php echo ENTRY_REVIEW_TEXT; ?></td>
          </tr>
      <tr>
        <td colspan="2" class="main"><strong><?php echo ENTRY_RATING; ?></strong>&nbsp;<?php echo TEXT_BAD; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php for ($i=1; $i<=5; $i++) echo tep_draw_std_radio_field('reviews_rating', $i, '', $rInfo->reviews_rating) . '&nbsp;&nbsp;&nbsp;&nbsp;'; echo TEXT_GOOD; ?></td>
      </tr>
      <tr>
        <td colspan="2" align="right" class="smallText"><?php echo tep_draw_hidden_field('reviews_id', $rInfo->reviews_id) . tep_draw_hidden_field('products_id', $rInfo->products_id) . tep_draw_hidden_field('customers_name', $rInfo->customers_name) . tep_draw_hidden_field('products_name', $rInfo->products_name) . tep_draw_hidden_field('products_image', $rInfo->products_image) . tep_draw_hidden_field('date_added', $rInfo->date_added) . tep_draw_button(IMAGE_PREVIEW, 'document') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('reviews.php', 'page=' . $_GET['page'] . '&rID=' . $_GET['rID'])); ?></td>
      </form></tr>
  </tbody>
  </table>
<?php
  } elseif ($action == 'preview') {
    if (tep_not_null($_POST)) {
      $rInfo = new objectInfo($_POST);
      $rating_str = '';
      for ($x = 1; $x <= $rInfo->reviews_rating; $x++) {
        $rating_str .= '<i class="fa fa-star" aria-hidden="true"></i>';
      } 
    } else {
      $rID = tep_db_prepare_input($_GET['rID']);
      $reviews_query = tep_db_query("select r.reviews_id, r.products_id, r.customers_name, r.date_added, r.last_modified, r.reviews_read, rd.reviews_text, r.reviews_rating, r.reviews_status from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.reviews_id = '" . (int)$rID . "' and r.reviews_id = rd.reviews_id");
      $reviews = tep_db_fetch_array($reviews_query);
      $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$reviews['products_id'] . "'");
      $products = tep_db_fetch_array($products_query);
      $products_name_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$reviews['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
      $products_name = tep_db_fetch_array($products_name_query);
      $rInfo_array = array_merge($reviews, $products, $products_name);
      $rInfo = new objectInfo($rInfo_array);
      $rating_str = '';
      for ($x = 1; $x <= $rInfo->reviews_rating; $x++) {
        $rating_str .= '<i class="fa fa-star" aria-hidden="true"></i>';
      } 
    }
?>
     <table class="table">
          <tbody>
    <?php echo tep_draw_form('update', 'reviews.php', 'page=' . $_GET['page'] . '&rID=' . $_GET['rID'] . '&action=update', 'post', 'enctype="multipart/form-data"'); ?>
          <tr>
            <td class="main" valign="top"><strong><?php echo ENTRY_PRODUCT; ?></strong> <?php echo $rInfo->products_name; ?><br /><strong><?php echo ENTRY_FROM; ?></strong> <?php echo $rInfo->customers_name; ?><br /><br /><strong><?php echo ENTRY_DATE; ?></strong> <?php echo tep_date_short($rInfo->date_added); ?></td>
            <td class="main" align="right" valign="top"><?php echo tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"'); ?></td>
          </tr>
          <tr>
            <td colspan="2" valign="top" class="main"><strong><?php echo ENTRY_REVIEW; ?></strong><br /><br /><?php echo nl2br(tep_db_output(tep_break_string($rInfo->reviews_text, 15))); ?></td>
          </tr>
      <tr>
        <td colspan="2" class="main"><strong><?php echo ENTRY_RATING; ?></strong>&nbsp;<?php echo $rating_str; ?></td>
      </tr>
<?php
    if (tep_not_null($_POST)) {
/* Re-Post all POST'ed variables */
      foreach($_POST as $key => $value) {
        echo tep_draw_hidden_field($key, htmlspecialchars(stripslashes($value)));
      }
?>
      <tr>
        <td colspan="2" align="right" class="smallText"><?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary',null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('reviews.php', 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id)); ?></td>
      </form></tr>
<?php
    } else {
      if (isset($_GET['origin'])) {
        $back_url = $_GET['origin'];
        $back_url_params = '';
      } else {
        $back_url = 'reviews.php';
        $back_url_params = 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id;
      }
?>
      <tr>
        <td colspan="2" align="right" class="smallText"><?php echo tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link($back_url, $back_url_params)); ?></td>
      </tr>
<?php
    }
?>
    </tbody>
    </table>
<?php
  } elseif ($action == 'new') {
?>
      <table class="table">
          <tbody> 
     <?php echo tep_draw_form('review', 'reviews.php', 'action=addnew'); ?>
          <tr>
            <td class="main" valign="top" width="140"><strong><?php echo ENTRY_PRODUCT; ?></strong></td>
            <td><?php echo tep_draw_products('products_id', 'required aria-required="true"'); ?></td>
          </tr>
          <tr>
            <td class="main" valign="top" width="140"><strong><?php echo ENTRY_FROM; ?></strong></td>
            <td><?php echo tep_draw_customers('customer_id', 'required aria-required="true"'); ?></td>
          </tr>
          <tr>
            <td class="main" valign="top" width="140"><strong><?php echo ENTRY_RATING; ?></strong></td>
            <td class="main"><?php echo TEXT_BAD . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . tep_draw_std_radio_field('rating', '1') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . tep_draw_std_radio_field('rating', '2') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . tep_draw_std_radio_field('rating', '3') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . tep_draw_std_radio_field('rating', '4') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . tep_draw_std_radio_field('rating', '5', 1) . '&nbsp;&nbsp;' . TEXT_GOOD; ?></td>
          </tr>
      <tr>
        <td colspan="2" class="main" valign="top"><strong><?php echo ENTRY_REVIEW; ?></strong><br /><br /><?php echo tep_draw_textarea_field('reviews_text', 'soft', '60', '15', '', 'required aria-required="true"'); ?></td>
      </tr>
      <tr>
        <td colspan="2" align="right"><?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary'); ?></td>
      </tr>
      </form>
  </tbody>
  </table>
       <?php
     } else {
     ?>
      <table class="table table-sm table-hover table-striped">
          <thead>
            <tr>
              <th scope="col"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
              <th class="text-right" scope="col"><?php echo TABLE_HEADING_RATING; ?></th>
              <th class="text-right" scope="col"><?php echo TABLE_HEADING_DATE_ADDED; ?></th>
              <th class="text-right" scope="col"><?php echo TABLE_HEADING_STATUS; ?></th>
              <th class="text-right" scope="col"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
          </thead>
          <tbody>
<?php
    $reviews_query_raw = "select reviews_id, products_id, date_added, last_modified, reviews_rating, reviews_status from " . TABLE_REVIEWS . " order by date_added DESC";
    $reviews_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $reviews_query_raw, $reviews_query_numrows);
    $reviews_query = tep_db_query($reviews_query_raw);
    while ($reviews = tep_db_fetch_array($reviews_query)) {
      if ((!isset($_GET['rID']) || (isset($_GET['rID']) && ($_GET['rID'] == $reviews['reviews_id']))) && !isset($rInfo)) {
        $reviews_text_query = tep_db_query("select r.reviews_read, r.customers_name, length(rd.reviews_text) as reviews_text_size from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.reviews_id = '" . (int)$reviews['reviews_id'] . "' and r.reviews_id = rd.reviews_id");
        $reviews_text = tep_db_fetch_array($reviews_text_query);
        $products_image_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$reviews['products_id'] . "'");
        $products_image = tep_db_fetch_array($products_image_query);
        $products_name_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$reviews['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
        $products_name = tep_db_fetch_array($products_name_query);
        $reviews_average_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int)$reviews['products_id'] . "'");
        $reviews_average = tep_db_fetch_array($reviews_average_query);
        $review_info = array_merge($reviews_text, $reviews_average, $products_name);
        $rInfo_array = array_merge($reviews, $review_info, $products_image);
        $rInfo = new objectInfo($rInfo_array);
      }
      if (isset($rInfo) && is_object($rInfo) && ($reviews['reviews_id'] == $rInfo->reviews_id) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('reviews.php', 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=preview') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('reviews.php', 'page=' . $_GET['page'] . '&rID=' . $reviews['reviews_id']) . '\'">' . "\n";
      }
      $rating_str = '';
      for ($x = 1; $x <= $reviews['reviews_rating']; $x++) {
        $rating_str .= '<i class="fa fa-star" aria-hidden="true"></i>';
      } 
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link('reviews.php', 'page=' . $_GET['page'] . '&rID=' . $reviews['reviews_id'] . '&action=preview') . '">' . '<i class="fa fa-search" aria-hidden="true"></i>' . '</a>&nbsp;' . tep_get_products_name($reviews['products_id']); ?></td>
                <td class="dataTableContent" align="right"><?php echo $rating_str; ?></td>
                <td class="dataTableContent" align="right"><?php echo tep_date_short($reviews['date_added']); ?></td>
                <td class="dataTableContent" align="center">
<?php
      if ($reviews['reviews_status'] == '1') {
        echo '<i class="fa fa-dot-circle-o" aria-hidden="true"></i>' . '&nbsp;&nbsp;<a href="' . tep_href_link('reviews.php', 'action=setflag&flag=0&rID=' . $reviews['reviews_id'] . '&page=' . $_GET['page']) . '">' . '<i class="fa fa-circle-o" aria-hidden="true"></i>' . '</a>';
      } else {
        echo '<a href="' . tep_href_link('reviews.php', 'action=setflag&flag=1&rID=' . $reviews['reviews_id'] . '&page=' . $_GET['page']) . '">' . '<i class="fa fa-circle-o" aria-hidden="true"></i>' . '</a>&nbsp;&nbsp;' . '<i class="fa fa-dot-circle-o" aria-hidden="true"></i>';
      }
?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($rInfo)) && ($reviews['reviews_id'] == $rInfo->reviews_id) ) { echo '<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>'; } else { echo '<a href="' . tep_href_link('reviews.php', 'page=' . $_GET['page'] . '&rID=' . $reviews['reviews_id']) . '">' . '<i class="fa fa-info-circle" aria-hidden="true"></i>' . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
        </tbody>
  </table>    
  <table class="table">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $reviews_split->display_count($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></td>
                    <td class="smallText" align="right"><?php echo $reviews_split->display_links($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  <tr>
                    <td colspan="2" class="smallText" align="right"><?php echo tep_draw_button(IMAGE_BUTTON_ADD_REVIEW, 'triangle-1-e', tep_href_link('reviews.php', 'action=new')); ?></td>
                  </tr>
  </table> 
  </div>       
    </div><!-- end card-body-->															
  </div><!-- end card-->	
</div><!-- end col-->        
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'delete':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_REVIEW . '</strong>');
        $contents = array('form' => tep_draw_form('reviews', 'reviews.php', 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=deleteconfirm'));
        $contents[] = array('text' => TEXT_INFO_DELETE_REVIEW_INTRO);
        $contents[] = array('text' => '<br /><strong>' . $rInfo->products_name . '</strong>');
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('reviews.php', 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id)));
        break;
      default:
      if (isset($rInfo) && is_object($rInfo)) {
        $heading[] = array('text' => '<strong>' . $rInfo->products_name . '</strong>');
        $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('reviews.php', 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=edit'), null, null, 'secondary') . ' ' . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('reviews.php', 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($rInfo->date_added));
        if (tep_not_null($rInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($rInfo->last_modified));
        $contents[] = array('text' => '<br />' . tep_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
        $contents[] = array('text' => '<br />' . TEXT_INFO_REVIEW_AUTHOR . ' ' . $rInfo->customers_name);
        $rating_str = '';
        for ($x = 1; $x <= $rInfo->reviews_rating; $x++) {
          $rating_str .= '<i class="fa fa-star" aria-hidden="true"></i>';
        } 
        $contents[] = array('text' => TEXT_INFO_REVIEW_RATING . ' ' . $rating_str);
        $contents[] = array('text' => TEXT_INFO_REVIEW_READ . ' ' . $rInfo->reviews_read);
        $contents[] = array('text' => '<br />' . TEXT_INFO_REVIEW_SIZE . ' ' . $rInfo->reviews_text_size . ' bytes');
        $contents[] = array('text' => '<br />' . TEXT_INFO_PRODUCTS_AVERAGE_RATING . ' ' . number_format($rInfo->average_rating, 2) . '%');
      }
        break;
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
<?php
  }
?>
    </div>
<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>