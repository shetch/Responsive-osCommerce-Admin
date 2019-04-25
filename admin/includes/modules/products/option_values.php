<!-- value //-->
<div class="row" style="margin-top:20px;">
  <div class="col-sm-12">
  <div class="table-responsive">
  <table class="table table-striped table-sm">
<?php
  if ($action == 'delete_option_value') { // delete product option value
    $values = tep_db_query("select products_options_values_id, products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$_GET['value_id'] . "' and language_id = '" . (int)$languages_id . "'");
    $values_values = tep_db_fetch_array($values);
?>
<?php
    $products = tep_db_query("select p.products_id, pd.products_name, po.products_options_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS . " po, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and po.language_id = '" . (int)$languages_id . "' and pa.products_id = p.products_id and pa.options_values_id='" . (int)$_GET['value_id'] . "' and po.products_options_id = pa.options_id order by pd.products_name");
    if (tep_db_num_rows($products)) {
?>
   <thead>
    <tr>
      <th scope="col"><?php echo TABLE_HEADING_ID; ?></th>
      <th scope="col"><?php echo TABLE_HEADING_PRODUCT; ?></th>
      <th scope="col"><?php echo TABLE_HEADING_OPT_NAME; ?></th>
    </tr>
  </thead>
  <tbody>                
<?php
      while ($products_values = tep_db_fetch_array($products)) {
        $rows++;
?>
        <tr>
          <td><?php echo $products_values['products_id']; ?></td>
          <td><?php echo $products_values['products_name']; ?></td>
          <td><?php echo $products_values['products_options_name']; ?></td>
        </tr>
<?php
      }
?>
        <tr>
          <td colspan="3"><?php echo TEXT_WARNING_OF_DELETE; ?></td>
        </tr>
        <tr>
          <td colspan="3"><?php echo tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link('products_attributes.php', $page_info. '&p=3')); ?></td>
        </tr>
<?php
    } else {
?>
        <tr>
          <td colspan="3"><?php echo TEXT_OK_TO_DELETE; ?></td>
        </tr>
        <tr>
          <td colspan="3"><?php echo tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('products_attributes.php', 'action=delete_value&value_id=' . $_GET['value_id'] . '&' . $page_info), 'primary', null, 'secondary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('products_attributes.php', $page_info. '&p=3')); ?></td>
        </tr>
<?php
    }
?>
<?php
  } else {
?>
<?php
    $values = "select pov.products_options_values_id, pov.products_options_values_name, pov2po.products_options_id from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov left join " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po on pov.products_options_values_id = pov2po.products_options_values_id where pov.language_id = '" . (int)$languages_id . "' order by pov.products_options_values_id";
    $values_split = new splitPageResults($value_page, MAX_ROW_LISTS_OPTIONS, $values, $values_query_numrows);
    $values_links = $values_split->display_links($values_query_numrows, MAX_ROW_LISTS_OPTIONS, MAX_DISPLAY_PAGE_LINKS, $value_page, 'p=3&'.'option_page=' . $option_page . '&attribute_page=' . $attribute_page, 'value_page');
?>
      <thead>
      <tr>
        <th scope="col"><?php echo TABLE_HEADING_ID; ?></th>
        <th scope="col"><?php echo TABLE_HEADING_OPT_NAME; ?></th>
        <th scope="col"><?php echo TABLE_HEADING_OPT_VALUE; ?></th>
        <th scope="col" class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
      </tr>
    </thead>
    <tbody>                
<?php
    $next_id = 1;
    $rows = 0;
    $values = tep_db_query($values);
    while ($values_values = tep_db_fetch_array($values)) {
      $options_name = tep_options_name($values_values['products_options_id']);
      $values_name = $values_values['products_options_values_name'];
      $rows++;
?>
        <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
      if (($action == 'update_option_value') && ($_GET['value_id'] == $values_values['products_options_values_id'])) {
        echo '<form name="values" action="' . tep_href_link('products_attributes.php', 'action=update_value&' . $page_info) . '" method="post">';
        $inputs = '';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          $value_name = tep_db_query("select products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$values_values['products_options_values_id'] . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
          $value_name = tep_db_fetch_array($value_name);
          $inputs .= $languages[$i]['code'] . ':<input type="text" name="value_name[' . $languages[$i]['id'] . ']" size="15" value="' . $value_name['products_options_values_name'] . '">';
        }
?>
        <td align="center"><?php echo $values_values['products_options_values_id']; ?><input type="hidden" name="value_id" value="<?php echo $values_values['products_options_values_id']; ?>"></td>
        <td align="center"><?php echo "\n"; ?><select name="option_id">
<?php
        $options = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$languages_id . "' order by products_options_name");
        while ($options_values = tep_db_fetch_array($options)) {
          echo "\n" . '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '"';
          if ($values_values['products_options_id'] == $options_values['products_options_id']) { 
            echo ' selected';
          }
          echo '>' . $options_values['products_options_name'] . '</option>';
        } 
?>
        </select></td>
        <td><?php echo $inputs; ?></td>
        <td align="right"><?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('products_attributes.php', $page_info. '&p=3')); ?></td>
<?php
        echo '</form>';
      } else {
?>
        <td align="center"><?php echo $values_values["products_options_values_id"]; ?></td>
        <td align="center"><?php echo $options_name; ?></td>
        <td><?php echo $values_name; ?></td>
        <td align="right"><?php echo tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('products_attributes.php', 'action=update_option_value&value_id=' . $values_values['products_options_values_id'] . '&' . $page_info), null, null, 'secondary') . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('products_attributes.php', 'action=delete_option_value&value_id=' . $values_values['products_options_values_id'] . '&' . $page_info. '&p=3')); ?></td>
<?php
      }
      $max_values_id_query = tep_db_query("select max(products_options_values_id) + 1 as next_id from " . TABLE_PRODUCTS_OPTIONS_VALUES);
      $max_values_id_values = tep_db_fetch_array($max_values_id_query);
      $next_id = $max_values_id_values['next_id'];
    }
?>
        </tr>
<?php
    if ($action != 'update_option_value') {
?>
        <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
      echo '<form name="values" action="' . tep_href_link('products_attributes.php', 'action=add_product_option_values&' . $page_info) . '" method="post">';
?>
        <td align="center"><?php echo $next_id; ?></td>
        <td align="center"><select name="option_id">
<?php
      $options = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_name");
      while ($options_values = tep_db_fetch_array($options)) {
        echo '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '">' . $options_values['products_options_name'] . '</option>';
      }
      $inputs = '';
      for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
        $inputs .= $languages[$i]['code'] . ':<input type="text" name="value_name[' . $languages[$i]['id'] . ']" size="15">';
      }
?>
        </select></td>
        <td><input type="hidden" name="value_id" value="<?php echo $next_id; ?>"><?php echo $inputs; ?></td>
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
echo $values_links;
?>
</div>
</div>
</div>