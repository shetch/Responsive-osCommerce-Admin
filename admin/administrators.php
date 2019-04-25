<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2010 osCommerce
  Released under the GNU General Public License
*/
  require('includes/application_top.php');
  $htaccess_array = null;
  $htpasswd_array = null;
  $is_iis = stripos($_SERVER['SERVER_SOFTWARE'], 'iis');
  $authuserfile_array = array('##### OSCOMMERCE ADMIN PROTECTION - BEGIN #####',
                              'AuthType Basic',
                              'AuthName "osCommerce Online Merchant Administration Tool"',
                              'AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce',
                              'Require valid-user',
                              '##### OSCOMMERCE ADMIN PROTECTION - END #####');
  if (!$is_iis && file_exists(DIR_FS_ADMIN . '.htpasswd_oscommerce') && tep_is_writable(DIR_FS_ADMIN . '.htpasswd_oscommerce') && file_exists(DIR_FS_ADMIN . '.htaccess') && tep_is_writable(DIR_FS_ADMIN . '.htaccess')) {
    $htaccess_array = array();
    $htpasswd_array = array();
    if (filesize(DIR_FS_ADMIN . '.htaccess') > 0) {
      $fg = fopen(DIR_FS_ADMIN . '.htaccess', 'rb');
      $data = fread($fg, filesize(DIR_FS_ADMIN . '.htaccess'));
      fclose($fg);
      $htaccess_array = explode("\n", $data);
    }
    if (filesize(DIR_FS_ADMIN . '.htpasswd_oscommerce') > 0) {
      $fg = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'rb');
      $data = fread($fg, filesize(DIR_FS_ADMIN . '.htpasswd_oscommerce'));
      fclose($fg);
      $htpasswd_array = explode("\n", $data);
    }
  }
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
        require('includes/functions/password_funcs.php');
        $username = tep_db_prepare_input($_POST['username']);
        $password = tep_db_prepare_input($_POST['password']);
        $check_query = tep_db_query("select id from " . TABLE_ADMINISTRATORS . " where user_name = '" . tep_db_input($username) . "' limit 1");
        if (tep_db_num_rows($check_query) < 1) {
          tep_db_query("insert into " . TABLE_ADMINISTRATORS . " (user_name, user_password) values ('" . tep_db_input($username) . "', '" . tep_db_input(tep_encrypt_password($password)) . "')");
          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);
              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }
            if (isset($_POST['htaccess']) && ($_POST['htaccess'] == 'true')) {
              $htpasswd_array[] = $username . ':' . tep_crypt_apr_md5($password);
            }
            $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
            fwrite($fp, implode("\n", $htpasswd_array));
            fclose($fp);
            if (!in_array('AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce', $htaccess_array) && !empty($htpasswd_array)) {
              array_splice($htaccess_array, sizeof($htaccess_array), 0, $authuserfile_array);
            } elseif (empty($htpasswd_array)) {
              for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
                if (in_array($htaccess_array[$i], $authuserfile_array)) {
                  unset($htaccess_array[$i]);
                }
              }
            }
            $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
            fwrite($fp, implode("\n", $htaccess_array));
            fclose($fp);
          }
        } else {
          $messageStack->add_session(ERROR_ADMINISTRATOR_EXISTS, 'error');
        }
        tep_redirect(tep_href_link('administrators.php'));
        break;
      case 'save':
        require('includes/functions/password_funcs.php');
        $username = tep_db_prepare_input($_POST['username']);
        $password = tep_db_prepare_input($_POST['password']);
        $check_query = tep_db_query("select id, user_name from " . TABLE_ADMINISTRATORS . " where id = '" . (int)$_GET['aID'] . "'");
        $check = tep_db_fetch_array($check_query);
// update username in current session if changed
        if ( ($check['id'] == $admin['id']) && ($check['user_name'] != $admin['username']) ) {
          $admin['username'] = $username;
        }
// update username in htpasswd if changed
        if (is_array($htpasswd_array)) {
          for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
            list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);
            if ( ($check['user_name'] == $ht_username) && ($check['user_name'] != $username) ) {
              $htpasswd_array[$i] = $username . ':' . $ht_password;
            }
          }
        }
        tep_db_query("update " . TABLE_ADMINISTRATORS . " set user_name = '" . tep_db_input($username) . "' where id = '" . (int)$_GET['aID'] . "'");
        if (tep_not_null($password)) {
// update password in htpasswd
          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);
              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }
            if (isset($_POST['htaccess']) && ($_POST['htaccess'] == 'true')) {
              $htpasswd_array[] = $username . ':' . tep_crypt_apr_md5($password);
            }
          }
          tep_db_query("update " . TABLE_ADMINISTRATORS . " set user_password = '" . tep_db_input(tep_encrypt_password($password)) . "' where id = '" . (int)$_GET['aID'] . "'");
        } elseif (!isset($_POST['htaccess']) || ($_POST['htaccess'] != 'true')) {
          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);
              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }
          }
        }
// write new htpasswd file
        if (is_array($htpasswd_array)) {
          $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
          fwrite($fp, implode("\n", $htpasswd_array));
          fclose($fp);
          if (!in_array('AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce', $htaccess_array) && !empty($htpasswd_array)) {
            array_splice($htaccess_array, sizeof($htaccess_array), 0, $authuserfile_array);
          } elseif (empty($htpasswd_array)) {
            for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
              if (in_array($htaccess_array[$i], $authuserfile_array)) {
                unset($htaccess_array[$i]);
              }
            }
          }
          $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
          fwrite($fp, implode("\n", $htaccess_array));
          fclose($fp);
        }
        tep_redirect(tep_href_link('administrators.php', 'aID=' . (int)$_GET['aID']));
        break;
      case 'deleteconfirm':
        $id = tep_db_prepare_input($_GET['aID']);
        $check_query = tep_db_query("select id, user_name from " . TABLE_ADMINISTRATORS . " where id = '" . (int)$id . "'");
        $check = tep_db_fetch_array($check_query);
        if ($admin['id'] == $check['id']) {
          tep_session_unregister('admin');
        }
        tep_db_query("delete from " . TABLE_ADMINISTRATORS . " where id = '" . (int)$id . "'");
        if (is_array($htpasswd_array)) {
          for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
            list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);
            if ($ht_username == $check['user_name']) {
              unset($htpasswd_array[$i]);
            }
          }
          $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
          fwrite($fp, implode("\n", $htpasswd_array));
          fclose($fp);
          if (empty($htpasswd_array)) {
            for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
              if (in_array($htaccess_array[$i], $authuserfile_array)) {
                unset($htaccess_array[$i]);
              }
            }
            $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
            fwrite($fp, implode("\n", $htaccess_array));
            fclose($fp);
          }
        }
        tep_redirect(tep_href_link('administrators.php'));
        break;
    }
  }
  $secMessageStack = new messageStack();
  if (is_array($htpasswd_array)) {
    if (empty($htpasswd_array)) {
      $secMessageStack->add(sprintf(HTPASSWD_INFO, implode('<br />', $authuserfile_array)), 'error');
    } else {
      $secMessageStack->add(HTPASSWD_SECURED, 'success');
    }
  } else if (!$is_iis) {
    $secMessageStack->add(HTPASSWD_PERMISSIONS, 'error');
  }
  require('includes/template_top.php');
?>
<div class="row">
  <div class="col-sm-6">
        <div class="card mb-3">
                  <div class="card-header">
                    <h3><i class="fa fa-lock"></i> <?php echo HEADING_TITLE; ?>
                    <span style="float:right"></span></h3>
                  </div>
                  <div class="card-body"> 
                  <div class="table-responsive">
                      <table class="table table-sm table-hover">
                      <thead>
                        <tr>
                          <th scope="col"><?php echo TABLE_HEADING_ADMINISTRATORS; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_HTPASSWD; ?></th>
                          <th scope="col"><?php echo TABLE_HEADING_ACTION; ?></th>
                        </tr>
                      </thead>
                      <tbody>
<?php
  $admins_query = tep_db_query("select id, user_name from " . TABLE_ADMINISTRATORS . " order by user_name");
  while ($admins = tep_db_fetch_array($admins_query)) {
    if ((!isset($_GET['aID']) || (isset($_GET['aID']) && ($_GET['aID'] == $admins['id']))) && !isset($aInfo) && (substr($action, 0, 3) != 'new')) {
      $aInfo = new objectInfo($admins);
    }
    $htpasswd_secured = '<i class="fa fa-close" aria-hidden="true"></i>';
    if ($is_iis) {
      $htpasswd_secured = 'N/A';
    }
    if (is_array($htpasswd_array)) {
      for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
        list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);
        if ($ht_username == $admins['user_name']) {
          $htpasswd_secured = '<i class="fa fa-check" aria-hidden="true"></i>';
          break;
        }
      }
    }
    if ( (isset($aInfo) && is_object($aInfo)) && ($admins['id'] == $aInfo->id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('administrators.php', 'aID=' . $aInfo->id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('administrators.php', 'aID=' . $admins['id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $admins['user_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $htpasswd_secured; ?></td>
                <td class="dataTableContent" align="right"><?php if ( (isset($aInfo) && is_object($aInfo)) && ($admins['id'] == $aInfo->id) ) { echo '<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>'; } else { echo '<a href="' . tep_href_link('administrators.php', 'aID=' . $admins['id']) . '">' . '<i class="fa fa-info-circle" aria-hidden="true"></i>' . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              </tbody>
            </table>
            <table class="table">
              <tr>
                <td class="smallText" align="right"><?php echo tep_draw_button(IMAGE_INSERT, 'plus', tep_href_link('administrators.php', 'action=new')); ?></td>
              </tr>
              </tbody>
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
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_ADMINISTRATOR . '</strong>');
      $contents = array('form' => tep_draw_form('administrator', 'administrators.php', 'action=insert', 'post', 'autocomplete="off"'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_USERNAME . '<br />' . tep_draw_input_field('username'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_PASSWORD . '<br />' . tep_draw_password_field('password'));
      if (is_array($htpasswd_array)) {
        $contents[] = array('text' => '<br />' . tep_draw_bs_checkbox_field('htaccess', 'true', '','',TEXT_INFO_PROTECT_WITH_HTPASSWD,''));
      }
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('administrators.php')));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . $aInfo->user_name . '</strong>');
      $contents = array('form' => tep_draw_form('administrator', 'administrators.php', 'aID=' . $aInfo->id . '&action=save', 'post', 'autocomplete="off"'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_USERNAME . '<br />' . tep_draw_input_field('username', $aInfo->user_name));
      $contents[] = array('text' => '<br />' . TEXT_INFO_NEW_PASSWORD . '<br />' . tep_draw_password_field('password'));
      if (is_array($htpasswd_array)) {
        $default_flag = false;
        for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
          list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);
          if ($ht_username == $aInfo->user_name) {
            $default_flag = true;
            break;
          }
        }
        $contents[] = array('text' => '<br />' . tep_draw_bs_checkbox_field('htaccess', 'true', $default_flag, '', TEXT_INFO_PROTECT_WITH_HTPASSWD, ''));
      }
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('administrators.php', 'aID=' . $aInfo->id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . $aInfo->user_name . '</strong>');
      $contents = array('form' => tep_draw_form('administrator', 'administrators.php', 'aID=' . $aInfo->id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $aInfo->user_name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(IMAGE_DELETE, 'trash', null, 'primary', null, 'secondary') . ' ' . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link('administrators.php', 'aID=' . $aInfo->id)));
      break;
    default:
      if (isset($aInfo) && is_object($aInfo)) {
        $heading[] = array('text' => '<strong>' . $aInfo->user_name . '</strong>');
        $contents[] = array('align' => 'center', 'text' => tep_draw_button(IMAGE_EDIT, 'document', tep_href_link('administrators.php', 'aID=' . $aInfo->id . '&action=edit'), null, null, 'secondary') . ' ' . tep_draw_button(IMAGE_DELETE, 'trash', tep_href_link('administrators.php', 'aID=' . $aInfo->id . '&action=delete')));
      }
      break;
  }
?>
<?php
  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
?>
    <div class="col-sm-6">
    <div class="card mb-3">
        <div class="card-header">
          <h3><?php echo $heading[0]['text']; ?></h3>
        </div>
        <div class="card-body">  
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
<div class="row">
  <div class="col-sm-12">
        <div class="card mb-3">
                  <div class="card-header">
                    <h3><i class="fa fa-lock"></i> Additional Protection
                    <span style="float:right"></span></h3>
                  </div>
                  <div class="card-body card-bg"> 
<?php
  echo $secMessageStack->output();
?>
                  </div><!-- end card-body-->															
        </div><!-- end card-->	
  </div><!-- end col-->
</div>
<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
