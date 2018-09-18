<?php include("includes/public_header.php"); ?>

<div id="content">
	<h2>All Products</h2>
	
	<ul class="alerts">
		<?php $this->get_alerts(); ?>
	</ul>
	
	<p><?php $this->get_data('header'); ?></p>
	
	<ul class="products">
		<?php $this->get_data('products'); ?>
	</ul>
</div>

<?php include("includes/public_footer.php"); ?>