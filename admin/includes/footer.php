<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2014 osCommerce
  Released under the GNU General Public License
*/
?>
    </div>
  </div>
</div>
<?php
  if (tep_session_is_registered('admin')) {
?>	
<footer class="footer" style="position:fixed;bottom:0;">
		<span class="text-right">
		osCommerce Online Merchant Copyright &copy; 2000-<?php echo date('Y'); ?>
		</span>
		<span class="float-right">
		Powered by <a href="http://www.oscommerce.com" target="_blank">osCommerce</a>
		</span>
  </footer>
  <?php
  }
?>	