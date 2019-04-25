<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
        $zone_country_id = tep_db_prepare_input($_POST['zone_country_id']);
        $zone_code = tep_db_prepare_input($_POST['zone_code']);
        $zone_name = tep_db_prepare_input($_POST['zone_name']);

        tep_db_query("insert into " . TABLE_ZONES . " (zone_country_id, zone_code, zone_name) values ('" . (int)$zone_country_id . "', '" . tep_db_input($zone_code) . "', '" . tep_db_input($zone_name) . "')");

        tep_redirect(tep_href_link('zones.php'));
        break;
      case 'save':
        $zone_id = tep_db_prepare_input($_GET['cID']);
        $zone_country_id = tep_db_prepare_input($_POST['zone_country_id']);
        $zone_code = tep_db_prepare_input($_POST['zone_code']);
        $zone_name = tep_db_prepare_input($_POST['zone_name']);

        tep_db_query("update " . TABLE_ZONES . " set zone_country_id = '" . (int)$zone_country_id . "', zone_code = '" . tep_db_input($zone_code) . "', zone_name = '" . tep_db_input($zone_name) . "' where zone_id = '" . (int)$zone_id . "'");

        tep_redirect(tep_href_link('zones.php', 'page=' . $_GET['page'] . '&cID=' . $zone_id));
        break;
      case 'deleteconfirm':
        $zone_id = tep_db_prepare_input($_GET['cID']);

        tep_db_query("delete from " . TABLE_ZONES . " where zone_id = '" . (int)$zone_id . "'");

        tep_redirect(tep_href_link('zones.php', 'page=' . $_GET['page']));
        break;
    }
  }

  require('includes/template_top.php');
?>
<div class="row">
  <div class="col-sm-9">
        <div class="card mb-3">
                  <div class="card-header">
                    <h3><i class="fa fa-money"></i> <?php echo HEADING_TITLE; ?>
                    <span style="float:right"></span></h3>
                  </div>
                  <div class="card-body">  
                    <div class="table-responsive">
                      <table class="table table-sm table-hover">
                      <thead>
                        <tr>
                          <th scope="col"><?php echo TABLE_HEADING_COUNTRY_NAME; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_ZONE_NAME; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_ZONE_CODE; ?></th>
                          <th class="text-right" scope="col"><?php echo TABLE_HEADING_ACTION; ?></th>
                        </tr>
                      </thead>
                      <tbody>
    
            
<?php
  $zones_query_raw = "select z.zone_id, c.countries_id, c.countries_name, z.zone_name, z.zone_code, z.zone_country_id from " . TABLE_ZONES . " z, " . TABLE_COUNTRIES . " c where z.zone_country_id = c.countries_id order by c.countries_name, z.zone_name";
  $zones_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $zones_query_raw, $zones_query_numrows);
  $zones_query = tep_db_query($zones_query_raw);
  while ($zones = tep_db_fetch_array($zones_query)) {
    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $zones['zone_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cInfo = new objectInfo($zones);
    }

    if (isset($cInfo) && is_object($cInfo) && ($zones['zone_id'] == $cInfo->zone_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('zones.php', 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('zones.php', 'page=' . $_GET['page'] . '&cID=' . $zones['zone_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $zones['countries_name']; ?></td>
                <td class="dataTableContent"><?php echo $zones['zone_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $zones['zone_code']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($zones['zone_id'] == $cInfo->zone_id) ) { echo '<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>'; } else { echo '<a href="' . tep_href_link('zones.php', 'page=' . $_GET['page'] . '&cID=' . $zones['zone_id']) . '">' . '<i class="fa fa-info-circle" aria-hidden="true"></i>' . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
        
        </tbody>         
       </table>
       <table class="table">
         <tr>
            <td class="smallText" valign="top"><?php echo $zones_split->display_count($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ZONES); ?></td>
            <td class="smallText" align="right"><?php echo $zones_split->display_links($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
        </tr> 
      
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td class="smallText" colspan="2" align="right"><?php echo tep_draw_button(IMAGE_NEW_ZONE, 'plus', tep_href_link('zones.php', 'page=' . $_GET['page'] . '&action=new')); ?></td>
                  </tr>
<?php
  }
?>
            </table> 
            </div>       
            </div><!-- end card-body-->															
        </div><!-- end card-->	
  </div><!-- end col-->
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_ZONE . '</strong>');

      $contents = array('form' => tep_draw_form('zones', 'zones.php', 'page=' . $_GET['page'] . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_ZONES_NAME . '<br />' . tep_draw_input_field('zone_name'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_ZONES_CODE . '<br />' . tep_draw_input_field('zone_code'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . tep_draw_pull_down_menu('zone_country_id', tep_get_countries()));
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('zones.php', 'page=' . $_GET['page'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_ZONE . '</strong>');

      $contents = array('form' => tep_draw_form('zones', 'zones.php', 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_ZONES_NAME . '<br />' . tep_draw_input_field('zone_name', $cInfo->zone_name));
      $contents[] = array('text' => '<br />' . TEXT_INFO_ZONES_CODE . '<br />' . tep_draw_input_field('zone_code', $cInfo->zone_code));
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . tep_draw_pull_down_menu('zone_country_id', tep_get_countries(), $cInfo->countries_id));
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('zones.php', 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_ZONE . '</strong>');

      $contents = array('form' => tep_draw_form('zones', 'zones.php', 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $cInfo->zone_name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary', null, 'secondary') . ' ' .  tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('zones.php', 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id)));
      break;
    default:
      if (isset($cInfo) && is_object($cInfo)) {
        $heading[] = array('text' => '<strong>' . $cInfo->zone_name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('zones.php', 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=edit'), null, null, 'secondary') . ' ' . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('zones.php', 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . TEXT_INFO_ZONES_NAME . '<br />' . $cInfo->zone_name . ' (' . $cInfo->zone_code . ')');
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . ' ' . $cInfo->countries_name);
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
    </div>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
