<?php
	//include our library and start drawing the page
	require_once("../../php_include/functions.php");
	$page_name = "intermediate";
	print_header($page_name);
	print_navbar();
?>

<div class="container">
	<div class="row">
		<div class="col-md-12">
			<h1>Intermediate</h1>
		</div>	
	</div>	
<?php 
	print_footnote();
?>

</div><!--container-->

<?php 
	//print the footer	
	print_footer();
?>