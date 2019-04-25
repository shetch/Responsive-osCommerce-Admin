<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2014 osCommerce
  Released under the GNU General Public License
*/
  require('includes/application_top.php');
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (tep_not_null($action)) {
    if ($action == 'reset') {
      tep_reset_cache_block($_GET['block']);
    }
    tep_redirect(tep_href_link('cache.php'));
  }
// check if the cache directory exists
  if (is_dir(DIR_FS_CACHE)) {
    if (!tep_is_writable(DIR_FS_CACHE)) $messageStack->add(ERROR_CACHE_DIRECTORY_NOT_WRITEABLE, 'error');
  } else {
    $messageStack->add(ERROR_CACHE_DIRECTORY_DOES_NOT_EXIST, 'error');
  }
  require('includes/template_top.php');
?>
<div class="row">
  <div class="col-sm-12">
    <div class="card mb-3">
      <div class="card-header">
        <h3><i class="fa fa-adjust"></i> <?php echo HEADING_TITLE; ?>
          <span style="float:right"></span></h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th scope="col"><?php echo TABLE_HEADING_CACHE; ?></th>
                <th scope="col"><?php echo TABLE_HEADING_DATE_CREATED; ?></th>
                <th scope="col"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
              <?php
  if ($messageStack->size < 1) {
    $languages = tep_get_languages();
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
      if ($languages[$i]['code'] == DEFAULT_LANGUAGE) {
        $language = $languages[$i]['directory'];
      }
    }
    for ($i=0, $n=sizeof($cache_blocks); $i<$n; $i++) {
      $cached_file = preg_replace('/-language/', '-' . $language, $cache_blocks[$i]['file']);
      if (file_exists(DIR_FS_CACHE . $cached_file)) {
        $cache_mtime = strftime(DATE_TIME_FORMAT, filemtime(DIR_FS_CACHE . $cached_file));
      } else {
        $cache_mtime = TEXT_FILE_DOES_NOT_EXIST;
        $dir = dir(DIR_FS_CACHE);
        while ($cache_file = $dir->read()) {
          $cached_file = preg_replace('/-language/', '-' . $language, $cache_blocks[$i]['file']);
          if (preg_match('/^' . $cached_file. '/', $cache_file)) {
            $cache_mtime = strftime(DATE_TIME_FORMAT, filemtime(DIR_FS_CACHE . $cache_file));
            break;
          }
        }
        $dir->close();
      }
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                <td class="dataTableContent"><?php echo $cache_blocks[$i]['title']; ?></td>
                <td class="dataTableContent"><?php echo $cache_mtime; ?></td>
                <td class="dataTableContent">
                  <?php echo '<a href="' . tep_href_link('cache.php', 'action=reset&block=' . $cache_blocks[$i]['code']) . '">' . tep_image('images/icon_reset.gif', 'Reset', 13, 13) . '</a>'; ?>&nbsp;
                </td>
              </tr>
              <?php
    }
  }
?>
              <tr>
                <td class="smallText" colspan="3"><?php echo TEXT_CACHE_DIRECTORY . ' ' . DIR_FS_CACHE; ?></td>
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