<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2010 osCommerce
  Released under the GNU General Public License
*/
?>
<!-- -->
<?php require('includes/footer.php'); ?>
</div><!-- -->
<script src="assets/js/modernizr.min.js"></script>

<script src="assets/js/moment.min.js"></script>
<script src="assets/js/popper.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/detect.js"></script>
<script src="assets/js/fastclick.js"></script>
<script src="assets/js/jquery.blockUI.js"></script>
<script src="assets/js/jquery.nicescroll.js"></script>
<script src="assets/js/shetchadmin.js"></script>
  
<script src="assets/plugins/waypoints/lib/jquery.waypoints.min.js"></script>
<script src="assets/plugins/counterup/jquery.counterup.min.js"></script>	
 
<script>
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})
	$(document).ready(function() {
		// counter-up
		$('.counter').counterUp({
			delay: 10,
			time: 600
		});
	} );		
</script>
</body>
</html>