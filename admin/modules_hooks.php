<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $directory = DIR_FS_CATALOG . 'includes/hooks/';

  require('includes/template_top.php');
?>

<div class="row">
  <div class="col-sm-12">
        <div class="card mb-3">
                  <div class="card-header">
                    <h3><i class="fa fa-money"></i> <?php echo HEADING_TITLE; ?>
                    <span style="float:right"></span></h3>
                  </div>
                  <div class="card-body">  
                    <div class="table-responsive">
                      <table class="table table-sm table-striped">
                      
                      <tbody>

<?php
  if ( $dir = @dir($directory) ) {
    while ( $file = $dir->read() ) {
      if ( is_dir($directory . '/' . $file) && !in_array($file, array('.', '..')) ) {
?>

  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent" colspan="2"><?php echo $file; ?></td>
  </tr>

<?php
        if ( $dir2 = @dir($directory . '/' . $file) ) {
          while ( $file2 = $dir2->read() ) {
            if ( is_dir($directory . '/' . $file . '/' . $file2) && !in_array($file2, array('.', '..')) ) {
              if ( $dir3 = @dir($directory . '/' . $file . '/' . $file2) ) {
                while ( $file3 = $dir3->read() ) {
                  if ( !is_dir($directory . '/' . $file . '/' . $file2 . '/' . $file3) ) {
                    if ( substr($file3, strrpos($file3, '.')) == '.php' ) {
                      $code = substr($file3, 0, strrpos($file3, '.'));
                      $class = 'hook_' . $file . '_' . $file2 . '_' . $code;

                      if ( !class_exists($class) ) {
                        include($directory . '/' . $file . '/' . $file2 . '/' . $file3);
                      }

                      $obj = new $class();

                      foreach ( get_class_methods($obj) as $method ) {
                        if ( substr($method, 0, 7) == 'listen_' ) {
?>

  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
    <td class="dataTableContent"><?php echo $file2 . '/' . $file3; ?></td>
    <td class="dataTableContent"><?php echo substr($method, 7); ?></td>
  </tr>

<?php
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
?>

</tbody>         
                    </table>
                  </div>     
                  <p class="smallText"><?php echo TEXT_HOOKS_DIRECTORY . ' ' . DIR_FS_CATALOG . 'includes/hooks/'; ?></p>
            </div><!-- end card-body-->															
        </div><!-- end card-->	
  </div><!-- end col-->


</div>
<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
