<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Application;

$app = new Application();

?>

<div class="aenimfields-group">
	<?php echo $app->render( $field->build_field_args() ); ?>
</div>
