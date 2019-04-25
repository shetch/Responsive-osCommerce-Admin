<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2014 osCommerce
  Released under the GNU General Public License
*/
  require('includes/application_top.php');
  $languages = tep_get_languages();
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $option_page = (isset($_GET['option_page']) && is_numeric($_GET['option_page'])) ? $_GET['option_page'] : 1;
  $value_page = (isset($_GET['value_page']) && is_numeric($_GET['value_page'])) ? $_GET['value_page'] : 1;
  $attribute_page = (isset($_GET['attribute_page']) && is_numeric($_GET['attribute_page'])) ? $_GET['attribute_page'] : 1;
  $page_info = 'option_page=' . $option_page . '&value_page=' . $value_page . '&attribute_page=' . $attribute_page;
  if (tep_not_null($action)) {
    switch ($action) {
      case 'add_product_options':
        $products_options_id = tep_db_prepare_input($_POST['products_options_id']);
        $option_name_array = $_POST['option_name'];
        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $option_name = tep_db_prepare_input($option_name_array[$languages[$i]['id']]);
          tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, products_options_name, language_id) values ('" . (int)$products_options_id . "', '" . tep_db_input($option_name) . "', '" . (int)$languages[$i]['id'] . "')");
        }
        tep_redirect(tep_href_link('products_attributes.php', $page_info. '&p=2'));
        break;
      case 'add_product_option_values':
        $value_name_array = $_POST['value_name'];
        $value_id = tep_db_prepare_input($_POST['value_id']);
        $option_id = tep_db_prepare_input($_POST['option_id']);
        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $value_name = tep_db_prepare_input($value_name_array[$languages[$i]['id']]);
          tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) values ('" . (int)$value_id . "', '" . (int)$languages[$i]['id'] . "', '" . tep_db_input($value_name) . "')");
        }
        tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_id, products_options_values_id) values ('" . (int)$option_id . "', '" . (int)$value_id . "')");
        tep_redirect(tep_href_link('products_attributes.php', $page_info. '&p=3'));
        break;
      case 'add_product_attributes':
        $products_id = tep_db_prepare_input($_POST['products_id']);
        $options_id = tep_db_prepare_input($_POST['options_id']);
        $values_id = tep_db_prepare_input($_POST['values_id']);
        $value_price = tep_db_prepare_input($_POST['value_price']);
        $price_prefix = tep_db_prepare_input($_POST['price_prefix']);
        tep_db_query("insert into " . TABLE_PRODUCTS_ATTRIBUTES . " values (null, '" . (int)$products_id . "', '" . (int)$options_id . "', '" . (int)$values_id . "', '" . (float)tep_db_input($value_price) . "', '" . tep_db_input($price_prefix) . "')");
        if (DOWNLOAD_ENABLED == 'true') {
          $products_attributes_id = tep_db_insert_id();
          $products_attributes_filename = tep_db_prepare_input($_POST['products_attributes_filename']);
          $products_attributes_maxdays = tep_db_prepare_input($_POST['products_attributes_maxdays']);
          $products_attributes_maxcount = tep_db_prepare_input($_POST['products_attributes_maxcount']);
          if (tep_not_null($products_attributes_filename)) {
            tep_db_query("insert into " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " values (" . (int)$products_attributes_id . ", '" . tep_db_input($products_attributes_filename) . "', '" . tep_db_input($products_attributes_maxdays) . "', '" . tep_db_input($products_attributes_maxcount) . "')");
          }
        }
        tep_redirect(tep_href_link('products_attributes.php', $page_info));
        break;
      case 'update_option_name':
        $option_name_array = $_POST['option_name'];
        $option_id = tep_db_prepare_input($_POST['option_id']);
        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $option_name = tep_db_prepare_input($option_name_array[$languages[$i]['id']]);
          tep_db_query("update " . TABLE_PRODUCTS_OPTIONS . " set products_options_name = '" . tep_db_input($option_name) . "' where products_options_id = '" . (int)$option_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
        }
        tep_redirect(tep_href_link('products_attributes.php', $page_info. '&p=2'));
        break;
      case 'update_value':
        $value_name_array = $_POST['value_name'];
        $value_id = tep_db_prepare_input($_POST['value_id']);
        $option_id = tep_db_prepare_input($_POST['option_id']);
        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $value_name = tep_db_prepare_input($value_name_array[$languages[$i]['id']]);
          tep_db_query("update " . TABLE_PRODUCTS_OPTIONS_VALUES . " set products_options_values_name = '" . tep_db_input($value_name) . "' where products_options_values_id = '" . tep_db_input($value_id) . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
        }
        tep_db_query("update " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " set products_options_id = '" . (int)$option_id . "'  where products_options_values_id = '" . (int)$value_id . "'");
        tep_redirect(tep_href_link('products_attributes.php', $page_info. '&p=3'));
        break;
      case 'update_product_attribute':
        $products_id = tep_db_prepare_input($_POST['products_id']);
        $options_id = tep_db_prepare_input($_POST['options_id']);
        $values_id = tep_db_prepare_input($_POST['values_id']);
        $value_price = tep_db_prepare_input($_POST['value_price']);
        $price_prefix = tep_db_prepare_input($_POST['price_prefix']);
        $attribute_id = tep_db_prepare_input($_POST['attribute_id']);
        tep_db_query("update " . TABLE_PRODUCTS_ATTRIBUTES . " set products_id = '" . (int)$products_id . "', options_id = '" . (int)$options_id . "', options_values_id = '" . (int)$values_id . "', options_values_price = '" . (float)tep_db_input($value_price) . "', price_prefix = '" . tep_db_input($price_prefix) . "' where products_attributes_id = '" . (int)$attribute_id . "'");
        if (DOWNLOAD_ENABLED == 'true') {
          $products_attributes_filename = tep_db_prepare_input($_POST['products_attributes_filename']);
          $products_attributes_maxdays = tep_db_prepare_input($_POST['products_attributes_maxdays']);
          $products_attributes_maxcount = tep_db_prepare_input($_POST['products_attributes_maxcount']);
          if (tep_not_null($products_attributes_filename)) {
            tep_db_query("replace into " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " set products_attributes_id = '" . (int)$attribute_id . "', products_attributes_filename = '" . tep_db_input($products_attributes_filename) . "', products_attributes_maxdays = '" . tep_db_input($products_attributes_maxdays) . "', products_attributes_maxcount = '" . tep_db_input($products_attributes_maxcount) . "'");
          }
        }
        tep_redirect(tep_href_link('products_attributes.php', $page_info. '&p=1'));
        break;
      case 'delete_option':
        $option_id = tep_db_prepare_input($_GET['option_id']);
        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$option_id . "'");
        tep_redirect(tep_href_link('products_attributes.php', $page_info. '&p=2'));
        break;
      case 'delete_value':
        $value_id = tep_db_prepare_input($_GET['value_id']);
        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$value_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_values_id = '" . (int)$value_id . "'");
        tep_redirect(tep_href_link('products_attributes.php', $page_info. '&p=3'));
        break;
      case 'delete_attribute':
        $attribute_id = tep_db_prepare_input($_GET['attribute_id']);
        tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_attributes_id = '" . (int)$attribute_id . "'");
// added for DOWNLOAD_ENABLED. Always try to remove attributes, even if downloads are no longer enabled
        tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " where products_attributes_id = '" . (int)$attribute_id . "'");
        tep_redirect(tep_href_link('products_attributes.php', $page_info));
        break;
    }
  }
  $p = (isset($_GET['p']) ? $_GET['p'] : '');
  $attribute_active = 'active';
  $attribute_tab_active = 'show active';
  if (tep_not_null($p)) {
    $attribute_active = '';
    $option_active = '';
    $value_active = '';    
    $attribute_tab_active = '';
    $option_tab_active = '';
    $value_tab_active = '';   
    switch ($p) {
      case '1':
      $attribute_active = 'active';
      $attribute_tab_active = 'show active';
      break;
      case '2':
      $option_active = 'active';
      $option_tab_active = 'show active';
      break;
      case '3':
      $value_active = 'active';
      $value_tab_active = 'show active';
      break;
    }
  }
  if (tep_not_null($action)) {
    $attribute_active = '';
    $option_active = '';
    $value_active = '';    
    $attribute_tab_active = '';
    $option_tab_active = '';
    $value_tab_active = '';   
    switch ($action) {
        case 'update_attribute':
          $attribute_active = 'active';
          $attribute_tab_active = 'show active';
          break;      
        case 'update_option':
          $option_active = 'active';
          $option_tab_active = 'show active';
          break;
        case 'update_option_value':
          $value_active = 'active';
          $value_tab_active = 'show active';
          break;
        case 'delete_option_value':
          $value_active = 'active';
          $value_tab_active = 'show active';
          break;
        case 'delete_value':
          $value_active = 'active';
          $value_tab_active = 'show active';
          break; 
        case 'delete_product_option':
          $option_active = 'active';
          $option_tab_active = 'show active';
          break;          
        default:
          $attribute_active = 'active';
        }
  }
  require('includes/template_top.php');
?>
<style>
.btn { margin: 1px; }
th { font-weight:500; }
</style>
<div class="row">
  <div class="col-sm-12">
    <div class="card mb-3">
      <div class="card-header">
        <h3><i class="fa fa-plug"></i> <?php echo HEADING_TITLE_ATRIB; ?>
        <span style="float:right"></span></h3>
      </div>               
      <div class="card-body">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link <?php echo $attribute_active; ?>" id="attributes-tab" data-toggle="tab" href="#attributes" role="tab" aria-controls="attributes" aria-selected="true"><i class="fa fa-plug" aria-hidden="true"></i> <?php echo HEADING_TITLE_ATRIB; ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo $option_active; ?>" id="options-tab" data-toggle="tab" href="#options" role="tab" aria-controls="options" aria-selected="false"><i class="fa fa-key" aria-hidden="true"></i> <?php echo HEADING_TITLE_OPT; ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo $value_active; ?>" id="values-tab" data-toggle="tab" href="#values" role="tab" aria-controls="values" aria-selected="false"><i class="fa fa-sitemap" aria-hidden="true"></i> <?php echo HEADING_TITLE_VAL; ?></a>
          </li>
        </ul>
        <div class="tab-content" id="attribute-tabs">
              <div class="tab-pane fade <?php echo $attribute_tab_active; ?>" id="attributes" role="tabpanel" aria-labelledby="attributes">
                <!-- bof attributes -->  
                <?php require('includes/modules/products/attributes.php'); ?>
                <!-- eof attributes -->
              </div>
              <div class="tab-pane fade <?php echo $option_tab_active; ?>" id="options" role="tabpanel" aria-labelledby="options">
                <!-- bof options -->  
                <?php require('includes/modules/products/options.php'); ?>
                <!-- eof options -->
              </div>
              <div class="tab-pane fade <?php echo $value_tab_active; ?>" id="values" role="tabpanel" aria-labelledby="values">
                <!-- bof values//-->
                <?php require('includes/modules/products/option_values.php'); ?>
                <!-- eof values -->
              </div>
        </div>
     </div><!-- end card-body-->															
   </div><!-- end card-->	
  </div><!-- end col-->
</div>
<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>