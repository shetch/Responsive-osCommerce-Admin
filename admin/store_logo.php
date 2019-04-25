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
      case 'save':
        $error = false;

        $store_logo = new upload('store_logo');
        $store_logo->set_extensions(array('png', 'gif', 'jpg', 'svg'));
        $store_logo->set_destination(DIR_FS_CATALOG_IMAGES);

        if ($store_logo->parse()) {
          if ($store_logo->save()) {
            $messageStack->add_session(SUCCESS_LOGO_UPDATED, 'success');
            tep_db_query("update configuration set configuration_value = '" . tep_db_input($store_logo->filename) . "' where configuration_value = '" . STORE_LOGO . "'");
          } else {
            $error = true;
          }
        } else {
          $error = true;
        }

        if ($error == false) {
          tep_redirect(tep_href_link('store_logo.php'));
        }
        break;
    }
  }

  if (!tep_is_writable(DIR_FS_CATALOG_IMAGES)) {
    $messageStack->add(sprintf(ERROR_IMAGES_DIRECTORY_NOT_WRITEABLE, tep_href_link('sec_dir_permissions.php')), 'error');
  }

  require('includes/template_top.php');
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
       
          <tbody>    
      <tr>
        <td><?php echo tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES .  STORE_LOGO); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_form('logo', 'store_logo.php', 'action=save', 'post', 'enctype="multipart/form-data"'); ?>
          <table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td class="main" valign="top"><?php echo TEXT_LOGO_IMAGE; ?></td>
              <td class="main"><?php echo tep_draw_file_field('store_logo'); ?></td>
              <td class="smallText"><?php echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary'); ?></td>
            </tr>
          </table>
        </form></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo TEXT_FORMAT_AND_LOCATION; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo DIR_FS_CATALOG_IMAGES .  STORE_LOGO; ?></td>
      </tr>
      </tbody>         
        </table>
      </div>       
    </div><!-- end card-body-->															
  </div><!-- end card-->	
</div><!-- end col-->
</div>
<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
