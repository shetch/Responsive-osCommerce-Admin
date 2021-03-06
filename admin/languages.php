<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2013 osCommerce
  Released under the GNU General Public License
*/
  require('includes/application_top.php');
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
        $name = tep_db_prepare_input($_POST['name']);
        $code = tep_db_prepare_input(substr($_POST['code'], 0, 2));
        $image = tep_db_prepare_input($_POST['image']);
        $directory = tep_db_prepare_input($_POST['directory']);
        $sort_order = (int)tep_db_prepare_input($_POST['sort_order']);
        tep_db_query("insert into " . TABLE_LANGUAGES . " (name, code, image, directory, sort_order) values ('" . tep_db_input($name) . "', '" . tep_db_input($code) . "', '" . tep_db_input($image) . "', '" . tep_db_input($directory) . "', '" . tep_db_input($sort_order) . "')");
        $insert_id = tep_db_insert_id();
// create additional categories_description records
        $categories_query = tep_db_query("select c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id where cd.language_id = '" . (int)$languages_id . "'");
        while ($categories = tep_db_fetch_array($categories_query)) {
          tep_db_query("insert into " . TABLE_CATEGORIES_DESCRIPTION . " (categories_id, language_id, categories_name) values ('" . (int)$categories['categories_id'] . "', '" . (int)$insert_id . "', '" . tep_db_input($categories['categories_name']) . "')");
        }
// create additional products_description records
        $products_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, pd.products_url from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id where pd.language_id = '" . (int)$languages_id . "'");
        while ($products = tep_db_fetch_array($products_query)) {
          tep_db_query("insert into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_url) values ('" . (int)$products['products_id'] . "', '" . (int)$insert_id . "', '" . tep_db_input($products['products_name']) . "', '" . tep_db_input($products['products_description']) . "', '" . tep_db_input($products['products_url']) . "')");
        }
// create additional products_options records
        $products_options_query = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$languages_id . "'");
        while ($products_options = tep_db_fetch_array($products_options_query)) {
          tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, language_id, products_options_name) values ('" . (int)$products_options['products_options_id'] . "', '" . (int)$insert_id . "', '" . tep_db_input($products_options['products_options_name']) . "')");
        }
// create additional products_options_values records
        $products_options_values_query = tep_db_query("select products_options_values_id, products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . (int)$languages_id . "'");
        while ($products_options_values = tep_db_fetch_array($products_options_values_query)) {
          tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) values ('" . (int)$products_options_values['products_options_values_id'] . "', '" . (int)$insert_id . "', '" . tep_db_input($products_options_values['products_options_values_name']) . "')");
        }
// create additional manufacturers_info records
        $manufacturers_query = tep_db_query("select m.manufacturers_id, mi.manufacturers_url from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on m.manufacturers_id = mi.manufacturers_id where mi.languages_id = '" . (int)$languages_id . "'");
        while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
          tep_db_query("insert into " . TABLE_MANUFACTURERS_INFO . " (manufacturers_id, languages_id, manufacturers_url) values ('" . $manufacturers['manufacturers_id'] . "', '" . (int)$insert_id . "', '" . tep_db_input($manufacturers['manufacturers_url']) . "')");
        }
// create additional orders_status records
        $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "'");
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
          tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . (int)$orders_status['orders_status_id'] . "', '" . (int)$insert_id . "', '" . tep_db_input($orders_status['orders_status_name']) . "')");
        }
        if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($code) . "' where configuration_key = 'DEFAULT_LANGUAGE'");
        }
        tep_redirect(tep_href_link('languages.php', (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'lID=' . $insert_id));
        break;
      case 'save':
        $lID = tep_db_prepare_input($_GET['lID']);
        $name = tep_db_prepare_input($_POST['name']);
        $code = tep_db_prepare_input(substr($_POST['code'], 0, 2));
        $image = tep_db_prepare_input($_POST['image']);
        $directory = tep_db_prepare_input($_POST['directory']);
        $sort_order = (int)tep_db_prepare_input($_POST['sort_order']);
        tep_db_query("update " . TABLE_LANGUAGES . " set name = '" . tep_db_input($name) . "', code = '" . tep_db_input($code) . "', image = '" . tep_db_input($image) . "', directory = '" . tep_db_input($directory) . "', sort_order = '" . tep_db_input($sort_order) . "' where languages_id = '" . (int)$lID . "'");
        if ($_POST['default'] == 'on') {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($code) . "' where configuration_key = 'DEFAULT_LANGUAGE'");
        }
        tep_redirect(tep_href_link('languages.php', 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']));
        break;
      case 'deleteconfirm':
        $lID = tep_db_prepare_input($_GET['lID']);
        $lng_query = tep_db_query("select languages_id from " . TABLE_LANGUAGES . " where code = '" . DEFAULT_CURRENCY . "'");
        $lng = tep_db_fetch_array($lng_query);
        if ($lng['languages_id'] == $lID) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_CURRENCY'");
        }
        tep_db_query("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where language_id = '" . (int)$lID . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . (int)$lID . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$lID . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . (int)$lID . "'");
        tep_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int)$lID . "'");
        tep_db_query("delete from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$lID . "'");
        tep_db_query("delete from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$lID . "'");
        tep_redirect(tep_href_link('languages.php', 'page=' . $_GET['page']));
        break;
      case 'delete':
        $lID = tep_db_prepare_input($_GET['lID']);
        $lng_query = tep_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$lID . "'");
        $lng = tep_db_fetch_array($lng_query);
        $remove_language = true;
        if ($lng['code'] == DEFAULT_LANGUAGE) {
          $remove_language = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_LANGUAGE, 'error');
        }
        break;
    }
  }
  require('includes/template_top.php');
?>
<div class="row">
  <div class="col-sm-9">
        <div class="card mb-3">
                  <div class="card-header">
                    <h3><i class="fa fa-comment-o"></i> <?php echo HEADING_TITLE; ?>
                    <span style="float:right">
                    <?php
                      if (empty($action)) {
                    ?>
                                     <tr>
                                        <td class="smallText" align="right" colspan="2"><?php echo tep_draw_button(IMAGE_NEW_LANGUAGE, 'plus', tep_href_link('languages.php', 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=new')); ?></td>
                                      </tr>
                    <?php
                      }
                    ?>                  
                  </span></h3>
                  </div>
                  <div class="card-body">  
                  <table class="table table-sm table-hover">
                  <thead>
                    <tr>
                      <th scope="col"><?php echo TABLE_HEADING_LANGUAGE_NAME; ?></th>
                      <th scope="col"><?php echo TABLE_HEADING_LANGUAGE_CODE; ?></th>
                      <th scope="col"><?php echo TABLE_HEADING_ACTION; ?></th>
                    </tr>
                  </thead>
                  <tbody>
<?php
  $languages_query_raw = "select languages_id, name, code, image, directory, sort_order from " . TABLE_LANGUAGES . " order by sort_order";
  $languages_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $languages_query_raw, $languages_query_numrows);
  $languages_query = tep_db_query($languages_query_raw);
  while ($languages = tep_db_fetch_array($languages_query)) {
    if ((!isset($_GET['lID']) || (isset($_GET['lID']) && ($_GET['lID'] == $languages['languages_id']))) && !isset($lInfo) && (substr($action, 0, 3) != 'new')) {
      $lInfo = new objectInfo($languages);
    }
    if (isset($lInfo) && is_object($lInfo) && ($languages['languages_id'] == $lInfo->languages_id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('languages.php', 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('languages.php', 'page=' . $_GET['page'] . '&lID=' . $languages['languages_id']) . '\'">' . "\n";
    }
    if (DEFAULT_LANGUAGE == $languages['code']) {
      echo '                <td class="dataTableContent"><strong>' . $languages['name'] . ' (' . TEXT_DEFAULT . ')</strong></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $languages['name'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $languages['code']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($lInfo) && is_object($lInfo) && ($languages['languages_id'] == $lInfo->languages_id)) { echo  '<i class="fa fa-chevron-circle-right"></i>'; } else { echo '<a href="' . tep_href_link('languages.php', 'page=' . $_GET['page'] . '&lID=' . $languages['languages_id']) . '">' . '<i class="fa fa-info-circle" aria-hidden="true"></i>' . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
             <!--  <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $languages_split->display_count($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_LANGUAGES); ?></td>
                    <td class="smallText" align="right"><?php echo $languages_split->display_links($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr> -->
   </tbody>         
                </table>
            </div><!-- end card-body-->															
        </div><!-- end card-->	
  </div><!-- end col-->
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_LANGUAGE . '</strong>');
      $contents = array('form' => tep_draw_form('languages', 'languages.php', 'action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_NAME . '<br />' . tep_draw_input_field('name'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_CODE . '<br />' . tep_draw_input_field('code'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_IMAGE . '<br />' . tep_draw_input_field('image', 'icon.gif'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . tep_draw_input_field('directory'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br />' . tep_draw_input_field('sort_order'));
      $contents[] = array('text' => '<br />' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('languages.php', 'page=' . $_GET['page'] . '&lID=' . $_GET['lID'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_LANGUAGE . '</strong>');
      $contents = array('form' => tep_draw_form('languages', 'languages.php', 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_NAME . '<br />' . tep_draw_input_field('name', $lInfo->name));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_CODE . '<br />' . tep_draw_input_field('code', $lInfo->code));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_IMAGE . '<br />' . tep_draw_input_field('image', $lInfo->image));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . tep_draw_input_field('directory', $lInfo->directory));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br />' . tep_draw_input_field('sort_order', $lInfo->sort_order));
      if (DEFAULT_LANGUAGE != $lInfo->code) $contents[] = array('text' => '<br />' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('languages.php', 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_LANGUAGE . '</strong>');
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $lInfo->name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . (($remove_language) ? tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('languages.php', 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=deleteconfirm'), 'primary', null, 'secondary') : '') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('languages.php', 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id)));
      break;
    default:
      if (is_object($lInfo)) {
        $heading[] = array('text' => '<strong>' . $lInfo->name . '</strong>');
        $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('languages.php', 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit'), null, null, 'primary') . ' ' . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('languages.php', 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=delete'), null, null, 'secondary') . ' ' . tep_draw_button(IMAGE_DETAILS, 'info', tep_href_link('define_language.php', 'lngdir=' . $lInfo->directory)));
        $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_NAME . ' ' . $lInfo->name);
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . ' ' . $lInfo->code);
        $contents[] = array('text' => '<br />' . tep_image(tep_catalog_href_link('includes/languages/' . $lInfo->directory . '/images/' . $lInfo->image, '', 'SSL'), $lInfo->name));
        $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . DIR_WS_CATALOG_LANGUAGES . '<strong>' . $lInfo->directory . '</strong>');
        $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . ' ' . $lInfo->sort_order);
      }
      break;
  }
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
</div>
<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
