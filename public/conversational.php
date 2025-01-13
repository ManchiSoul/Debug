<!DOCTYPE html>
<html <?php language_attributes(); ?>>

	<head>

		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
<?php

	wp_head();
?>
	</head>

	<body>
<?php

	the_post();

	the_content();

	wp_footer();
?>
	</body>

</html>
