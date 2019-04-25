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
        $tax_class_title = tep_db_prepare_input($_POST['tax_class_title']);
        $tax_class_description = tep_db_prepare_input($_POST['tax_class_description']);

        tep_db_query("insert into " . TABLE_TAX_CLASS . " (tax_class_title, tax_class_description, date_added) values ('" . tep_db_input($tax_class_title) . "', '" . tep_db_input($tax_class_description) . "', now())");

        tep_redirect(tep_href_link('tax_classes.php'));
        break;
      case 'save':
        $tax_class_id = tep_db_prepare_input($_GET['tID']);
        $tax_class_title = tep_db_prepare_input($_POST['tax_class_title']);
        $tax_class_description = tep_db_prepare_input($_POST['tax_class_description']);

        tep_db_query("update " . TABLE_TAX_CLASS . " set tax_class_id = '" . (int)$tax_class_id . "', tax_class_title = '" . tep_db_input($tax_class_title) . "', tax_class_description = '" . tep_db_input($tax_class_description) . "', last_modified = now() where tax_class_id = '" . (int)$tax_class_id . "'");

        tep_redirect(tep_href_link('tax_classes.php', 'page=' . $_GET['page'] . '&tID=' . $tax_class_id));
        break;
      case 'deleteconfirm':
        $tax_class_id = tep_db_prepare_input($_GET['tID']);

        tep_db_query("delete from " . TABLE_TAX_CLASS . " where tax_class_id = '" . (int)$tax_class_id . "'");

        tep_redirect(tep_href_link('tax_classes.php', 'page=' . $_GET['page']));
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
          <th scope="col"><?php echo TABLE_HEADING_TAX_CLASSES; ?></th>
          <th class="text-right" scope="col"><?php echo TABLE_HEADING_ACTION; ?></th>
        </tr>
      </thead>
      <tbody>
   
<?php
  $classes_query_raw = "select tax_class_id, tax_class_title, tax_class_description, last_modified, date_added from " . TABLE_TAX_CLASS . " order by tax_class_title";
  $classes_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $classes_query_raw, $classes_query_numrows);
  $classes_query = tep_db_query($classes_query_raw);
  while ($classes = tep_db_fetch_array($classes_query)) {
    if ((!isset($_GET['tID']) || (isset($_GET['tID']) && ($_GET['tID'] == $classes['tax_class_id']))) && !isset($tcInfo) && (substr($action, 0, 3) != 'new')) {
      $tcInfo = new objectInfo($classes);
    }

    if (isset($tcInfo) && is_object($tcInfo) && ($classes['tax_class_id'] == $tcInfo->tax_class_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('tax_classes.php', 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo'              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('tax_classes.php', 'page=' . $_GET['page'] . '&tID=' . $classes['tax_class_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $classes['tax_class_title']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tcInfo) && is_object($tcInfo) && ($classes['tax_class_id'] == $tcInfo->tax_class_id)) { echo '<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>'; } else { echo '<a href="' . tep_href_link('tax_classes.php', 'page=' . $_GET['page'] . '&tID=' . $classes['tax_class_id']) . '">' . '<i class="fa fa-info-circle" aria-hidden="true"></i>' . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
            

          </tbody>         
      </table>
      <table class="table">
      <tr>
        <td class="smallText" valign="top"><?php echo $classes_split->display_count($classes_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_TAX_CLASSES); ?></td>
        <td class="smallText" align="right"><?php echo $classes_split->display_links($classes_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
      </tr>
      <?php
        if (empty($action)) {
      ?>
      <tr>
        <td class="smallText" colspan="2" align="right"><?php echo tep_draw_button(IMAGE_NEW_TAX_CLASS, 'plus', tep_href_link('tax_classes.php', 'page=' . $_GET['page'] . '&action=new')); ?></td>
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
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_TAX_CLASS . '</strong>');

      $contents = array('form' => tep_draw_form('classes', 'tax_classes.php', 'page=' . $_GET['page'] . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_TITLE . '<br />' . tep_draw_input_field('tax_class_title'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_DESCRIPTION . '<br />' . tep_draw_input_field('tax_class_description'));
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_SAVE, 'plus', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('tax_classes.php', 'page=' . $_GET['page'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_TAX_CLASS . '</strong>');

      $contents = array('form' => tep_draw_form('classes', 'tax_classes.php', 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_TITLE . '<br />' . tep_draw_input_field('tax_class_title', $tcInfo->tax_class_title));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_DESCRIPTION . '<br />' . tep_draw_input_field('tax_class_description', $tcInfo->tax_class_description));
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('tax_classes.php', 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_TAX_CLASS . '</strong>');

      $contents = array('form' => tep_draw_form('classes', 'tax_classes.php', 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $tcInfo->tax_class_title . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('tax_classes.php', 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id)));
      break;
    default:
      if (isset($tcInfo) && is_object($tcInfo)) {
        $heading[] = array('text' => '<strong>' . $tcInfo->tax_class_title . '</strong>');

        $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('tax_classes.php', 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit'), null, null, 'secondary') . ' ' . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('tax_classes.php', 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($tcInfo->date_added));
        $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($tcInfo->last_modified));
        $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_DESCRIPTION . '<br />' . $tcInfo->tax_class_description);
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
