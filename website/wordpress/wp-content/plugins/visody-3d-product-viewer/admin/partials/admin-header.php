<?php
/**
 * Visody plugin admin header links
 * 
 * @link       https://visody.com/
 * @since      1.0.0
 *
 * @package    Visody
 * @subpackage Visody/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$actions = array(
	'viewer' => array(
		'url' => esc_url( admin_url('edit.php?post_type=visody_viewer') ),
		'type' => 'visody_viewer',
		'page' => '',
		'text' =>  __('Viewers', 'visody')
	),
	'template' => array(
		'url' => esc_url( admin_url('edit.php?post_type=visody_template') ),
		'type' => 'visody_template',
		'page' => '',
		'text' =>  __('Templates', 'visody')
	),
	'notes' => array(
		'url' => esc_url( admin_url('edit.php?post_type=visody_viewer_note') ),
		'type' => 'visody_viewer_note',
		'page' => '',
		'text' =>  __('Annotations', 'visody')
	),
	'options' => array(
		'url' => esc_url( admin_url('edit.php?post_type=visody_viewer&page=visody_options') ),
		'type' => 'visody_viewer',
		'page' => 'visody_options',
		'text' =>  __('Appearance', 'visody')
	)
);

if (!visody_fs()->is_plan_or_trial('pro')) {
	unset( $actions['notes'] );

	$actions['pricing'] = array(
		'url' => esc_url( admin_url('edit.php?post_type=visody_viewer&page=visody-pricing') ),
		'type' => 'visody_viewer',
		'page' => 'visody-pricing',
		'text' =>  __('Go PRO', 'visody')
	);
}
?>
<div class="visody-admin-header">
	<a href="<?php echo esc_url( admin_url('edit.php?post_type=visody_viewer&page=welcome') ); ?>" class="visody-admin-logo">
		<img src="<?php echo esc_url( plugin_dir_url(__FILE__) ) . '../img/visody-icon.png'; ?>" height="36" width="36" alt="<?php echo $this->plugin_name; ?>">
		<span>Visody <?php echo wp_kses_post((visody_fs()->is_plan_or_trial('pro')) ? '<small>PRO</small>' : ''); ?></span>
	</a>
	<ul class="visody-admin-actions">
		<?php
		foreach ($actions as $key => $action) {
			$class = '';
			if ((isset($_GET['page']) && isset($_GET['post_type']) && $action['page'] == $_GET['page'] && $action['type'] == $_GET['post_type'])
				|| (!isset($_GET['page']) && !$action['page'] && isset($_GET['post_type']) && $action['type'] == $_GET['post_type'])
			) {
				$class = 'active';
			}

			if ('visody-pricing' == $action['page']) {
				$class .= ' buy-button';
			}
		?>
			<li>
				<a href="<?php echo esc_url( $action['url'] ); ?>" class="<?php echo esc_html( $class ); ?>"><?php echo esc_html( $action['text'] ); ?></a>
			</li>
		<?php
		}
		?>
	</ul>
	<div class="visody-version">
		v<?php echo esc_html( $this->version ); ?>
	</div>
</div>