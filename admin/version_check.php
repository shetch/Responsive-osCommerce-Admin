<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $current_version = tep_get_version();
  $major_version = (int)substr($current_version, 0, 1);

  $releases = null;
  $new_versions = array();
  $check_message = array();

  if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://www.oscommerce.com/version/online_merchant/' . $major_version);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = trim(curl_exec($ch));
    curl_close($ch);

    if (!empty($response)) {
      $releases = explode("\n", $response);
    }
  } else {
    if ($fp = @fsockopen('www.oscommerce.com', 80, $errno, $errstr, 30)) {
      $header = 'GET /version/online_merchant/' . $major_version . ' HTTP/1.0' . "\r\n" .
                'Host: www.oscommerce.com' . "\r\n" .
                'Connection: close' . "\r\n\r\n";

      fwrite($fp, $header);

      $response = '';
      while (!feof($fp)) {
        $response .= fgets($fp, 1024);
      }

      fclose($fp);

      $response = explode("\r\n\r\n", $response); // split header and content

      if (isset($response[1]) && !empty($response[1])) {
        $releases = explode("\n", trim($response[1]));
      }
    }
  }

  if (is_array($releases) && !empty($releases)) {
    $serialized = serialize($releases);
    if ($f = @fopen(DIR_FS_CACHE . 'oscommerce_version_check.cache', 'w')) {
      fwrite ($f, $serialized, strlen($serialized));
      fclose($f);
    }

    foreach ($releases as $version) {
      $version_array = explode('|', $version);

      if (version_compare($current_version, $version_array[0], '<')) {
        $new_versions[] = $version_array;
      }
    }

    if (!empty($new_versions)) {
      $check_message = array('class' => 'secWarning',
                             'message' => sprintf(VERSION_UPGRADES_AVAILABLE, $new_versions[0][0]));
    } else {
      $check_message = array('class' => 'secSuccess',
                             'message' => VERSION_RUNNING_LATEST);
    }
  } else {
    $check_message = array('class' => 'secError',
                           'message' => ERROR_COULD_NOT_CONNECT);
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
                      <table class="table">
                     
                      <tbody>   
      <tr>
        <td class="smallText"><?php echo TITLE_INSTALLED_VERSION . ' <strong>osCommerce Online Merchant v' . $current_version . '</strong>'; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><div class="<?php echo $check_message['class']; ?>">
          <p class="smallText"><?php echo $check_message['message']; ?></p>
        </div></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
  if (!empty($new_versions)) {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_VERSION; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_RELEASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>

<?php
    foreach ($new_versions as $version) {
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                <td class="dataTableContent"><?php echo '<a href="' . $version[2] . '" target="_blank">osCommerce Online Merchant v' . $version[0] . '</a>'; ?></td>
                <td class="dataTableContent"><?php echo tep_date_long(substr($version[1], 0, 4) . '-' . substr($version[1], 4, 2) . '-' . substr($version[1], 6, 2)); ?></td>
                <td class="dataTableContent" align="right"><?php echo '<a href="' . $version[2] . '" target="_blank">' . tep_image('images/icon_info.gif', IMAGE_ICON_INFO) . '</a>'; ?>&nbsp;</td>
              </tr>
<?php
    }
?>
            </table></rd>
          </tr>
        </table></td>
      </tr>
<?php
  }
?>
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
