<?php
/**
 * This file contains the rendering code for the default plugin options
 * page header.
 *
 * @package immonex\WordPressFreePluginCore
 */

// @codingStandardsIgnoreLine
$iwpfpc_plugin_infos   = apply_filters( "{$this->plugin_slug}_plugin_infos", array() );
$iwpfpc_options_logo   = $this->plugin_dir . '/assets/options-logo.png';
$iwpfpc_is_custom_logo = false;

if ( file_exists( $iwpfpc_options_logo ) ) {
	$iwpfpc_options_logo_url = plugins_url(
		'/assets/options-logo.png',
		$iwpfpc_plugin_infos['plugin_main_file']
	);
	$iwpfpc_options_logo_alt = $iwpfpc_plugin_infos['name'];
	$iwpfpc_is_custom_logo   = true;
} elseif ( $iwpfpc_plugin_infos['has_free_license'] ) {
	$iwpfpc_options_logo_url = plugins_url(
		'/vendor/immonex/wp-free-plugin-core/assets/immonex-os-logo-small.png',
		$iwpfpc_plugin_infos['plugin_main_file']
	);
	$iwpfpc_options_logo_alt = 'Logo: immonex Open Source Software';
} else {
	$iwpfpc_options_logo_url = plugins_url(
		'/vendor/immonex/wp-free-plugin-core/assets/immonex-wp-logo-small.png',
		$iwpfpc_plugin_infos['plugin_main_file']
	);
	$iwpfpc_options_logo_alt = 'Logo: immonex Solutions for WordPress';
}

$iwpfpc_has_tabbed_sections = ! empty( $this->option_page_tabs[ $this->current_tab ]['attributes']['tabbed_sections'] );

$iwpfpc_current_screen = get_current_screen();
if ( ! empty( $iwpfpc_current_screen->parent_base ) && 'options-general' !== $iwpfpc_current_screen->parent_base ) {
	settings_errors();
}
?>
<div id="main" class="wrap immonex-plugin-options immonex-plugin-core-<?php echo strtolower( str_replace( '.', '-', $iwpfpc_plugin_infos['core_version'] ) ); ?>">
	<?php echo isset( $iwpfpc_plugin_infos['logo_link_url'] ) ? '<a href="' . $iwpfpc_plugin_infos['logo_link_url'] . '" target="_blank">' : ''; ?>
	<img src="<?php echo esc_url( $iwpfpc_options_logo_url ); ?>" alt="<?php echo $iwpfpc_options_logo_alt; ?>" class="options-logo">
	<?php echo isset( $iwpfpc_plugin_infos['logo_link_url'] ) ? '</a>' : ''; ?>

	<?php echo isset( $iwpfpc_plugin_infos['name'] ) ? '<h1 class="options-hl">' . $iwpfpc_plugin_infos['name'] . '</h1>' : ''; ?>

	<?php if ( isset( $iwpfpc_plugin_infos['debug_level'] ) && $iwpfpc_plugin_infos['debug_level'] ) : ?>
	<div class="debug-mode-info">DEBUG MODE <?php echo '(' . $iwpfpc_plugin_infos['debug_level'] . ')'; ?></div>
	<?php endif; ?>

	<?php
	// @codingStandardsIgnoreLine
	do_action( $this->plugin_slug . '_option_page_extended_infos' );
	?>

	<?php if ( ! empty( $iwpfpc_plugin_infos['special_info'] ) ) : ?>
	<div class="special-info">
		<div>
			<div class="immonex-plugin-options-icon immonex-plugin-options-icon-info"></div>
		</div>
		<div>
			<?php echo $iwpfpc_plugin_infos['special_info']; ?>
		</div>
	</div>
	<?php endif; ?>
