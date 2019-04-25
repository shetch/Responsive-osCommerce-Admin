<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2018 osCommerce
  Released under the GNU General Public License
*/
  require('includes/application_top.php');
  $directory = DIR_FS_CATALOG . 'includes/actions/';
  require('includes/template_top.php');
?>
<div class="row">
  <div class="col-sm-12">
        <div class="card mb-3">
                  <div class="card-header">
                    <h3><i class="fa fa-tasks"></i> <?php echo HEADING_TITLE; ?>
                    <span style="float:right"></span></h3>
                  </div>
                  <div class="card-body">  
                    <div class="table-responsive">
                      <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th scope="col"><?php echo TABLE_HEADING_FILE; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_ACTION; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_CLASS; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_METHOD; ?></th>
                        </tr>
                      </thead>
                      <tbody>
<?php
  $files = array_diff(scandir($directory), array('.', '..'));
  foreach ($files as $file) {
    $code = substr($file, 0, strrpos($file, '.'));
	  $class = 'osC_Actions_' . $code;
    if ( !class_exists($class) ) {
      include($directory . '/' . $file);
    }
    $obj = new $class();
    foreach (get_class_methods($obj) as $method) {
      ?>
      <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
        <td class="dataTableContent"><?php echo $file; ?></td>
        <td class="dataTableContent"><?php echo $code; ?></td>
        <td class="dataTableContent"><?php echo $class; ?></td>
        <td class="dataTableContent"><?php echo $method; ?></td>
      </tr>
    <?php
    }
  }
?>
</tbody>         
                    </table>
                  </div>       
                  <p class="smallText"><?php echo TEXT_ACTIONS_DIRECTORY . ' ' . DIR_FS_CATALOG . 'includes/actions/'; ?></p>
            </div><!-- end card-body-->															
          </div><!-- end card-->	
  </div><!-- end col-->
  </div>
<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
