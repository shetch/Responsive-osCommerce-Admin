<div id="main">
	<div class="headerbar">
        <div class="headerbar-left">
            <a href="<?php echo tep_href_link('index.php'); ?>" class="logo"><img alt="Logo" src="assets/images/oscommerce.png" /></a>
        </div>
        <nav class="navbar-custom">
                    <ul class="list-inline float-right mb-0">
                        <?php
                        if (tep_session_is_registered('admin')) {
                        ?>						
						<li data-toggle="tooltip" data-placement="bottom" title="<?php echo HEADER_TITLE_ONLINE_CATALOG; ?>" class="list-inline-item dropdown notif">
                            <a target="_blank" class="nav-link arrow-none" href="<?php echo tep_catalog_href_link(); ?>">
                                <i class="fa fa-fw fa-shopping-cart"></i>
                            </a>
                        </li>
						<li data-toggle="tooltip" data-placement="bottom" title="Support Site" class="list-inline-item dropdown notif">
                            <a target="_blank" class="nav-link arrow-none" href="https://forums.oscommerce.com/">
                                <i class="fa fa-fw fa-question-circle"></i>
                            </a>
                        </li>
                        <li class="list-inline-item dropdown notif">
                            <a class="nav-link dropdown-toggle nav-user" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <i class="fa fa-fw fa-user-o"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                                <div class="dropdown-item noti-title">
                                    <h5 class="text-overflow"><small>Hello, <?php echo $admin['username']; ?></small> </h5>
                                </div>
                                <a target="_blank" href="<?php echo tep_catalog_href_link(); ?>" class="dropdown-item notify-item">
                                    <i class="fa fa-shopping-cart"></i> <span><?php echo HEADER_TITLE_ONLINE_CATALOG; ?></span>
                                </a>
                                <a target="_blank" href="https://apps.oscommerce.com/" class="dropdown-item notify-item">
                                    <i class="fa fa-external-link"></i> <span>Marketplace</span>
                                </a>
                                <a target="_blank" href="http://www.oscommerce.com" class="dropdown-item notify-item">
                                    <i class="fa fa-external-link"></i> <span><?php echo HEADER_TITLE_SUPPORT_SITE; ?></span>
                                </a>
                                <a href="<?php echo tep_href_link('login.php', 'action=logoff'); ?>" class="dropdown-item notify-item">
                                    <i class="fa fa-power-off"></i> <span>Logout</span>
                                </a>
                            </div>
                        </li>
                    </ul>
                    <ul class="list-inline menu-left mb-0">
                        <li class="float-left">
                            <button class="button-menu-mobile open-left">
								<i class="fa fa-fw fa-bars"></i>
                            </button>
                        </li>                        
                    </ul>
<?php
  }
?>
        </nav>
	</div>
    <?php
  if (!tep_session_is_registered('admin')) {
    ?>
<?php
  } else {
?>
	<div class="left main-sidebar">
		<div class="sidebar-inner leftscroll">
			<div id="sidebar-menu">
			<ul>
					<li class="submenu">
						<a class="active" href="<?php echo tep_href_link('index.php'); ?>"><i class="fa fa-fw fa-tachometer"></i><span> Dashboard </span> </a>
                    </li>
                    <?php
                        if (tep_session_is_registered('admin')) {
                            include('includes/column_left.php');
                        }   
                    ?>
            </ul>
            <div class="clearfix"></div>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
<?php
  }
?>
  <div class="content-page">
    <div class="content">
<div class="container-fluid">
    <div class="row">
            <div class="col-xl-12" style="margin-top:20px;">
            <?php
            if ($messageStack->size > 0) {
            echo '<div class="alert alert-info alert-dismissible fade show" role="alert">'.$messageStack->output().'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>';
            }
            ?>
            </div>
    </div>