<?php

defined( 'ABSPATH' ) || exit;

use FieldsBox\Core\Application;

$app = new Application();

?>

<div class="fieldsbox-group">
	<?php echo $app->render( $field->build_field_args() ); ?>
</div>
