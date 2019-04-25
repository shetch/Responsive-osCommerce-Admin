<?php
  if ($action == 'update_attribute') {
    $form_action = 'update_product_attribute';
  } else {
    $form_action = 'add_product_attributes';
  }
?>
<?php
  $attributes = "select pa.* from " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pa.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' order by pd.products_name";
  $attributes_split = new splitPageResults($attribute_page, MAX_ROW_LISTS_OPTIONS, $attributes, $attributes_query_numrows);
?>        
<div class="row" style="margin-top:20px;">
  <div class="col-sm-12">
  <form name="attributes" action="<?php echo tep_href_link('products_attributes.php', 'action=' . $form_action . '&' . $page_info); ?>" method="post">
  <div class="table-responsive">
  <table class="table table-striped table-sm">
  <thead>
    <tr>
      <th scope="col"><?php echo TABLE_HEADING_ID; ?></th>
      <th scope="col"><?php echo TABLE_HEADING_PRODUCT; ?></th>
      <th scope="col"><?php echo TABLE_HEADING_OPT_NAME; ?></th>
      <th scope="col"><?php echo TABLE_HEADING_OPT_VALUE; ?></th>
      <th scope="col"><?php echo TABLE_HEADING_OPT_PRICE; ?></th>
      <th scope="col"><?php echo TABLE_HEADING_OPT_PRICE_PREFIX; ?></th>
      <th scope="col" class="text-right" style="min-width:120px"><?php echo TABLE_HEADING_ACTION; ?></th>
    </tr>
  </thead>
  <tbody>
  <?php
  $next_id = 1;
  $attributes = tep_db_query($attributes);
  while ($attributes_values = tep_db_fetch_array($attributes)) {
    $products_name_only = tep_get_products_name($attributes_values['products_id']);
    $options_name = tep_options_name($attributes_values['options_id']);
    $values_name = tep_values_name($attributes_values['options_values_id']);
    $rows++;
?>
<?php
    if (($action == 'update_attribute') && ($_GET['attribute_id'] == $attributes_values['products_attributes_id'])) {
?>
      <tr>     
            <td><?php echo $attributes_values['products_attributes_id']; ?><input class="form-control form-control-sm" type="hidden" name="attribute_id" value="<?php echo $attributes_values['products_attributes_id']; ?>"></td>
            <td><select class="form-control form-control-sm" name="products_id">
<?php
      $products = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
      while($products_values = tep_db_fetch_array($products)) {
        if ($attributes_values['products_id'] == $products_values['products_id']) {
          echo "\n" . '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '" SELECTED>' . $products_values['products_name'] . '</option>';
        } else {
          echo "\n" . '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $products_values['products_name'] . '</option>';
        }
      } 
?>
            </select>
          </td>
            <td>
              <select class="form-control form-control-sm" name="options_id">
<?php
      $options = tep_db_query("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_name");
      while($options_values = tep_db_fetch_array($options)) {
        if ($attributes_values['options_id'] == $options_values['products_options_id']) {
          echo "\n" . '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '" SELECTED>' . $options_values['products_options_name'] . '</option>';
        } else {
          echo "\n" . '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '">' . $options_values['products_options_name'] . '</option>';
        }
      } 
?>
            </select>
          </td>
            <td><select class="form-control form-control-sm" name="values_id">
<?php
      $values = tep_db_query("select * from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id ='" . $languages_id . "' order by products_options_values_name");
      while($values_values = tep_db_fetch_array($values)) {
        if ($attributes_values['options_values_id'] == $values_values['products_options_values_id']) {
          echo "\n" . '<option name="' . $values_values['products_options_values_name'] . '" value="' . $values_values['products_options_values_id'] . '" SELECTED>' . $values_values['products_options_values_name'] . '</option>';
        } else {
          echo "\n" . '<option name="' . $values_values['products_options_values_name'] . '" value="' . $values_values['products_options_values_id'] . '">' . $values_values['products_options_values_name'] . '</option>';
        }
      } 
?>        
            </select></td>
      <td><input class="form-control form-control-sm" type="text" name="value_price" value="<?php echo $attributes_values['options_values_price']; ?>" size="6"></td>
      <td><input class="form-control form-control-sm" type="text" name="price_prefix" value="<?php echo $attributes_values['price_prefix']; ?>" size="2"></td>
      <td><?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('products_attributes.php', $page_info)); ?></td>
      </tr>
<?php
      if (DOWNLOAD_ENABLED == 'true') {
        $download_query_raw ="select products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount 
                              from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " 
                              where products_attributes_id='" . $attributes_values['products_attributes_id'] . "'";
        $download_query = tep_db_query($download_query_raw);
        if (tep_db_num_rows($download_query) > 0) {
          $download = tep_db_fetch_array($download_query);
          $products_attributes_filename = $download['products_attributes_filename'];
          $products_attributes_maxdays  = $download['products_attributes_maxdays'];
          $products_attributes_maxcount = $download['products_attributes_maxcount'];
        }
?>
          <tr>
            <td colspan="7">
              <table><tr>
                  <td><?php echo TABLE_HEADING_DOWNLOAD; ?></td>
                  <td><?php echo TABLE_TEXT_FILENAME; ?></td>
                  <td><?php echo tep_draw_sm_input_field('products_attributes_filename', $products_attributes_filename, 'size="15"'); ?></td>
                  <td><?php echo TABLE_TEXT_MAX_DAYS; ?></td>
                  <td><?php echo tep_draw_sm_input_field('products_attributes_maxdays', $products_attributes_maxdays, 'size="5"'); ?></td>
                  <td><?php echo TABLE_TEXT_MAX_COUNT; ?></td>
                  <td><?php echo tep_draw_sm_input_field('products_attributes_maxcount', $products_attributes_maxcount, 'size="5"'); ?></td>
                  </tr></table>
              </td>
          </tr> 
<?php
      }
?>
<?php
    } elseif (($action == 'delete_product_attribute') && ($_GET['attribute_id'] == $attributes_values['products_attributes_id'])) {
?>
<tr>
            <td><strong><?php echo $attributes_values["products_attributes_id"]; ?></strong></td>
            <td><strong><?php echo $products_name_only; ?></strong></td>
            <td><strong><?php echo $options_name; ?></strong></td>
            <td><strong><?php echo $values_name; ?></strong></td>
            <td><strong><?php echo $attributes_values["options_values_price"]; ?></strong></td>
            <td><strong><?php echo $attributes_values["price_prefix"]; ?></strong></td>
            <td><?php echo tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('products_attributes.php', 'action=delete_attribute&attribute_id=' . $_GET['attribute_id'] . '&' . $page_info), 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('products_attributes.php', $page_info)); ?></td>
</tr>
<?php
    } else {
?>
          <tr>       
            <td><?php echo $attributes_values["products_attributes_id"]; ?></td>
            <td><?php echo $products_name_only; ?></td>
            <td><?php echo $options_name; ?></td>
            <td><?php echo $values_name; ?></td>
            <td><?php echo $attributes_values["options_values_price"]; ?></td>
            <td><?php echo $attributes_values["price_prefix"]; ?></td>
            <td><?php echo tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('products_attributes.php', 'action=update_attribute&attribute_id=' . $attributes_values['products_attributes_id'] . '&' . $page_info), null, null, 'secondary') . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('products_attributes.php', 'action=delete_product_attribute&attribute_id=' . $attributes_values['products_attributes_id'] . '&' . $page_info)); ?></td>
            </tr>
<?php
    }
    $max_attributes_id_query = tep_db_query("select max(products_attributes_id) + 1 as next_id from " . TABLE_PRODUCTS_ATTRIBUTES);
    $max_attributes_id_values = tep_db_fetch_array($max_attributes_id_query);
    $next_id = $max_attributes_id_values['next_id'];
?>
<?php
  }
  if ($action != 'update_attribute') {
?>
          <tr>
            <td><?php echo $next_id; ?></td>
      	    <td><select class="form-control form-control-sm" name="products_id">
<?php
    $products = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
    while ($products_values = tep_db_fetch_array($products)) {
      echo '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $products_values['products_name'] . '</option>';
    } 
?>
            </select></td>
            <td><select class="form-control form-control-sm" name="options_id">
<?php
    $options = tep_db_query("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_name");
    while ($options_values = tep_db_fetch_array($options)) {
      echo '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '">' . $options_values['products_options_name'] . '</option>';
    } 
?>
            </select></td>
            <td><select class="form-control form-control-sm" name="values_id">
<?php
    $values = tep_db_query("select * from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . $languages_id . "' order by products_options_values_name");
    while ($values_values = tep_db_fetch_array($values)) {
      echo '<option name="' . $values_values['products_options_values_name'] . '" value="' . $values_values['products_options_values_id'] . '">' . $values_values['products_options_values_name'] . '</option>';
    } 
?>
            </select></td>
            <td><input class="form-control form-control-sm" type="text" name="value_price" size="6"></td>
            <td><input class="form-control form-control-sm" type="text" name="price_prefix" size="2" value="+"></td>
            <td align="right"><?php echo tep_draw_button(IMAGE_INSERT, 'plus'); ?></td>
          </tr>
<?php
      if (DOWNLOAD_ENABLED == 'true') {
        $products_attributes_maxdays  = DOWNLOAD_MAX_DAYS;
        $products_attributes_maxcount = DOWNLOAD_MAX_COUNT;
?>
          <tr>
            <td colspan="7">
              <table>
                <tr>
                  <td><?php echo TABLE_HEADING_DOWNLOAD; ?></td>
                  <td><?php echo TABLE_TEXT_FILENAME; ?></td>
                  <td><?php echo tep_draw_sm_input_field('products_attributes_filename', $products_attributes_filename, 'size="15"'); ?></td>
                  <td><?php echo TABLE_TEXT_MAX_DAYS; ?></td>
                  <td><?php echo tep_draw_sm_input_field('products_attributes_maxdays', $products_attributes_maxdays, 'size="5"'); ?></td>
                  <td><?php echo TABLE_TEXT_MAX_COUNT; ?></td>
                  <td><?php echo tep_draw_sm_input_field('products_attributes_maxcount', $products_attributes_maxcount, 'size="5"'); ?></td>
                </tr>
      </table> </td>
          </tr> 
<?php
      } // end of DOWNLOAD_ENABLED section
?>
<?php
  }
?>
       </tbody>
        </table>
        </form>
      </div> 
</div>        
</div>  
<div class="text-right">
<?php
echo $attributes_split->display_links($attributes_query_numrows, MAX_ROW_LISTS_OPTIONS, MAX_DISPLAY_PAGE_LINKS, $attribute_page, 'option_page=' . $option_page . '&value_page=' . $value_page, 'attribute_page');
?>
</div>