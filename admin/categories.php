<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2015 osCommerce
  Released under the GNU General Public License
*/
  require('includes/application_top.php');
  require('includes/classes/currencies.php');
  $currencies = new currencies();
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
          if (isset($_GET['pID'])) {
            tep_set_product_status($_GET['pID'], $_GET['flag']);
          }
          if (USE_CACHE == 'true') {
            tep_reset_cache_block('categories');
            tep_reset_cache_block('also_purchased');
          }
        }
        tep_redirect(tep_href_link('categories.php', 'cPath=' . $_GET['cPath'] . '&pID=' . $_GET['pID']));
        break;
      case 'insert_category':
      case 'update_category':
        if (isset($_POST['categories_id'])) $categories_id = tep_db_prepare_input($_POST['categories_id']);
        $sort_order = tep_db_prepare_input($_POST['sort_order']);
        $sql_data_array = array('sort_order' => (int)$sort_order);
        if ($action == 'insert_category') {
          $insert_sql_data = array('parent_id' => $current_category_id,
                                   'date_added' => 'now()');
          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
          tep_db_perform(TABLE_CATEGORIES, $sql_data_array);
          $categories_id = tep_db_insert_id();
        } elseif ($action == 'update_category') {
          $update_sql_data = array('last_modified' => 'now()');
          $sql_data_array = array_merge($sql_data_array, $update_sql_data);
          tep_db_perform(TABLE_CATEGORIES, $sql_data_array, 'update', "categories_id = '" . (int)$categories_id . "'");
        }
        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $categories_name_array = $_POST['categories_name'];
          $categories_description_array = $_POST['categories_description'];
          $categories_seo_description_array = $_POST['categories_seo_description'];
          $categories_seo_keywords_array = $_POST['categories_seo_keywords'];
          $categories_seo_title_array = $_POST['categories_seo_title'];
          $language_id = $languages[$i]['id'];
          $sql_data_array = array('categories_name' => tep_db_prepare_input($categories_name_array[$language_id]));
          $sql_data_array['categories_description'] = tep_db_prepare_input($categories_description_array[$language_id]);
          $sql_data_array['categories_seo_description'] = tep_db_prepare_input($categories_seo_description_array[$language_id]);
          $sql_data_array['categories_seo_keywords'] = tep_db_prepare_input($categories_seo_keywords_array[$language_id]);
          $sql_data_array['categories_seo_title'] = tep_db_prepare_input($categories_seo_title_array[$language_id]);
          if ($action == 'insert_category') {
            $insert_sql_data = array('categories_id' => $categories_id,
                                     'language_id' => $languages[$i]['id']);
            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
            tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array);
          } elseif ($action == 'update_category') {
            tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array, 'update', "categories_id = '" . (int)$categories_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
          }
        }
        $categories_image = new upload('categories_image');
        $categories_image->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($categories_image->parse() && $categories_image->save()) {
          tep_db_query("update " . TABLE_CATEGORIES . " set categories_image = '" . tep_db_input($categories_image->filename) . "' where categories_id = '" . (int)$categories_id . "'");
        }
        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }
        tep_redirect(tep_href_link('categories.php', 'cPath=' . $cPath . '&cID=' . $categories_id));
        break;
      case 'delete_category_confirm':
        if (isset($_POST['categories_id'])) {
          $categories_id = tep_db_prepare_input($_POST['categories_id']);
          $categories = tep_get_category_tree($categories_id, '', '0', '', true);
          $products = array();
          $products_delete = array();
          for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
            $product_ids_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int)$categories[$i]['id'] . "'");
            while ($product_ids = tep_db_fetch_array($product_ids_query)) {
              $products[$product_ids['products_id']]['categories'][] = $categories[$i]['id'];
            }
          }
          foreach ($products as $key => $value) {
            $category_ids = '';
            for ($i=0, $n=sizeof($value['categories']); $i<$n; $i++) {
              $category_ids .= "'" . (int)$value['categories'][$i] . "', ";
            }
            $category_ids = substr($category_ids, 0, -2);
            $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$key . "' and categories_id not in (" . $category_ids . ")");
            $check = tep_db_fetch_array($check_query);
            if ($check['total'] < '1') {
              $products_delete[$key] = $key;
            }
          }
// removing categories can be a lengthy process
          tep_set_time_limit(0);
          for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
            tep_remove_category($categories[$i]['id']);
          }
          foreach ($products_delete as $key) {
            tep_remove_product($key);
          }
        }
        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }
        tep_redirect(tep_href_link('categories.php', 'cPath=' . $cPath));
        break;
      case 'delete_product_confirm':
        if (isset($_POST['products_id']) && isset($_POST['product_categories']) && is_array($_POST['product_categories'])) {
          $product_id = tep_db_prepare_input($_POST['products_id']);
          $product_categories = $_POST['product_categories'];
          for ($i=0, $n=sizeof($product_categories); $i<$n; $i++) {
            tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "' and categories_id = '" . (int)$product_categories[$i] . "'");
          }
          $product_categories_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "'");
          $product_categories = tep_db_fetch_array($product_categories_query);
          if ($product_categories['total'] == '0') {
            tep_remove_product($product_id);
          }
        }
        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }
        tep_redirect(tep_href_link('categories.php', 'cPath=' . $cPath));
        break;
      case 'move_category_confirm':
        if (isset($_POST['categories_id']) && ($_POST['categories_id'] != $_POST['move_to_category_id'])) {
          $categories_id = tep_db_prepare_input($_POST['categories_id']);
          $new_parent_id = tep_db_prepare_input($_POST['move_to_category_id']);
          $path = explode('_', tep_get_generated_category_path_ids($new_parent_id));
          if (in_array($categories_id, $path)) {
            $messageStack->add_session(ERROR_CANNOT_MOVE_CATEGORY_TO_PARENT, 'error');
            tep_redirect(tep_href_link('categories.php', 'cPath=' . $cPath . '&cID=' . $categories_id));
          } else {
            tep_db_query("update " . TABLE_CATEGORIES . " set parent_id = '" . (int)$new_parent_id . "', last_modified = now() where categories_id = '" . (int)$categories_id . "'");
            if (USE_CACHE == 'true') {
              tep_reset_cache_block('categories');
              tep_reset_cache_block('also_purchased');
            }
            tep_redirect(tep_href_link('categories.php', 'cPath=' . $new_parent_id . '&cID=' . $categories_id));
          }
        }
        break;
      case 'move_product_confirm':
        $products_id = tep_db_prepare_input($_POST['products_id']);
        $new_parent_id = tep_db_prepare_input($_POST['move_to_category_id']);
        $duplicate_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "' and categories_id = '" . (int)$new_parent_id . "'");
        $duplicate_check = tep_db_fetch_array($duplicate_check_query);
        if ($duplicate_check['total'] < 1) tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set categories_id = '" . (int)$new_parent_id . "' where products_id = '" . (int)$products_id . "' and categories_id = '" . (int)$current_category_id . "'");
        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }
        tep_redirect(tep_href_link('categories.php', 'cPath=' . $new_parent_id . '&pID=' . $products_id));
        break;
      case 'insert_product':
      case 'update_product':
        if (isset($_GET['pID'])) $products_id = tep_db_prepare_input($_GET['pID']);
        $products_date_available = tep_db_prepare_input($_POST['products_date_available']);
        $products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';
        $sql_data_array = array('products_quantity' => (int)tep_db_prepare_input($_POST['products_quantity']),
                                'products_model' => tep_db_prepare_input($_POST['products_model']),
                                'products_price' => tep_db_prepare_input($_POST['products_price']),
                                'products_date_available' => $products_date_available,
                                'products_weight' => (float)tep_db_prepare_input($_POST['products_weight']),
                                'products_status' => tep_db_prepare_input($_POST['products_status']),
                                'products_tax_class_id' => tep_db_prepare_input($_POST['products_tax_class_id']),
                                'manufacturers_id' => (int)tep_db_prepare_input($_POST['manufacturers_id']));
        $sql_data_array['products_gtin'] = (tep_not_null($_POST['products_gtin'])) ? str_pad(tep_db_prepare_input($_POST['products_gtin']), 14, '0', STR_PAD_LEFT) : 'null';
        $products_image = new upload('products_image');
        $products_image->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image->parse() && $products_image->save()) {
          $sql_data_array['products_image'] = tep_db_prepare_input($products_image->filename);
        }
        if ($action == 'insert_product') {
          $insert_sql_data = array('products_date_added' => 'now()');
          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
          tep_db_perform(TABLE_PRODUCTS, $sql_data_array);
          $products_id = tep_db_insert_id();
          tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products_id . "', '" . (int)$current_category_id . "')");
        } elseif ($action == 'update_product') {
          $update_sql_data = array('products_last_modified' => 'now()');
          $sql_data_array = array_merge($sql_data_array, $update_sql_data);
          tep_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");
        }
        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $language_id = $languages[$i]['id'];
          $sql_data_array = array('products_name' => tep_db_prepare_input($_POST['products_name'][$language_id]),
                                  'products_description' => tep_db_prepare_input($_POST['products_description'][$language_id]),
                                  'products_url' => tep_db_prepare_input($_POST['products_url'][$language_id]));
          $sql_data_array['products_seo_description'] = tep_db_prepare_input($_POST['products_seo_description'][$language_id]);
          $sql_data_array['products_seo_keywords'] = tep_db_prepare_input($_POST['products_seo_keywords'][$language_id]);
          $sql_data_array['products_seo_title'] = tep_db_prepare_input($_POST['products_seo_title'][$language_id]);
          if ($action == 'insert_product') {
            $insert_sql_data = array('products_id' => $products_id,
                                     'language_id' => $language_id);
            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
            tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
          } elseif ($action == 'update_product') {
            tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and language_id = '" . (int)$language_id . "'");
          }
        }
        $pi_sort_order = 0;
        $piArray = array(0);
        foreach ($_FILES as $key => $value) {
// Update existing large product images
          if (preg_match('/^products_image_large_([0-9]+)$/', $key, $matches)) {
            $pi_sort_order++;
            $sql_data_array = array('htmlcontent' => tep_db_prepare_input($_POST['products_image_htmlcontent_' . $matches[1]]),
                                    'sort_order' => $pi_sort_order);
            $t = new upload($key);
            $t->set_destination(DIR_FS_CATALOG_IMAGES);
            if ($t->parse() && $t->save()) {
              $sql_data_array['image'] = tep_db_prepare_input($t->filename);
            }
            tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and id = '" . (int)$matches[1] . "'");
            $piArray[] = (int)$matches[1];
          } elseif (preg_match('/^products_image_large_new_([0-9]+)$/', $key, $matches)) {
// Insert new large product images
            $sql_data_array = array('products_id' => (int)$products_id,
                                    'htmlcontent' => tep_db_prepare_input($_POST['products_image_htmlcontent_new_' . $matches[1]]));
            $t = new upload($key);
            $t->set_destination(DIR_FS_CATALOG_IMAGES);
            if ($t->parse() && $t->save()) {
              $pi_sort_order++;
              $sql_data_array['image'] = tep_db_prepare_input($t->filename);
              $sql_data_array['sort_order'] = $pi_sort_order;
              tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array);
              $piArray[] = tep_db_insert_id();
            }
          }
        }
        $product_images_query = tep_db_query("select image from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "' and id not in (" . implode(',', $piArray) . ")");
        if (tep_db_num_rows($product_images_query)) {
          while ($product_images = tep_db_fetch_array($product_images_query)) {
            $duplicate_image_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_IMAGES . " where image = '" . tep_db_input($product_images['image']) . "'");
            $duplicate_image = tep_db_fetch_array($duplicate_image_query);
            if ($duplicate_image['total'] < 2) {
              if (file_exists(DIR_FS_CATALOG_IMAGES . $product_images['image'])) {
                @unlink(DIR_FS_CATALOG_IMAGES . $product_images['image']);
              }
            }
          }
          tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "' and id not in (" . implode(',', $piArray) . ")");
        }
        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }
        tep_redirect(tep_href_link('categories.php', 'cPath=' . $cPath . '&pID=' . $products_id));
        break;
      case 'copy_to_confirm':
        if (isset($_POST['products_id']) && isset($_POST['categories_id'])) {
          $products_id = tep_db_prepare_input($_POST['products_id']);
          $categories_id = tep_db_prepare_input($_POST['categories_id']);
          if ($_POST['copy_as'] == 'link') {
            if ($categories_id != $current_category_id) {
              $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "' and categories_id = '" . (int)$categories_id . "'");
              $check = tep_db_fetch_array($check_query);
              if ($check['total'] < '1') {
                tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products_id . "', '" . (int)$categories_id . "')");
              }
            } else {
              $messageStack->add_session(ERROR_CANNOT_LINK_TO_SAME_CATEGORY, 'error');
            }
          } elseif ($_POST['copy_as'] == 'duplicate') {
            $product_query = tep_db_query("select products_quantity, products_model, products_image, products_price, products_date_available, products_weight, products_tax_class_id, manufacturers_id, products_gtin from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
            $product = tep_db_fetch_array($product_query);
            tep_db_query("insert into " . TABLE_PRODUCTS . " (products_quantity, products_model,products_image, products_price, products_date_added, products_date_available, products_weight, products_status, products_tax_class_id, manufacturers_id, products_gtin) values ('" . tep_db_input($product['products_quantity']) . "', '" . tep_db_input($product['products_model']) . "', '" . tep_db_input($product['products_image']) . "', '" . tep_db_input($product['products_price']) . "',  now(), " . (empty($product['products_date_available']) ? "null" : "'" . tep_db_input($product['products_date_available']) . "'") . ", '" . tep_db_input($product['products_weight']) . "', '0', '" . (int)$product['products_tax_class_id'] . "', '" . (int)$product['manufacturers_id'] . "', '" . tep_db_input($product['products_gtin']) . "')");
            $dup_products_id = tep_db_insert_id();
            $attributes_query = tep_db_query("select products_attributes_id, options_id, options_values_id, options_values_price, price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = " . (int)$products_id);
            if (tep_db_num_rows($attributes_query) > 0) {
              while ($attributes = tep_db_fetch_array($attributes_query)) { 
                tep_db_query("insert into " . TABLE_PRODUCTS_ATTRIBUTES . " (products_id, options_id, options_values_id, options_values_price, price_prefix) values ('" . (int)$dup_products_id . "', '" . (int)$attributes['options_id'] . "', '" . (int)$attributes['options_values_id'] . "', '" . tep_db_input($attributes['options_values_price']) . "', '" . tep_db_input($attributes['price_prefix']) . "')");
              } 
            }
            $description_query = tep_db_query("select language_id, products_name, products_description, products_url, products_seo_title, products_seo_description, products_seo_keywords from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "'");
            while ($description = tep_db_fetch_array($description_query)) {
              tep_db_query("insert into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_url, products_viewed, products_seo_title, products_seo_description, products_seo_keywords) values ('" . (int)$dup_products_id . "', '" . (int)$description['language_id'] . "', '" . tep_db_input($description['products_name']) . "', '" . tep_db_input($description['products_description']) . "', '" . tep_db_input($description['products_url']) . "', '0', '" . tep_db_input($description['products_seo_title']) . "', '" . tep_db_input($description['products_seo_description']) . "', '" . tep_db_input($description['products_seo_keywords']) . "')");
            }
            $product_images_query = tep_db_query("select image, htmlcontent, sort_order from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "'");
            while ($product_images = tep_db_fetch_array($product_images_query)) {
              tep_db_query("insert into " . TABLE_PRODUCTS_IMAGES . " (products_id, image, htmlcontent, sort_order) values ('" . (int)$dup_products_id . "', '" . tep_db_input($product_images['image']) . "', '" . tep_db_input($product_images['htmlcontent']) . "', '" . tep_db_input($product_images['sort_order']) . "')");
            }
            tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$dup_products_id . "', '" . (int)$categories_id . "')");
            $products_id = $dup_products_id;
          }
          if (USE_CACHE == 'true') {
            tep_reset_cache_block('categories');
            tep_reset_cache_block('also_purchased');
          }
        }
        tep_redirect(tep_href_link('categories.php', 'cPath=' . $categories_id . '&pID=' . $products_id));
        break;
    }
  }
// check if the catalog image directory exists
  if (is_dir(DIR_FS_CATALOG_IMAGES)) {
    if (!tep_is_writable(DIR_FS_CATALOG_IMAGES)) $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, 'error');
  } else {
    $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, 'error');
  }
  require('includes/template_top.php');
  if ($action == 'new_product') {
    $parameters = array('products_name' => '',
                       'products_description' => '',
                       'products_url' => '',
                       'products_id' => '',
                       'products_quantity' => '',
                       'products_model' => '',
                       'products_image' => '',
                       'products_larger_images' => array(),
                       'products_price' => '',
                       'products_weight' => '',
                       'products_date_added' => '',
                       'products_last_modified' => '',
                       'products_date_available' => '',
                       'products_status' => '',
                       'products_tax_class_id' => '',
                       'manufacturers_id' => '');
    $parameters['products_gtin'] = '';
    $parameters['products_seo_description'] = '';
    $parameters['products_seo_keywords'] = '';
    $parameters['products_seo_title'] = '';
    $pInfo = new objectInfo($parameters);
    if (isset($_GET['pID']) && empty($_POST)) {
      $product_query = tep_db_query("select pd.products_name, pd.products_description, pd.products_url, p.products_id, p.products_quantity, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_date_added, p.products_last_modified, date_format(p.products_date_available, '%Y-%m-%d') as products_date_available, p.products_status, p.products_tax_class_id, p.manufacturers_id, p.products_gtin from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$_GET['pID'] . "' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'");
      $product = tep_db_fetch_array($product_query);
      $pInfo->objectInfo($product);
      $product_images_query = tep_db_query("select id, image, htmlcontent, sort_order from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$product['products_id'] . "' order by sort_order");
      while ($product_images = tep_db_fetch_array($product_images_query)) {
        $pInfo->products_larger_images[] = array('id' => $product_images['id'],
                                                 'image' => $product_images['image'],
                                                 'htmlcontent' => $product_images['htmlcontent'],
                                                 'sort_order' => $product_images['sort_order']);
      }
    }
    $manufacturers_array = array(array('id' => '', 'text' => TEXT_NONE));
    $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
    while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
      $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'],
                                     'text' => $manufacturers['manufacturers_name']);
    }
    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
    while ($tax_class = tep_db_fetch_array($tax_class_query)) {
      $tax_class_array[] = array('id' => $tax_class['tax_class_id'],
                                 'text' => $tax_class['tax_class_title']);
    }
    $languages = tep_get_languages();
    if (!isset($pInfo->products_status)) $pInfo->products_status = '1';
    switch ($pInfo->products_status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
    }
    $form_action = (isset($_GET['pID'])) ? 'update_product' : 'insert_product';
?>
<script type="text/javascript"><!--
var tax_rates = new Array();
<?php
    for ($i=0, $n=sizeof($tax_class_array); $i<$n; $i++) {
      if ($tax_class_array[$i]['id'] > 0) {
        echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . tep_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
      }
    }
?>
function doRound(x, places) {
  return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}
function getTaxRate() {
  var selected_value = document.forms["new_product"].products_tax_class_id.selectedIndex;
  var parameterVal = document.forms["new_product"].products_tax_class_id[selected_value].value;
  if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
    return tax_rates[parameterVal];
  } else {
    return 0;
  }
}
function updateGross() {
  var taxRate = getTaxRate();
  var grossValue = document.forms["new_product"].products_price.value;
  if (taxRate > 0) {
    grossValue = grossValue * ((taxRate / 100) + 1);
  }
  document.forms["new_product"].products_price_gross.value = doRound(grossValue, 4);
}
function updateNet() {
  var taxRate = getTaxRate();
  var netValue = document.forms["new_product"].products_price_gross.value;
  if (taxRate > 0) {
    netValue = netValue / ((taxRate / 100) + 1);
  }
  document.forms["new_product"].products_price.value = doRound(netValue, 4);
}
//--></script>
<div class="row">
  <div class="col-sm-12">
  <?php echo tep_draw_form('new_product', 'categories.php', 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"'); ?>
<div class="card mb-3">
  <div class="card-header">
    <h6><i class="fa fa-barcode"></i> Edit/Create Product
    <span style="float:right">
        <?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('categories.php', 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : ''))); ?>
    </span></h6>
  </div>
  <div class="card-body">      
      <div class="row jumbotron">
      <div class="col-sm-12">
            <div class="form-group">
              <label><?php echo TEXT_PRODUCTS_STATUS; ?></label>&nbsp;&nbsp;
              <?php echo tep_draw_bs_radio_field('products_status', '1', $in_status, null, TEXT_PRODUCT_AVAILABLE, 'form-check-inline') . tep_draw_bs_radio_field('products_status', '0', $out_status, null, TEXT_PRODUCT_NOT_AVAILABLE, 'form-check-inline'); ?> 
            </div>
          </div>
      <div class="col-sm-4">
                <?php
                    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
                ?>                        
                  <?php if ($i == 0) echo '<label>'.TEXT_PRODUCTS_NAME.'</label>'; ?>
                  <div class="form-group">
                    <div class="input-group input-group-sm mb-3">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1"><?php echo $languages[$i]['code']; ?></span>
                      </div>
                      <?php echo tep_draw_input_field('products_name[' . $languages[$i]['id'] . ']', (empty($pInfo->products_id) ? '' : tep_get_products_name($pInfo->products_id, $languages[$i]['id'])));?>
                    </div>          
                  </div>
                <?php
                    }
                ?>
                <div class="form-group">
                    <?php echo '<label>'.TEXT_PRODUCTS_TAX_CLASS.'</label>'; ?>
                    <?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id, 'onchange="updateGross()"'); ?>
                </div>  
                <div class="form-group">
                    <?php echo '<label>'.TEXT_PRODUCTS_PRICE_NET.'</label>'; ?>
                    <?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_price', $pInfo->products_price, 'onkeyup="updateGross()"'); ?>
                </div>  
                <div class="form-group">
                  <?php echo '<label>'.TEXT_PRODUCTS_PRICE_GROSS.'</label>'; ?>
                  <?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_price_gross', $pInfo->products_price, 'onkeyup="updateNet()"'); ?>
                </div>  
                <script type="text/javascript"><!--
                  updateGross();
                //--></script>
        </div>
        <div class="col-sm-4">
                <?php
                for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
                ?>
                <?php if ($i == 0) echo '<label>'.TEXT_PRODUCTS_SEO_TITLE.'</label>'; ?>
                    <div class="form-group">
                      <div class="input-group input-group-sm mb-3">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1"><?php echo $languages[$i]['code']; ?></span>
                      </div>
                      <?php echo tep_draw_input_field('products_seo_title[' . $languages[$i]['id'] . ']', (empty($pInfo->products_id) ? '' : tep_get_products_seo_title($pInfo->products_id, $languages[$i]['id']))); ?>
                      </div>          
                    </div>
                <?php
                }
                ?>              
                <div class="form-group">
                  <?php echo '<label>'.TEXT_PRODUCTS_QUANTITY.'</label>'; ?>
                  <?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_quantity', $pInfo->products_quantity); ?>
                </div>  
                <div class="form-group">
                  <?php echo '<label>'.TEXT_PRODUCTS_MANUFACTURER.'</label>'; ?>
                  <?php echo tep_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $pInfo->manufacturers_id); ?>
                </div>
                <div class="form-group">
                  <?php echo '<label>'.TEXT_PRODUCTS_WEIGHT.'</label>'; ?>
                  <?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_weight', $pInfo->products_weight); ?>
                </div>  
        </div> 
        <div class="col-sm-4">
                <?php
                    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
                      ?>
                    <?php if ($i == 0) echo '<label>'.TEXT_PRODUCTS_URL.'</label>' . TEXT_PRODUCTS_URL_WITHOUT_HTTP; ?>
                    <div class="form-group">
                      <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><?php echo $languages[$i]['code']; ?></span>
                        </div>
                        <?php echo tep_draw_input_field('products_url[' . $languages[$i]['id'] . ']', (isset($products_url[$languages[$i]['id']]) ? stripslashes($products_url[$languages[$i]['id']]) : tep_get_products_url($pInfo->products_id, $languages[$i]['id']))); ?>
                      </div>          
                    </div>
                    <?php
                        }
                        ?>      
                    <div class="form-group">
                        <?php echo '<label>'.TEXT_PRODUCTS_MODEL.'</label>'; ?>
                        <?php echo tep_draw_input_field('products_model', $pInfo->products_model); ?>
                    </div>  
                    <div class="form-group">
                      <?php echo '<label>'.TEXT_PRODUCTS_DATE_AVAILABLE.'</label>'; ?> <small>(YYYY-MM-DD)</small>
                      <?php echo tep_draw_input_field('products_date_available', $pInfo->products_date_available, 'id="products_date_available"') . ' '; ?>
                    </div>
                    <div class="form-group">
                      <?php echo '<label>'.TEXT_PRODUCTS_GTIN.'</label>'; ?>
                      <?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_gtin', $pInfo->products_gtin); ?>
                    </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
        <?php
          for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        ?>
               <?php if ($i == 0) echo '<label>'.TEXT_PRODUCTS_DESCRIPTION.'</label>'; ?>
               <?php echo tep_draw_editor_textarea_field('products_description[' . $languages[$i]['id'] . ']', 'soft', '70', '15', (empty($pInfo->products_id) ? '' : tep_get_products_description($pInfo->products_id, $languages[$i]['id']))); ?>
        <?php
          }
        ?>
        </div>
      </div>
      <div class="row jumbotron">
        <div class="col-sm-6">
        <?php
            for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        ?>
         <?php if ($i == 0) echo '<label>'.TEXT_PRODUCTS_SEO_KEYWORDS.'</label>'; ?>
          <div class="form-group">
            <div class="input-group input-group-sm mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1"><?php echo $languages[$i]['code']; ?></span>
              </div>
              <?php echo tep_draw_input_field('products_seo_keywords[' . $languages[$i]['id'] . ']', tep_get_products_seo_keywords($pInfo->products_id, $languages[$i]['id']), 'placeholder="' . PLACEHOLDER_COMMA_SEPARATION . '" style="width: 300px;"'); ?>
            </div>          
          </div>
        <?php
            }
        ?>      
        </div>
        <div class="col-sm-6">
        <?php
                for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            ?>
            <?php if ($i == 0) echo '<label>'.TEXT_PRODUCTS_SEO_DESCRIPTION.'<label>'; ?>
            <span class="input-group-text" id="basic-addon1"><?php echo $languages[$i]['code']; ?></span>
            <?php echo tep_draw_textarea_field('products_seo_description[' . $languages[$i]['id'] . ']', 'soft', '70', '2', (empty($pInfo->products_id) ? '' : tep_get_products_seo_description($pInfo->products_id, $languages[$i]['id']))); ?>
            <?php
                }
            ?>    
        </div>
      </div>
      <div class="row jumbotron">
      <div class="col-sm-12" style="margin-bottom:20px;">
         <?php echo '<label>' . TEXT_PRODUCTS_MAIN_IMAGE . '</label> <small>(' . SMALL_IMAGE_WIDTH . ' x ' . SMALL_IMAGE_HEIGHT . 'px)</small><div class="input-group mb-3"><div class="custom-file"><input type="file" class="custom-file-input" name="products_image"><label class="custom-file-label">'.$pInfo->products_image.'</label></div></div>';?>
      </div>
      <div class="col-sm-12"> 
      <label><?php echo TEXT_PRODUCTS_IMAGE_DRAG; ?></label>
      <ul id="piList">
<?php
    $pi_counter = 0;
    foreach ($pInfo->products_larger_images as $pi) {
      $pi_counter++;
      echo '<li class="pi" id="piId' . $pi_counter . '" draggable="true"><label>' . TEXT_PRODUCTS_LARGE_IMAGE . ' ' .$pi_counter.'</label><div class="input-group mb-3"><div class="input-group-prepend">
      <button type="button" class="btn btn-danger btn-sm">&nbsp;<i class="fa fa-trash" aria-hidden="true"></i>&nbsp;</button>
    </div><div class="custom-file">
      <input type="file" class="custom-file-input" name="products_image_large_' . $pi['id'] .'">
      <label class="custom-file-label">'.$pi['image'].'</label></div></div>' . TEXT_PRODUCTS_LARGE_IMAGE_HTML_CONTENT . '' . tep_draw_textarea_field('products_image_htmlcontent_' . $pi['id'], 'soft', '70', '2', $pi['htmlcontent']) . '</li>';
    }
?>
          </ul>
      </div>
      </div>
      <div class="row" style="margin-top:20px;">
        <div class="col-sm-12">
          <button id="add-image" type="button" class="btn btn-sm btn-primary"><?php echo TEXT_PRODUCTS_ADD_LARGE_IMAGE; ?></button>
          <?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('categories.php', 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : ''))); ?>
        </div>
      </div>  
      <style type="text/css">
#piList { list-style-type: none; margin: 0; padding: 0; }
#piList li { margin: 0; padding: 0; }
</style>
<script>
</script>
      <?php echo tep_draw_hidden_field('products_date_added', (tep_not_null($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d'))); ?>
    </div><!-- end card-body-->															
        </div><!-- end card-->	
  </div><!-- end col-->
    </form>
<?php
  } elseif ($action == 'new_product_preview') {
    $product_query = tep_db_query("select p.products_id, pd.language_id, pd.products_name, pd.products_description, pd.products_url, p.products_quantity, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.manufacturers_id, pd.products_seo_title, p.products_gtin, pd.products_seo_description, pd.products_seo_keywords from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and p.products_id = '" . (int)$_GET['pID'] . "'");
    $product = tep_db_fetch_array($product_query);
    $pInfo = new objectInfo($product);
    $products_image_name = $pInfo->products_image;
    $languages = tep_get_languages();
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
      $pInfo->products_name = tep_get_products_name($pInfo->products_id, $languages[$i]['id']);
      $pInfo->products_description = tep_get_products_description($pInfo->products_id, $languages[$i]['id']);
      $pInfo->products_url = tep_get_products_url($pInfo->products_id, $languages[$i]['id']);
      $pInfo->products_seo_description = tep_get_products_seo_description($pInfo->products_id, $languages[$i]['id']);
      $pInfo->products_seo_keywords = tep_get_products_seo_keywords($pInfo->products_id, $languages[$i]['id']);
      $pInfo->products_seo_title = tep_get_products_seo_title($pInfo->products_id, $languages[$i]['id']);
?>
<div class="row">
  <div class="col-sm-12">
        <div class="card mb-3">
                  <div class="card-header">
                    <h6><?php echo tep_image(tep_catalog_href_link('includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . $pInfo->products_name; ?>
                    <span style="float:right"><?php echo $currencies->format($pInfo->products_price); ?></span></h6>
                  </div>
                  <div class="card-body">      
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="main"><?php echo tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $products_image_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="right" hspace="5" vspace="5"') . $pInfo->products_description; ?></td>
      </tr>
<?php
      if ($pInfo->products_url) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo sprintf(TEXT_PRODUCT_MORE_INFORMATION, $pInfo->products_url); ?></td>
      </tr>
<?php
      }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
      if ($pInfo->products_date_available > date('Y-m-d')) {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_PRODUCT_DATE_AVAILABLE, tep_date_long($pInfo->products_date_available)); ?></td>
      </tr>
<?php
      } else {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_PRODUCT_DATE_ADDED, tep_date_long($pInfo->products_date_added)); ?></td>
      </tr>
<?php
      }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
    }
    if (isset($_GET['origin'])) {
      $pos_params = strpos($_GET['origin'], '?', 0);
      if ($pos_params != false) {
        $back_url = substr($_GET['origin'], 0, $pos_params);
        $back_url_params = substr($_GET['origin'], $pos_params + 1);
      } else {
        $back_url = $_GET['origin'];
        $back_url_params = '';
      }
    } else {
      $back_url = 'categories.php';
      $back_url_params = 'cPath=' . $cPath . '&pID=' . $pInfo->products_id;
    }
?>
      <tr>
        <td align="right" class="smallText"><?php echo tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link($back_url, $back_url_params)); ?></td>
      </tr>
    </table>
    </div><!-- end card-body-->															
        </div><!-- end card-->	
  </div><!-- end col-->
<?php
  } else {
?>
      <div class="row" style="margin-bottom:10px;">
        <div class="col-md-4"> 
        <?php
    echo tep_draw_form('goto', 'categories.php', '', 'get');
    echo tep_draw_pull_down_menu('cPath', tep_get_category_tree(), $current_category_id, 'onchange="this.form.submit();"');
    echo tep_hide_session_id() . '</form>';
?>           
        </div>
        <div class="col-md-4">
        <?php
    echo tep_draw_form('search', 'categories.php', '', 'get');
    echo tep_draw_input_field('search', '', 'placeholder="Search"');
    echo tep_hide_session_id() . '</form>';
?>         
        </div>
        <div class="col-md-4" style=""> 
        <?php 
        if (isset($cPath_array) && (sizeof($cPath_array) > 0))  echo tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link('categories.php', $cPath_back . 'cID=' . $current_category_id), null, null, 'primary'). ' '; 
        if (!isset($_GET['search'])) echo tep_draw_button(IMAGE_NEW_CATEGORY, 'plus', tep_href_link('categories.php', 'cPath=' . $cPath . '&action=new_category'), null, null, 'secondary') . ' ' . tep_draw_button(IMAGE_NEW_PRODUCT, 'plus', tep_href_link('categories.php', 'cPath=' . $cPath . '&action=new_product')); 
        ?>
        </div>
      </div>
<div class="row">
  <div class="col-sm-8">
        <div class="card mb-3">
                  <div class="card-header">
                    <h6><i class="fa fa-barcode"></i> <?php echo HEADING_TITLE; ?>
                    <span style="float:right"></span></h6>
                  </div>
                  <div class="card-body">      
                  <div class="table-responsive">
                      <table class="table table-sm table-striped table-hover">
                      <thead>
                        <tr>
                          <th scope="col"><?php echo TABLE_HEADING_CATEGORIES_PRODUCTS; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_STATUS; ?></th>
                          <th scope="col"><span style="float:right"><?php echo TABLE_HEADING_ACTION; ?></span></th>
                        </tr>
                      </thead>
                      <tbody>   
<?php
    $categories_count = 0;
    $rows = 0;
    if (isset($_GET['search'])) {
      $search = tep_db_prepare_input($_GET['search']);
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, cd.categories_description, cd.categories_seo_description, cd.categories_seo_keywords, cd.categories_seo_title, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and cd.categories_name like '%" . tep_db_input($search) . "%' order by c.sort_order, cd.categories_name");
    } else {
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, cd.categories_description, cd.categories_seo_description, cd.categories_seo_keywords, cd.categories_seo_title, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by c.sort_order, cd.categories_name");
    }
    while ($categories = tep_db_fetch_array($categories_query)) {
      $categories_count++;
      $rows++;
// Get parent_id for subcategories if search
      if (isset($_GET['search'])) $cPath= $categories['parent_id'];
      if ((!isset($_GET['cID']) && !isset($_GET['pID']) || (isset($_GET['cID']) && ($_GET['cID'] == $categories['categories_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
        $category_childs = array('childs_count' => tep_childs_in_category_count($categories['categories_id']));
        $category_products = array('products_count' => tep_products_in_category_count($categories['categories_id']));
        $cInfo_array = array_merge($categories, $category_childs, $category_products);
        $cInfo = new objectInfo($cInfo_array);
      }
      if (isset($cInfo) && is_object($cInfo) && ($categories['categories_id'] == $cInfo->categories_id) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('categories.php', tep_get_path($categories['categories_id'])) . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('categories.php', 'cPath=' . $cPath . '&cID=' . $categories['categories_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link('categories.php', tep_get_path($categories['categories_id'])) . '">' . '<i class="fa fa-folder-o" aria-hidden="true"></i>' . '</a>&nbsp;' . $categories['categories_name'] . ''; ?></td>
                <td class="dataTableContent" align="center">&nbsp;</td>
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($categories['categories_id'] == $cInfo->categories_id) ) { echo '<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>'; } else { echo '<a href="' . tep_href_link('categories.php', 'cPath=' . $cPath . '&cID=' . $categories['categories_id']) . '">' . '<i class="fa fa-info-circle" aria-hidden="true"></i>' . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
    $products_count = 0;
    if (isset($_GET['search'])) {
      $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_model, p.products_gtin, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p2c.categories_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and((pd.products_name like '%" . tep_db_input($search) . "%') || (p.products_model like '%" . tep_db_input($search) . "%') ||  (p.products_gtin like '%" . tep_db_input($search) . "%')) order by pd.products_name");
    } else {
      $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by pd.products_name");
    }
    while ($products = tep_db_fetch_array($products_query)) {
      $products_count++;
      $rows++;
// Get categories_id for product if search
      if (isset($_GET['search'])) $cPath = $products['categories_id'];
      if ( (!isset($_GET['pID']) && !isset($_GET['cID']) || (isset($_GET['pID']) && ($_GET['pID'] == $products['products_id']))) && !isset($pInfo) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
// find out the rating average from customer reviews
        $reviews_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int)$products['products_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        $pInfo_array = array_merge($products, $reviews);
        $pInfo = new objectInfo($pInfo_array);
      }
      if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('categories.php', 'cPath=' . $cPath . '&pID=' . $products['products_id'] . '&action=new_product_preview&read=only') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('categories.php', 'cPath=' . $cPath . '&pID=' . $products['products_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link('categories.php', 'cPath=' . $cPath . '&pID=' . $products['products_id'] . '&action=new_product_preview&read=only') . '">' . '<i class="fa fa-search" aria-hidden="true"></i>' . '</a>&nbsp;' . $products['products_name']; ?></td>
                <td class="dataTableContent" align="center">
<?php
      if ($products['products_status'] == '1') {
        echo '<i class="fa fa-dot-circle-o" aria-hidden="true"></i>' . '&nbsp;&nbsp;<a href="' . tep_href_link('categories.php', 'action=setflag&flag=0&pID=' . $products['products_id'] . '&cPath=' . $cPath) . '">' . '<i class="fa fa-circle-o" aria-hidden="true"></i>' . '</a>';
      } else {
        echo '<a href="' . tep_href_link('categories.php', 'action=setflag&flag=1&pID=' . $products['products_id'] . '&cPath=' . $cPath) . '">' . '<i class="fa fa-circle-o" aria-hidden="true"></i>' . '</a>&nbsp;&nbsp;' . '<i class="fa fa-dot-circle-o" aria-hidden="true"></i>';
      }
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id)) { echo '<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>'; } else { echo '<a href="' . tep_href_link('categories.php', 'cPath=' . $cPath . '&pID=' . $products['products_id']) . '">' . '<i class="fa fa-info-circle" aria-hidden="true"></i>' . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
    $cPath_back = '';
    if (isset($cPath_array) && sizeof($cPath_array) > 0) {
      for ($i=0, $n=sizeof($cPath_array)-1; $i<$n; $i++) {
        if (empty($cPath_back)) {
          $cPath_back .= $cPath_array[$i];
        } else {
          $cPath_back .= '_' . $cPath_array[$i];
        }
      }
    }
    $cPath_back = (tep_not_null($cPath_back)) ? 'cPath=' . $cPath_back . '&' : '';
?>
             <!--  <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><?php echo TEXT_CATEGORIES . '&nbsp;' . $categories_count . '<br />' . TEXT_PRODUCTS . '&nbsp;' . $products_count; ?></td>
                  </tr>
                </table></td>
              </tr> -->
              </tbody>         
                    </table>
                  </div>  
            </div> 
            </div>  
            </div>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'new_category':
        $heading[] = array('text' => '' . TEXT_INFO_HEADING_NEW_CATEGORY . '');
        $contents = array('form' => tep_draw_form('newcategory', 'categories.php', 'action=insert_category&cPath=' . $cPath, 'post', 'enctype="multipart/form-data"'));
        $contents[] = array('text' => TEXT_NEW_CATEGORY_INTRO);
        $category_inputs_string = $category_description_string = $category_seo_description_string = $category_seo_keywords_string = $category_seo_title_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $category_inputs_string .= '<br />' . tep_image(tep_catalog_href_link('includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_name[' . $languages[$i]['id'] . ']');
          $category_description_string .= '<br />' . tep_image(tep_catalog_href_link('includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name'], '', '', 'style="vertical-align: top;"') . '&nbsp;' . tep_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', 'soft', '80', '10');
          $category_seo_description_string .= '<br />' . tep_image(tep_catalog_href_link('includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name'], '', '', 'style="vertical-align: top;"') . '&nbsp;' . tep_draw_textarea_field('categories_seo_description[' . $languages[$i]['id'] . ']', 'soft', '80', '10');
          $category_seo_keywords_string .= '<br />' . tep_image(tep_catalog_href_link('includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_seo_keywords[' . $languages[$i]['id'] . ']', NULL, 'style="width: 300px;" placeholder="' . PLACEHOLDER_COMMA_SEPARATION . '"');
          $category_seo_title_string .= '<br />' . tep_image(tep_catalog_href_link('includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_seo_title[' . $languages[$i]['id'] . ']');
        }
        $contents[] = array('text' => '<br />' . TEXT_CATEGORIES_NAME . $category_inputs_string);
        $contents[] = array('text' => '<br />' . TEXT_CATEGORIES_SEO_TITLE . $category_seo_title_string);
        $contents[] = array('text' => '<br />' . TEXT_CATEGORIES_DESCRIPTION . $category_description_string);
        $contents[] = array('text' => '<br />' . TEXT_CATEGORIES_SEO_DESCRIPTION . $category_seo_description_string);
        $contents[] = array('text' => '<br />' . TEXT_CATEGORIES_SEO_KEYWORDS . $category_seo_keywords_string);
        $contents[] = array('text' => '<br />' . TEXT_CATEGORIES_IMAGE . '<br />' . tep_draw_file_field('categories_image'));
        $contents[] = array('text' => '<br />' . TEXT_SORT_ORDER . '<br />' . tep_draw_input_field('sort_order', '', 'size="2"'));
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('categories.php', 'cPath=' . $cPath)));
        break;
      case 'edit_category':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_CATEGORY . '</strong>');
        $contents = array('form' => tep_draw_form('categories', 'categories.php', 'action=update_category&cPath=' . $cPath, 'post', 'enctype="multipart/form-data"') . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        $contents[] = array('text' => TEXT_EDIT_INTRO);
        $category_inputs_string = $category_description_string = $category_seo_description_string = $category_seo_keywords_string = $category_seo_title_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $category_inputs_string .= '<br />' . tep_image(tep_catalog_href_link('includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', tep_get_category_name($cInfo->categories_id, $languages[$i]['id']));
          $category_seo_title_string .= '<br />' . tep_image(tep_catalog_href_link('includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_seo_title[' . $languages[$i]['id'] . ']', tep_get_category_seo_title($cInfo->categories_id, $languages[$i]['id']));
          $category_description_string .= '<br />' . tep_image(tep_catalog_href_link('includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name'], '', '', 'style="vertical-align: top;"') . '&nbsp;' . tep_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', 'soft', '80', '10', tep_get_category_description($cInfo->categories_id, $languages[$i]['id']));
          $category_seo_description_string .= '<br />' . tep_image(tep_catalog_href_link('includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name'], '', '', 'style="vertical-align: top;"') . '&nbsp;' . tep_draw_textarea_field('categories_seo_description[' . $languages[$i]['id'] . ']', 'soft', '80', '10', tep_get_category_seo_description($cInfo->categories_id, $languages[$i]['id']));
          $category_seo_keywords_string .= '<br />' . tep_image(tep_catalog_href_link('includes/languages/' . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_seo_keywords[' . $languages[$i]['id'] . ']', tep_get_category_seo_keywords($cInfo->categories_id, $languages[$i]['id']), 'style="width: 300px;" placeholder="' . PLACEHOLDER_COMMA_SEPARATION . '"');
        }
        $contents[] = array('text' => '<br />' . TEXT_EDIT_CATEGORIES_NAME . $category_inputs_string);
        $contents[] = array('text' => '<br />' . TEXT_EDIT_CATEGORIES_SEO_TITLE . $category_seo_title_string);
        $contents[] = array('text' => '<br />' . TEXT_EDIT_CATEGORIES_DESCRIPTION . $category_description_string);
        $contents[] = array('text' => '<br />' . TEXT_EDIT_CATEGORIES_SEO_DESCRIPTION . $category_seo_description_string);
        $contents[] = array('text' => '<br />' . TEXT_EDIT_CATEGORIES_SEO_KEYWORDS . $category_seo_keywords_string);
        $contents[] = array('text' => '<br />' . tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $cInfo->categories_image, $cInfo->categories_name) . '<br />' . DIR_WS_CATALOG_IMAGES . '<br /><strong>' . $cInfo->categories_image . '</strong>');
        $contents[] = array('text' => '<br />' . TEXT_EDIT_CATEGORIES_IMAGE . '<br />' . tep_draw_file_field('categories_image'));
        $contents[] = array('text' => '<br />' . TEXT_EDIT_SORT_ORDER . '<br />' . tep_draw_input_field('sort_order', $cInfo->sort_order, 'size="2"'));
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('categories.php', 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id)));
        break;
      case 'delete_category':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_CATEGORY . '</strong>');
        $contents = array('form' => tep_draw_form('categories', 'categories.php', 'action=delete_category_confirm&cPath=' . $cPath) . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        $contents[] = array('text' => TEXT_DELETE_CATEGORY_INTRO);
        $contents[] = array('text' => '<br /><strong>' . $cInfo->categories_name . '</strong>');
        if ($cInfo->childs_count > 0) $contents[] = array('text' => '<br />' . sprintf(TEXT_DELETE_WARNING_CHILDS, $cInfo->childs_count));
        if ($cInfo->products_count > 0) $contents[] = array('text' => '<br />' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $cInfo->products_count));
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('categories.php', 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id)));
        break;
      case 'move_category':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_MOVE_CATEGORY . '</strong>');
        $contents = array('form' => tep_draw_form('categories', 'categories.php', 'action=move_category_confirm&cPath=' . $cPath) . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        $contents[] = array('text' => sprintf(TEXT_MOVE_CATEGORIES_INTRO, $cInfo->categories_name));
        $contents[] = array('text' => '<br />' . sprintf(TEXT_MOVE, $cInfo->categories_name) . '<br />' . tep_draw_pull_down_menu('move_to_category_id', tep_get_category_tree(), $current_category_id));
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_MOVE, 'arrow-4', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('categories.php', 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id)));
        break;
      case 'delete_product':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_PRODUCT . '</strong>');
        $contents = array('form' => tep_draw_form('products', 'categories.php', 'action=delete_product_confirm&cPath=' . $cPath) . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => TEXT_DELETE_PRODUCT_INTRO);
        $contents[] = array('text' => '<br /><strong>' . $pInfo->products_name . '</strong>');
        $product_categories_string = '';
        $product_categories = tep_generate_category_path($pInfo->products_id, 'product');
        for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
          $category_path = '';
          for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
            $category_path .= $product_categories[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
          }
          $category_path = substr($category_path, 0, -16);
          $product_categories_string .= tep_draw_bs_checkbox_field('product_categories[]', $product_categories[$i][sizeof($product_categories[$i])-1]['id'], true, '', $category_path, '');
        }
        $product_categories_string = substr($product_categories_string, 0, -4);
        $contents[] = array('text' => '<br />' . $product_categories_string);
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('categories.php', 'cPath=' . $cPath . '&pID=' . $pInfo->products_id)));
        break;
      case 'move_product':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_MOVE_PRODUCT . '</strong>');
        $contents = array('form' => tep_draw_form('products', 'categories.php', 'action=move_product_confirm&cPath=' . $cPath) . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => sprintf(TEXT_MOVE_PRODUCTS_INTRO, $pInfo->products_name));
        $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENT_CATEGORIES . '<br /><strong>' . tep_output_generated_category_path($pInfo->products_id, 'product') . '</strong>');
        $contents[] = array('text' => '<br />' . sprintf(TEXT_MOVE, $pInfo->products_name) . '<br />' . tep_draw_pull_down_menu('move_to_category_id', tep_get_category_tree(), $current_category_id));
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_MOVE, 'arrow-4', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('categories.php', 'cPath=' . $cPath . '&pID=' . $pInfo->products_id)));
        break;
      case 'copy_to':
        $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_COPY_TO . '</strong>');
        $contents = array('form' => tep_draw_form('copy_to', 'categories.php', 'action=copy_to_confirm&cPath=' . $cPath) . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => TEXT_INFO_COPY_TO_INTRO);
        $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENT_CATEGORIES . '<br /><strong>' . tep_output_generated_category_path($pInfo->products_id, 'product') . '</strong>');
        $contents[] = array('text' => '<br />' . TEXT_CATEGORIES . '<br />' . tep_draw_pull_down_menu('categories_id', tep_get_category_tree(), $current_category_id));
        $contents[] = array('text' => '<br />' . TEXT_HOW_TO_COPY . '<br />' . tep_draw_bs_radio_field('copy_as', 'link', true, null, TEXT_COPY_AS_LINK, '')  . tep_draw_bs_radio_field('copy_as', 'duplicate', null, null, TEXT_COPY_AS_DUPLICATE, ''));
        $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_COPY, 'copy', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('categories.php', 'cPath=' . $cPath . '&pID=' . $pInfo->products_id)));
        break;
      default:
        if ($rows > 0) {
          if (isset($cInfo) && is_object($cInfo)) { // category info box contents
            $category_path_string = '';
            $category_path = tep_generate_category_path($cInfo->categories_id);
            for ($i=(sizeof($category_path[0])-1); $i>0; $i--) {
              $category_path_string .= $category_path[0][$i]['id'] . '_';
            }
            $category_path_string = substr($category_path_string, 0, -1);
            $heading[] = array('text' => '<strong>' . $cInfo->categories_name . '</strong>');
            $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('categories.php', 'cPath=' . $category_path_string . '&cID=' . $cInfo->categories_id . '&action=edit_category'), null, null, 'primary') . ' ' .  tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('categories.php', 'cPath=' . $category_path_string . '&cID=' . $cInfo->categories_id . '&action=delete_category'), null, null, 'danger') . ' ' . tep_draw_button(IMAGE_MOVE, 'arrow-4', tep_href_link('categories.php', 'cPath=' . $category_path_string . '&cID=' . $cInfo->categories_id . '&action=move_category')));
            $contents[] = array('text' => '<br />' . TEXT_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added));
            if (tep_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified));
            $contents[] = array('text' => '<br />' . tep_info_image($cInfo->categories_image, $cInfo->categories_name, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT) . '<br />' . $cInfo->categories_image);
            $contents[] = array('text' => '<br />' . TEXT_SUBCATEGORIES . ' ' . $cInfo->childs_count . '<br />' . TEXT_PRODUCTS . ' ' . $cInfo->products_count);
          } elseif (isset($pInfo) && is_object($pInfo)) { // product info box contents
            $heading[] = array('text' => '<strong>' . tep_get_products_name($pInfo->products_id, $languages_id) . '</strong>');
            $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('categories.php', 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=new_product'), null, null, 'primary') . ' ' . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('categories.php', 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=delete_product'), null, null, 'danger') . ' ' . tep_draw_button(IMAGE_MOVE, 'arrow-4', tep_href_link('categories.php', 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=move_product'), null, null, 'secondary') . ' ' . tep_draw_button(IMAGE_COPY_TO, 'copy', tep_href_link('categories.php', 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=copy_to')));
            $contents[] = array('text' => '<br />' . TEXT_DATE_ADDED . ' ' . tep_date_short($pInfo->products_date_added));
            if (tep_not_null($pInfo->products_last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($pInfo->products_last_modified));
            if (date('Y-m-d') < $pInfo->products_date_available) $contents[] = array('text' => TEXT_DATE_AVAILABLE . ' ' . tep_date_short($pInfo->products_date_available));
            $contents[] = array('text' => '<br />' . tep_info_image($pInfo->products_image, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '<br />' . $pInfo->products_image);
            $contents[] = array('text' => '<br />' . TEXT_PRODUCTS_PRICE_INFO . ' ' . $currencies->format($pInfo->products_price) . '<br />' . TEXT_PRODUCTS_QUANTITY_INFO . ' ' . $pInfo->products_quantity);
            $contents[] = array('text' => '<br />' . TEXT_PRODUCTS_AVERAGE_RATING . ' ' . number_format($pInfo->average_rating, 2) . '%');
          }
        } else { // create category/product info
          $heading[] = array('text' => '<strong>' . EMPTY_CATEGORY . '</strong>');
          $contents[] = array('text' => TEXT_NO_CHILD_CATEGORIES_OR_PRODUCTS);
        }
        break;
    }
    if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
      ?>
      <div class="col-sm-4">
      <div class="card mb-3">
          <div class="card-header">
            <h6><?php echo $heading[0]['text']; ?></h6>
          </div>
          <div class="card-body card-bg">  
              <?php
                $box = new box;
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
  }
?>
<!-- Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Are you sure?</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to remove this image?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button rel="" id="confirm-image-delete" type="button" class="btn btn-primary">Confirm</button>
      </div>
    </div>
  </div>
</div>
<?php
  require('includes/template_bottom.php');
?>

<script src="assets/plugins/datetimepicker/js/moment.min.js"></script>
<script src="assets/plugins/datetimepicker/js/daterangepicker.js"></script>
<script type="text/javascript">
$('#products_date_available').datepicker({
  dateFormat: 'yy-mm-dd'
});
</script>
<script>
</script>
<script>
  $(document).ready(function () {
    'use strict';
    $('.editor').trumbowyg({
      svgPath: false,
      hideButtonTexts: true
    });
    var c = 0;
    $('input[name^="products_url"]').each(function() {
        var a = $(this).parent().find('span').text();
        var b = '.trumbowyg-button-pane:eq(' + c + ')';
        $(b).prepend('<a class="btn">'+a+'</a>');
        c++;
    });
    $('input[name="products_date_available"]').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        drops: 'up',
        locale: {
            cancelLabel: 'Clear',
            format: 'YYYY-MM-DD'
        }
    });
    $('input[name="products_date_available"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });
    $('input[name="products_date_available"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
    $('.daterangepicker').css("width","360px");
    var a = <?php echo $pi_counter; ?>;
    $("#add-image").click(function(e){
          a++;
          $('#piList').append('<li class="pi" id="piId'+a+'" draggable="true"><label>Large Image '+a+'</label><div class="input-group mb-3"><div class="input-group-prepend"><button type="button" class="btn btn-danger btn-sm">&nbsp;<i class="fa fa-trash" aria-hidden="true"></i>&nbsp;</button></div><div class="custom-file"><input type="file" class="custom-file-input" name="products_image_large_new_'+a+'"><label class="custom-file-label"></label></div></div>HTML Content (for popup)<textarea class="form-control" name="products_image_htmlcontent_new_'+a+'" wrap="soft" cols="70" rows="3"></textarea></li>');
          var cols = document.querySelectorAll('#piList .pi');
          [].forEach.call(cols, addDnDHandlers);
          $('.custom-file-input').on('change',function(){
                var fileName = $(this).val().slice(12);
                $(this).next('.custom-file-label').html(fileName);
          });   
          $("#piList button").click(function(e){
              var id = $(this).parent().parent().parent().attr("id");
              $('#confirm-image-delete').attr("rel",id);
              $('#confirmModal').modal('show');
          });
          $("#confirm-image-delete").click(function(e){
              var id = '#'+$(this).attr("rel");
              $('#confirmModal').modal('hide');
              $(id).remove();
          });
    });
    $("#piList button").click(function(e){
        var id = $(this).parent().parent().parent().attr("id");
        $('#confirm-image-delete').attr("rel",id);
        $('#confirmModal').modal('show');
    });
    $("#confirm-image-delete").click(function(e){
        var id = '#'+$(this).attr("rel");
        $('#confirmModal').modal('hide');
        $(id).remove();
    });
    $('.custom-file-input').on('change',function(){
        var fileName = $(this).val().slice(12);
        $(this).next('.custom-file-label').html(fileName);
    }) 
var dragSrcEl = null;
function handleDragStart(e) {
  dragSrcEl = this;
  e.dataTransfer.effectAllowed = 'move';
  e.dataTransfer.setData('text/html', this.outerHTML);
  this.classList.add('dragElem');
}
function handleDragOver(e) {
  if (e.preventDefault) {
    e.preventDefault(); 
  }
  this.classList.add('over');
  e.dataTransfer.dropEffect = 'move';
  return false;
}
function handleDragEnter(e) {
}
function handleDragLeave(e) {
  this.classList.remove('over'); 
}
function handleDrop(e) {
  if (e.stopPropagation) {
    e.stopPropagation(); 
  }
  if (dragSrcEl != this) {
    this.parentNode.removeChild(dragSrcEl);
    var dropHTML = e.dataTransfer.getData('text/html');
    this.insertAdjacentHTML('beforebegin',dropHTML);
    var dropElem = this.previousSibling;
    addDnDHandlers(dropElem);
  }
  this.classList.remove('over');
  $('.custom-file-input').on('change',function(){
      var fileName = $(this).val().slice(12);
      $(this).next('.custom-file-label').html(fileName);
  });   
  $("#piList button").click(function(e){
      var id = $(this).parent().parent().parent().attr("id");
      $('#confirm-image-delete').attr("rel",id);
      $('#confirmModal').modal('show');
  });
  $("#confirm-image-delete").click(function(e){
      var id = '#'+$(this).attr("rel");
      $('#confirmModal').modal('hide');
      $(id).remove();
  });
  return false;
}
function handleDragEnd(e) {
  this.classList.remove('over');
}
function addDnDHandlers(elem) {
  elem.addEventListener('dragstart', handleDragStart, false);
  elem.addEventListener('dragenter', handleDragEnter, false)
  elem.addEventListener('dragover', handleDragOver, false);
  elem.addEventListener('dragleave', handleDragLeave, false);
  elem.addEventListener('drop', handleDrop, false);
  elem.addEventListener('dragend', handleDragEnd, false);
}
var cols = document.querySelectorAll('#piList .pi');
[].forEach.call(cols, addDnDHandlers);
  });
  </script>
<?php
  require('includes/application_bottom.php');
?>