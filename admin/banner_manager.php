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
  $banner_extension = tep_banner_image_extension();
  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
          tep_set_banner_status($_GET['bID'], $_GET['flag']);
          $messageStack->add_session(SUCCESS_BANNER_STATUS_UPDATED, 'success');
        } else {
          $messageStack->add_session(ERROR_UNKNOWN_STATUS_FLAG, 'error');
        }
        tep_redirect(tep_href_link('banner_manager.php', 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']));
        break;
      case 'insert':
      case 'update':
        if (isset($_POST['banners_id'])) $banners_id = tep_db_prepare_input($_POST['banners_id']);
        $banners_title = tep_db_prepare_input($_POST['banners_title']);
        $banners_url = tep_db_prepare_input($_POST['banners_url']);
        $new_banners_group = tep_db_prepare_input($_POST['new_banners_group']);
        $banners_group = (empty($new_banners_group)) ? tep_db_prepare_input($_POST['banners_group']) : $new_banners_group;
        $banners_html_text = tep_db_prepare_input($_POST['banners_html_text']);
        $banners_image_local = tep_db_prepare_input($_POST['banners_image_local']);
        $banners_image_target = tep_db_prepare_input($_POST['banners_image_target']);
        $db_image_location = '';
        $expires_date = tep_db_prepare_input($_POST['expires_date']);
        $expires_impressions = tep_db_prepare_input($_POST['expires_impressions']);
        $date_scheduled = tep_db_prepare_input($_POST['date_scheduled']);
        $banner_error = false;
        if (empty($banners_title)) {
          $messageStack->add(ERROR_BANNER_TITLE_REQUIRED, 'error');
          $banner_error = true;
        }
        if (empty($banners_group)) {
          $messageStack->add(ERROR_BANNER_GROUP_REQUIRED, 'error');
          $banner_error = true;
        }
        if (empty($banners_html_text)) {
          if (empty($banners_image_local)) {
            $banners_image = new upload('banners_image');
            $banners_image->set_destination(DIR_FS_CATALOG_IMAGES . $banners_image_target);
            if ( ($banners_image->parse() == false) || ($banners_image->save() == false) ) {
              $banner_error = true;
            }
          }
        }
        if ($banner_error == false) {
          $db_image_location = (tep_not_null($banners_image_local)) ? $banners_image_local : $banners_image_target . $banners_image->filename;
          $sql_data_array = array('banners_title' => $banners_title,
                                  'banners_url' => $banners_url,
                                  'banners_image' => $db_image_location,
                                  'banners_group' => $banners_group,
                                  'banners_html_text' => $banners_html_text,
                                  'expires_date' => 'null',
                                  'expires_impressions' => 0,
                                  'date_scheduled' => 'null');
          if ($action == 'insert') {
            $insert_sql_data = array('date_added' => 'now()',
                                     'status' => '1');
            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
            tep_db_perform(TABLE_BANNERS, $sql_data_array);
            $banners_id = tep_db_insert_id();
            $messageStack->add_session(SUCCESS_BANNER_INSERTED, 'success');
          } elseif ($action == 'update') {
            tep_db_perform(TABLE_BANNERS, $sql_data_array, 'update', "banners_id = '" . (int)$banners_id . "'");
            $messageStack->add_session(SUCCESS_BANNER_UPDATED, 'success');
          }
          if (tep_not_null($expires_date)) {
            $expires_date = substr($expires_date, 0, 4) . substr($expires_date, 5, 2) . substr($expires_date, 8, 2);
            tep_db_query("update " . TABLE_BANNERS . " set expires_date = '" . tep_db_input($expires_date) . "', expires_impressions = null where banners_id = '" . (int)$banners_id . "'");
          } elseif (tep_not_null($expires_impressions)) {
            tep_db_query("update " . TABLE_BANNERS . " set expires_impressions = '" . tep_db_input($expires_impressions) . "', expires_date = null where banners_id = '" . (int)$banners_id . "'");
          }
          if (tep_not_null($date_scheduled)) {
            $date_scheduled = substr($date_scheduled, 0, 4) . substr($date_scheduled, 5, 2) . substr($date_scheduled, 8, 2);
            tep_db_query("update " . TABLE_BANNERS . " set status = '0', date_scheduled = '" . tep_db_input($date_scheduled) . "' where banners_id = '" . (int)$banners_id . "'");
          }
          tep_redirect(tep_href_link('banner_manager.php', (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'bID=' . $banners_id));
        } else {
          $action = 'new';
        }
        break;
      case 'deleteconfirm':
        $banners_id = tep_db_prepare_input($_GET['bID']);
        if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
          $banner_query = tep_db_query("select banners_image from " . TABLE_BANNERS . " where banners_id = '" . (int)$banners_id . "'");
          $banner = tep_db_fetch_array($banner_query);
          if (is_file(DIR_FS_CATALOG_IMAGES . $banner['banners_image'])) {
            if (tep_is_writable(DIR_FS_CATALOG_IMAGES . $banner['banners_image'])) {
              unlink(DIR_FS_CATALOG_IMAGES . $banner['banners_image']);
            } else {
              $messageStack->add_session(ERROR_IMAGE_IS_NOT_WRITEABLE, 'error');
            }
          } else {
            $messageStack->add_session(ERROR_IMAGE_DOES_NOT_EXIST, 'error');
          }
        }
        tep_db_query("delete from " . TABLE_BANNERS . " where banners_id = '" . (int)$banners_id . "'");
        tep_db_query("delete from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$banners_id . "'");
        if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
          if (is_file('images/graphs/banner_infobox-' . $banners_id . '.' . $banner_extension)) {
            if (tep_is_writable('images/graphs/banner_infobox-' . $banners_id . '.' . $banner_extension)) {
              unlink('images/graphs/banner_infobox-' . $banners_id . '.' . $banner_extension);
            }
          }
          if (is_file('images/graphs/banner_yearly-' . $banners_id . '.' . $banner_extension)) {
            if (tep_is_writable('images/graphs/banner_yearly-' . $banners_id . '.' . $banner_extension)) {
              unlink('images/graphs/banner_yearly-' . $banners_id . '.' . $banner_extension);
            }
          }
          if (is_file('images/graphs/banner_monthly-' . $banners_id . '.' . $banner_extension)) {
            if (tep_is_writable('images/graphs/banner_monthly-' . $banners_id . '.' . $banner_extension)) {
              unlink('images/graphs/banner_monthly-' . $banners_id . '.' . $banner_extension);
            }
          }
          if (is_file('images/graphs/banner_daily-' . $banners_id . '.' . $banner_extension)) {
            if (tep_is_writable('images/graphs/banner_daily-' . $banners_id . '.' . $banner_extension)) {
              unlink('images/graphs/banner_daily-' . $banners_id . '.' . $banner_extension);
            }
          }
        }
        $messageStack->add_session(SUCCESS_BANNER_REMOVED, 'success');
        tep_redirect(tep_href_link('banner_manager.php', 'page=' . $_GET['page']));
        break;
    }
  }
// check if the graphs directory exists
  $dir_ok = false;
  if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
    if (is_dir('images/graphs')) {
      if (tep_is_writable('images/graphs')) {
        $dir_ok = true;
      } else {
        $messageStack->add(ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE, 'error');
      }
    } else {
      $messageStack->add(ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST, 'error');
    }
  }
  require('includes/template_top.php');
?>
<script type="text/javascript"><!--
function popupImageWindow(url) {
  window.open(url,'popupImageWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
<style>
.btn {
  margin:1px;
}
</style>
<?php
  if ($action == 'new') {
?>
<?php
    $form_action = 'insert';
    $parameters = array('expires_date' => '',
                        'date_scheduled' => '',
                        'banners_title' => '',
                        'banners_url' => '',
                        'banners_group' => '',
                        'banners_image' => '',
                        'banners_html_text' => '',
                        'expires_impressions' => '');
    $bInfo = new objectInfo($parameters);
    if (isset($_GET['bID'])) {
      $form_action = 'update';
      $bID = tep_db_prepare_input($_GET['bID']);
      $banner_query = tep_db_query("select banners_title, banners_url, banners_image, banners_group, banners_html_text, status, date_format(date_scheduled, '%Y/%m/%d') as date_scheduled, date_format(expires_date, '%Y/%m/%d') as expires_date, expires_impressions, date_status_change from " . TABLE_BANNERS . " where banners_id = '" . (int)$bID . "'");
      $banner = tep_db_fetch_array($banner_query);
      $bInfo->objectInfo($banner);
    } elseif (tep_not_null($_POST)) {
      $bInfo->objectInfo($_POST);
    }
    $groups_array = array();
    $groups_query = tep_db_query("select distinct banners_group from " . TABLE_BANNERS . " order by banners_group");
    while ($groups = tep_db_fetch_array($groups_query)) {
      $groups_array[] = array('id' => $groups['banners_group'], 'text' => $groups['banners_group']);
    }
?>
<div class="row">
    <div class="col-sm-12">
      <div class="card mb-3">
        <div class="card-header">
          <h3><i class="fa fa-image"></i> <?php echo HEADING_TITLE; ?>
          <span style="float:right"></span></h3>
        </div>
        <div class="card-body">  
          <div class="table-responsive">
            <table class="table">
            <?php echo tep_draw_form('new_banner', 'banner_manager.php', (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data"'); 
      if ($form_action == 'update') echo tep_draw_hidden_field('banners_id', $bID); ?>
            <tbody>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_TITLE; ?></td>
            <td class="main"><?php echo tep_draw_input_field('banners_title', $bInfo->banners_title, '', true); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_URL; ?></td>
            <td class="main"><?php echo tep_draw_input_field('banners_url', $bInfo->banners_url); ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_BANNERS_GROUP; ?></td>
            <td class="main"><?php echo tep_draw_pull_down_menu('banners_group', $groups_array, $bInfo->banners_group) . TEXT_BANNERS_NEW_GROUP . '<br />' . tep_draw_input_field('new_banners_group', '', '', ((sizeof($groups_array) > 0) ? false : true)); ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_BANNERS_IMAGE; ?></td>
            <td class="main"><?php echo tep_draw_file_field('banners_image') . ' ' . TEXT_BANNERS_IMAGE_LOCAL . '<br />' . DIR_FS_CATALOG_IMAGES . tep_draw_input_field('banners_image_local', (isset($bInfo->banners_image) ? $bInfo->banners_image : '')); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_IMAGE_TARGET; ?></td>
            <td class="main"><?php echo DIR_FS_CATALOG_IMAGES . tep_draw_input_field('banners_image_target'); ?></td>
          </tr>
          <tr>
            <td valign="top" class="main"><?php echo TEXT_BANNERS_HTML_TEXT; ?></td>
            <td class="main"><?php echo tep_draw_textarea_field('banners_html_text', 'soft', '60', '5', $bInfo->banners_html_text); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_SCHEDULED_AT; ?></td>
            <td class="main"><?php echo tep_draw_input_field('date_scheduled', $bInfo->date_scheduled, 'id="date_scheduled"') . ' <small>(YYYY-MM-DD)</small>'; ?></td>
          </tr>
          <tr>
            <td valign="top" class="main"><?php echo TEXT_BANNERS_EXPIRES_ON; ?></td>
            <td class="main"><?php echo tep_draw_input_field('expires_date', $bInfo->expires_date, 'id="expires_date"') . ' <small>(YYYY-MM-DD)</small>' . TEXT_BANNERS_OR_AT . '<br />' . tep_draw_input_field('expires_impressions', $bInfo->expires_impressions, 'maxlength="7" size="7"') . ' ' . TEXT_BANNERS_IMPRESSIONS; ?></td>
          </tr>
          </tbody>         
        </table>
        <table class="table">
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_BANNER_NOTE . '<br />' . TEXT_BANNERS_INSERT_NOTE . '<br />' . TEXT_BANNERS_EXPIRCY_NOTE . '<br />' . TEXT_BANNERS_SCHEDULE_NOTE; ?></td>
            <td class="smallText" align="right" valign="top" nowrap><?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('banner_manager.php', (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['bID']) ? 'bID=' . $_GET['bID'] : ''))); ?></td>
          </tr>
      </form>
      </table>
      </div>       
    </div><!-- end card-body-->															
  </div><!-- end card-->	
</div><!-- end col-->
</div> 
<?php
  } else {
?>
<div class="row">
  <div class="col-sm-9">
        <div class="card mb-3">
                  <div class="card-header">
                    <h3><i class="fa fa-picture-o"></i> <?php echo HEADING_TITLE; ?>
                    <span style="float:right">
                   <?php echo tep_draw_button(IMAGE_NEW_BANNER, 'plus', tep_href_link('banner_manager.php', 'action=new')); ?>
                  </span></h3>
                  </div>
                  <div class="card-body">  
                    <div class="table-responsive">
                      <table class="table table-sm table-striped table-hover">
                      <thead>
                        <tr>
                          <th scope="col"><?php echo TABLE_HEADING_BANNERS; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_GROUPS; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_STATISTICS; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_STATUS; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_ACTION; ?></th>
                        </tr>
                      </thead>
                      <tbody>
<?php
    $banners_query_raw = "select banners_id, banners_title, banners_image, banners_group, status, expires_date, expires_impressions, date_status_change, date_scheduled, date_added from " . TABLE_BANNERS . " order by banners_title, banners_group";
    $banners_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $banners_query_raw, $banners_query_numrows);
    $banners_query = tep_db_query($banners_query_raw);
    while ($banners = tep_db_fetch_array($banners_query)) {
      $info_query = tep_db_query("select sum(banners_shown) as banners_shown, sum(banners_clicked) as banners_clicked from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$banners['banners_id'] . "'");
      $info = tep_db_fetch_array($info_query);
      if ((!isset($_GET['bID']) || (isset($_GET['bID']) && ($_GET['bID'] == $banners['banners_id']))) && !isset($bInfo) && (substr($action, 0, 3) != 'new')) {
        $bInfo_array = array_merge($banners, $info);
        $bInfo = new objectInfo($bInfo_array);
      }
      $banners_shown = ($info['banners_shown'] != '') ? $info['banners_shown'] : '0';
      $banners_clicked = ($info['banners_clicked'] != '') ? $info['banners_clicked'] : '0';
      if (isset($bInfo) && is_object($bInfo) && ($banners['banners_id'] == $bInfo->banners_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('banner_statistics.php', 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id) . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('banner_manager.php', 'page=' . $_GET['page'] . '&bID=' . $banners['banners_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="javascript:popupImageWindow(\'popup_image.php?banner=' . $banners['banners_id'] . '\')">' . tep_image('images/icon_popup.gif', 'View Banner') . '</a>&nbsp;' . $banners['banners_title']; ?></td>
                <td class="dataTableContent" align="right"><?php echo $banners['banners_group']; ?></td>
                <td class="dataTableContent" align="right"><?php echo $banners_shown . ' / ' . $banners_clicked; ?></td>
                <td class="dataTableContent" align="right">
<?php
      if ($banners['status'] == '1') {
        echo '<i class="fa fa-dot-circle-o" aria-hidden="true"></i>' . '&nbsp;&nbsp;<a href="' . tep_href_link('banner_manager.php', 'page=' . $_GET['page'] . '&bID=' . $banners['banners_id'] . '&action=setflag&flag=0') . '">' . '<i class="fa fa-circle-o" aria-hidden="true"></i>' . '</a>';
      } else {
        echo '<a href="' . tep_href_link('banner_manager.php', 'page=' . $_GET['page'] . '&bID=' . $banners['banners_id'] . '&action=setflag&flag=1') . '">' . '<i class="fa fa-circle-o" aria-hidden="true"></i>' . '</a>&nbsp;&nbsp;' . '<i class="fa fa-dot-circle-o" aria-hidden="true"></i>';
      }
?></td>
                <td class="dataTableContent" align="right"><?php echo '<a href="' . tep_href_link('banner_statistics.php', 'page=' . $_GET['page'] . '&bID=' . $banners['banners_id']) . '">' . '<i class="fa fa-line-chart" aria-hidden="true"></i>' . '</a>&nbsp;'; if (isset($bInfo) && is_object($bInfo) && ($banners['banners_id'] == $bInfo->banners_id)) { echo '<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>'; } else { echo '<a href="' . tep_href_link('banner_manager.php', 'page=' . $_GET['page'] . '&bID=' . $banners['banners_id']) . '">' . '<i class="fa fa-info-circle" aria-hidden="true"></i>' . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              </tbody>         
              </table>  
              <table class="table">  
                  <tr>
                    <td class="smallText" valign="top"><?php echo $banners_split->display_count($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_BANNERS); ?></td>
                    <td class="smallText" align="right"><?php echo $banners_split->display_links($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
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
      $heading[] = array('text' => '<strong>' . $bInfo->banners_title . '</strong>');
      $contents = array('form' => tep_draw_form('banners', 'banner_manager.php', 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $bInfo->banners_title . '</strong>');
      if ($bInfo->banners_image) $contents[] = array('text' => '<br />' . tep_draw_checkbox_field('delete_image', 'on', true) . ' ' . TEXT_INFO_DELETE_IMAGE);
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('banner_manager.php', 'page=' . $_GET['page'] . '&bID=' . $_GET['bID'])));
      break;
    default:
      if (is_object($bInfo)) {
        $heading[] = array('text' => '<strong>' . $bInfo->banners_title . '</strong>');
        $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('banner_manager.php', 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=new'), null, null, 'secondary') . ' ' . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('banner_manager.php', 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=delete'), null, null, 'primary') . ' ' . tep_draw_button(IMAGE_DETAILS, 'info', tep_href_link('banner_statistics.php', 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id)));
        $contents[] = array('text' => '<br />' . TEXT_BANNERS_DATE_ADDED . ' ' . tep_date_short($bInfo->date_added));
        if ( (function_exists('imagecreate')) && ($dir_ok) && ($banner_extension) ) {
          $banner_id = $bInfo->banners_id;
          $days = '3';
          include('includes/graphs/banner_infobox.php');
          $contents[] = array('align' => 'center', 'text' => '<br />' . tep_image('images/graphs/banner_infobox-' . $banner_id . '.' . $banner_extension));
        } else {
          include('includes/functions/html_graphs.php');
          $contents[] = array('align' => 'center', 'text' => '<br />' . tep_banner_graph_infoBox($bInfo->banners_id, '3'));
        }
        $contents[] = array('text' => tep_image('images/graph_hbar_blue.gif', 'Blue', '5', '5') . ' ' . TEXT_BANNERS_BANNER_VIEWS . '<br />' . tep_image('images/graph_hbar_red.gif', 'Red', '5', '5') . ' ' . TEXT_BANNERS_BANNER_CLICKS);
        if ($bInfo->date_scheduled) $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, tep_date_short($bInfo->date_scheduled)));
        if ($bInfo->expires_date) {
          $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, tep_date_short($bInfo->expires_date)));
        } elseif ($bInfo->expires_impressions) {
          $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo->expires_impressions));
        }
        if ($bInfo->date_status_change) $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_STATUS_CHANGE, tep_date_short($bInfo->date_status_change)));
      }
      break;
  }
?>
<?php
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
  }
?>
<?php
  require('includes/template_bottom.php');
?>
<script src="assets/plugins/datetimepicker/js/moment.min.js"></script>
<script src="assets/plugins/datetimepicker/js/daterangepicker.js"></script>
<script>
  $(document).ready(function () {
    $('input[name="expires_date"]').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        drops: 'down',
        locale: {
            cancelLabel: 'Clear',
            format: 'YYYY-MM-DD'
        }
    });
    $('input[name="expires_date"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });
    $('input[name="expires_date"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
    $('.daterangepicker').css("width","360px");
    $('input[name="date_scheduled"]').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        drops: 'down',
        locale: {
            cancelLabel: 'Clear',
            format: 'YYYY-MM-DD'
        }
  });
  $('input[name="date_scheduled"]').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('YYYY-MM-DD'));
  });
  $('input[name="date_scheduled"]').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val('');
  });
  $('.daterangepicker').css("width","360px");
  });
  </script>
<?php
  require('includes/application_bottom.php');
?>
