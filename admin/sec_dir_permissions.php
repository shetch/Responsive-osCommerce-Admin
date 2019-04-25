<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  function tep_opendir($path) {
    $path = rtrim($path, '/') . '/';

    $exclude_array = array('.', '..', '.DS_Store', 'Thumbs.db');

    $result = array();

    if ($handle = opendir($path)) {
      while (false !== ($filename = readdir($handle))) {
        if (!in_array($filename, $exclude_array)) {
          $file = array('name' => $path . $filename,
                        'is_dir' => is_dir($path . $filename),
                        'writable' => tep_is_writable($path . $filename));

          $result[] = $file;

          if ($file['is_dir'] == true) {
            $result = array_merge($result, tep_opendir($path . $filename));
          }
        }
      }

      closedir($handle);
    }

    return $result;
  }

  $whitelist_array = array();

  $whitelist_query = tep_db_query("select directory from " . TABLE_SEC_DIRECTORY_WHITELIST);
  while ($whitelist = tep_db_fetch_array($whitelist_query)) {
    $whitelist_array[] = $whitelist['directory'];
  }

  $admin_dir = basename(DIR_FS_ADMIN);

  if ($admin_dir != 'admin') {
    for ($i=0, $n=sizeof($whitelist_array); $i<$n; $i++) {
      if (substr($whitelist_array[$i], 0, 6) == 'admin/') {
        $whitelist_array[$i] = $admin_dir . substr($whitelist_array[$i], 5);
      }
    }
  }

  require('includes/template_top.php');
?>
<div class="row">
  <div class="col-sm-12">
        <div class="card mb-3">
                  <div class="card-header">
                    <h3><i class="fa fa-flask"></i> <?php echo HEADING_TITLE; ?>
                    <span style="float:right"></span></h3>
                  </div>
                  <div class="card-body">  
                  <div class="table-responsive">
                  <table class="table table-hover">
                  <thead>
                    <tr>
                      <th scope="col"><?php echo TABLE_HEADING_DIRECTORIES; ?></th>
                      <th scope="col"><?php echo TABLE_HEADING_WRITABLE; ?></th>
                      <th scope="col"><?php echo TABLE_HEADING_RECOMMENDED; ?></th>
                    </tr>
                  </thead>
                  <tbody>
<?php   
  foreach (tep_opendir(DIR_FS_CATALOG) as $file) {
    if ($file['is_dir']) {
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                <td class="dataTableContent"><?php echo substr($file['name'], strlen(DIR_FS_CATALOG)); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_image('images/icons/' . (($file['writable'] == true) ? 'tick.gif' : 'cross.gif')); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_image('images/icons/' . (in_array(substr($file['name'], strlen(DIR_FS_CATALOG)), $whitelist_array) ? 'tick.gif' : 'cross.gif')); ?></td>
              </tr>
<?php
    }
  }
?>
              <tr>
                <td colspan="3" class="smallText"><?php echo TEXT_DIRECTORY . ' ' . DIR_FS_CATALOG; ?></td>
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
