<!-- options //-->
<div class="row" style="margin-top:20px;">
  <div class="col-sm-12">
  <div class="table-responsive">
  <table class="table table-striped table-sm">
<?php
  if ($action == 'delete_product_option') { // delete product option
    $options = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$_GET['option_id'] . "' and language_id = '" . (int)$languages_id . "'");
    $options_values = tep_db_fetch_array($options);
?>
<?php
    $products = tep_db_query("select p.products_id, pd.products_name, pov.products_options_values_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pov.language_id = '" . (int)$languages_id . "' and pd.language_id = '" . (int)$languages_id . "' and pa.products_id = p.products_id and pa.options_id='" . (int)$_GET['option_id'] . "' and pov.products_options_values_id = pa.options_values_id order by pd.products_name");
    if (tep_db_num_rows($products)) {
?>
   <thead>
    <tr>
      <th scope="col"><?php echo TABLE_HEADING_ID; ?></th>
      <th scope="col"><?php echo TABLE_HEADING_PRODUCT; ?></th>
      <th scope="col"><?php echo TABLE_HEADING_OPT_VALUE; ?></th>
    </tr>
  </thead>
  <tbody>   
<?php
      $rows = 0;
      while ($products_values = tep_db_fetch_array($products)) {
        $rows++;
?>
                  <tr>
                    <td><?php echo $products_values['products_id']; ?></td>
                    <td><?php echo $products_values['products_name']; ?></td>
                    <td><?php echo $products_values['products_options_values_name']; ?></td>
                  </tr>
<?php
      }
?>
                  <tr>
                    <td colspan="3"><br /><?php echo TEXT_WARNING_OF_DELETE; ?></td>
                  </tr>
                  <tr>
                    <td colspan="3"><br /><?php echo tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link('products_attributes.php', $page_info)); ?></td>
                  </tr>
<?php
    } else {
?>
                  <tr>
                    <td colspan="3"><br /><?php echo TEXT_OK_TO_DELETE; ?></td>
                  </tr>
                  <tr>
                    <td colspan="3"><br /><?php echo tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('products_attributes.php', 'action=delete_option&option_id=' . $_GET['option_id'] . '&' . $page_info), 'primary', null, 'secondary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('products_attributes.php', $page_info. '&p=2')); ?></td>
                  </tr>
<?php
    }
?>
<?php
  } else {
?>
<?php
    $options = "select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$languages_id . "' order by products_options_id";
    $options_split = new splitPageResults($option_page, MAX_ROW_LISTS_OPTIONS, $options, $options_query_numrows);
    $options_link = $options_split->display_links($options_query_numrows, MAX_ROW_LISTS_OPTIONS, MAX_DISPLAY_PAGE_LINKS, $option_page, 'p=2&'.'value_page=' . $value_page . '&attribute_page=' . $attribute_page, 'option_page');
?>
         <thead>
          <tr>
            <th scope="col"><?php echo TABLE_HEADING_ID; ?></th>
            <th scope="col"><?php echo TABLE_HEADING_OPT_NAME; ?></th>
            <th class="text-right" scope="col"><?php echo TABLE_HEADING_ACTION; ?></th>
          </tr>
        </thead>
        <tbody>            
<?php
    $next_id = 1;
    $rows = 0;
    $options = tep_db_query($options);
    while ($options_values = tep_db_fetch_array($options)) {
      $rows++;
?>
              <tr>
<?php
      if (($action == 'update_option') && ($_GET['option_id'] == $options_values['products_options_id'])) {
        echo '<form name="option" action="' . tep_href_link('products_attributes.php', 'action=update_option_name&' . $page_info. '&p=2') . '" method="post">';
        $inputs = '';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          $option_name = tep_db_query("select products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . $options_values['products_options_id'] . "' and language_id = '" . $languages[$i]['id'] . "'");
          $option_name = tep_db_fetch_array($option_name);
          $inputs .= $languages[$i]['code'] . ':<input type="text" name="option_name[' . $languages[$i]['id'] . ']" size="20" value="' . $option_name['products_options_name'] . '"><br />';
        }
?>
                <td><?php echo $options_values['products_options_id']; ?><input type="hidden" name="option_id" value="<?php echo $options_values['products_options_id']; ?>"></td>
                <td><?php echo $inputs; ?></td>
                <td align="right"><?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('products_attributes.php', $page_info. '&p=2')); ?></td>
<?php
        echo '</form>' . "\n";
      } else {
?>
                <td><?php echo $options_values["products_options_id"]; ?></td>
                <td><?php echo $options_values["products_options_name"]; ?></td>
                <td align="right"><?php echo tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('products_attributes.php', 'action=update_option&option_id=' . $options_values['products_options_id'] . '&' . $page_info. '&p=2'), null, null, 'secondary') . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('products_attributes.php', 'action=delete_product_option&option_id=' . $options_values['products_options_id'] . '&' . $page_info. '&p=2')); ?></td>
<?php
      }
?>
              </tr>
<?php
      $max_options_id_query = tep_db_query("select max(products_options_id) + 1 as next_id from " . TABLE_PRODUCTS_OPTIONS);
      $max_options_id_values = tep_db_fetch_array($max_options_id_query);
      $next_id = $max_options_id_values['next_id'];
    }
?>
<?php
    if ($action != 'update_option') {
?>
              <tr>
<?php
      echo '<form name="options" action="' . tep_href_link('products_attributes.php', 'action=add_product_options&' . $page_info. '&p=2') . '" method="post"><input type="hidden" name="products_options_id" value="' . $next_id . '">';
      $inputs = '';
      for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
        $inputs .= $languages[$i]['code'] . ':<input type="text" name="option_name[' . $languages[$i]['id'] . ']" size="20"><br />';
      }
?>
                <td><?php echo $next_id; ?></td>
                <td><?php echo $inputs; ?></td>
                <td align="right"><?php echo tep_draw_button(IMAGE_INSERT, 'plus'); ?></td>
<?php
      echo '</form>';
?>
              </tr>
<?php
    }
  }
?>
            </tbody>
  </table>
</div>
<!-- option value eof //-->
<div class="text-right">
<?php
echo $options_link;
?>
</div>
</div>
</div>