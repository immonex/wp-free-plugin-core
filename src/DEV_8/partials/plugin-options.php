<?php
/**
 * This file contains the rendering code for the plugin options page (if any).
 *
 * @package immonex\WordPressFreePluginCore
 */

$iwpfpc_options_logo   = $this->plugin_dir . '/assets/options-logo.png';
$iwpfpc_is_custom_logo = false;

if ( file_exists( $iwpfpc_options_logo ) ) {
	$iwpfpc_options_logo_url = plugins_url(
		'/assets/options-logo.png',
		$this->plugin_infos['plugin_main_file']
	);
	$iwpfpc_options_logo_alt = $this->plugin_infos['name'];
	$iwpfpc_is_custom_logo   = true;
} elseif ( $this->plugin_infos['has_free_license'] ) {
	$iwpfpc_options_logo_url = plugins_url(
		'/vendor/immonex/wp-free-plugin-core/assets/immonex-os-logo-small.png',
		$this->plugin_infos['plugin_main_file']
	);
	$iwpfpc_options_logo_alt = 'Logo: immonex Open Source Software';
} else {
	$iwpfpc_options_logo_url = plugins_url(
		'/vendor/immonex/wp-free-plugin-core/assets/immonex-wp-logo-small.png',
		$this->plugin_infos['plugin_main_file']
	);
	$iwpfpc_options_logo_alt = 'Logo: immonex Solutions for WordPress';
}

$iwpfpc_immonex_os_logo_url = plugins_url(
	'/vendor/immonex/wp-free-plugin-core/assets/immonex-os-logo-tiny.png',
	$this->plugin_infos['plugin_main_file']
);

$iwpfpc_inveris_oss_logo_url = plugins_url(
	'/vendor/immonex/wp-free-plugin-core/assets/inveris-oss-logo-tiny.png',
	$this->plugin_infos['plugin_main_file']
);

$iwpfpc_inveris_plugin_logo_url = plugins_url(
	'/vendor/immonex/wp-free-plugin-core/assets/inveris-plugin-logo-tiny.png',
	$this->plugin_infos['plugin_main_file']
);

$iwpfpc_has_tabbed_sections = ! empty( $this->option_page_tabs[ $this->current_tab ]['attributes']['tabbed_sections'] );
?>
<div id="main" class="wrap immonex-plugin-options">
	<?php echo isset( $this->plugin_infos['logo_link_url'] ) ? '<a href="' . $this->plugin_infos['logo_link_url'] . '" target="_blank">' : ''; ?>
	<img src="<?php echo esc_url( $iwpfpc_options_logo_url ); ?>" alt="<?php echo $iwpfpc_options_logo_alt; ?>" class="options-logo">
	<?php echo isset( $this->plugin_infos['logo_link_url'] ) ? '</a>' : ''; ?>

	<?php echo isset( $this->plugin_infos['name'] ) ? '<h1 class="options-hl">' . $this->plugin_infos['name'] . '</h1>' : ''; ?>

	<?php if ( isset( $this->plugin_infos['debug_level'] ) && $this->plugin_infos['debug_level'] ) : ?>
	<div class="debug-mode-info">DEBUG MODE <?php echo '(' . $this->plugin_infos['debug_level'] . ')'; ?></div>
	<?php endif; ?>

	<?php if ( ! empty( $this->plugin_infos['special_info'] ) ) : ?>
	<div class="special-info">
		<div>
			<div class="immonex-plugin-options-icon immonex-plugin-options-icon-info"></div>
		</div>
		<div>
			<?php echo $this->plugin_infos['special_info']; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php $this->display_tab_nav(); ?>

	<?php
	if (
		isset( $this->option_page_tabs[ $this->current_tab ] ) &&
		trim( $this->option_page_tabs[ $this->current_tab ]['content'] )
	) :
		echo $this->option_page_tabs[ $this->current_tab ]['content'];
	else :
		$iwpfpc_tab_description       = ! empty( $this->option_page_tabs[ $this->current_tab ]['attributes']['description'] ) ?
			$this->option_page_tabs[ $this->current_tab ]['attributes']['description'] : '';
		$iwpfpc_tab_description_class = 'tab-description';

		if ( $iwpfpc_tab_description ) {
			if ( is_array( $iwpfpc_tab_description ) ) {
				$iwpfpc_tab_description = wp_sprintf(
					'<p>%s</p>',
					implode( '</p>' . PHP_EOL . '<p>', $iwpfpc_tab_description )
				);
			} else {
				$iwpfpc_tab_description = "<p>{$iwpfpc_tab_description}</p>";
			}
			if ( $iwpfpc_has_tabbed_sections ) {
				$iwpfpc_tab_description_class .= ' tab-description-tabbed-sections';
			}
		}
		?>
	<form method="post" action="options.php" style="clear:both">
		<div class="immonex-plugin-options-inside">
			<?php if ( $iwpfpc_tab_description ) : ?>
			<div class="<?php echo $iwpfpc_tab_description_class; ?>">
				<?php echo $iwpfpc_tab_description; ?>
			</div>
			<?php endif; ?>

			<?php settings_fields( isset( $this->option_page_tabs[ $this->current_tab ]['attributes']['plugin_slug'] ) ? $this->option_page_tabs[ $this->current_tab ]['attributes']['plugin_slug'] . '_options' : $this->plugin_slug . '_options' ); ?>
			<?php $this->display_tab_sections( $this->current_tab, $section_page ); ?>
			<?php if ( isset( $this->option_page_tabs[ $this->current_tab ]['attributes']['footer_info'] ) ) : ?>
			<div class="tab-footer-info"><?php echo $this->option_page_tabs[ $this->current_tab ]['attributes']['footer_info']; ?></div>
			<?php endif; ?>
		</div>

			<?php submit_button(); ?>
	</form>
			<?php
	endif;
	?>

	<div class="options-page-footer">
		<div>
			<?php
			if ( ! empty( $this->plugin_infos['footer'] ) ) {
				foreach ( $this->plugin_infos['footer'] as $iwpfpc_i => $iwpfpc_info ) {
					echo $iwpfpc_info;
					if ( $iwpfpc_i < count( $this->plugin_infos['footer'] ) - 1 ) {
						echo ' | ';
					}
				}
			}
			?>
		</div>

		<div class="developer-logos">
			<?php if ( $iwpfpc_is_custom_logo && $this->plugin_infos['has_free_license'] ) : ?>
			<a href="https://immonex.dev/" target="_blank"><img src="<?php echo esc_url( $iwpfpc_immonex_os_logo_url ); ?>" alt="Logo: immonex Open Source Software"></a>
			<?php endif; ?>
			<?php if ( $this->plugin_infos['has_free_license'] ) : ?>
			<a href="https://inveris.de/" target="_blank"><img src="<?php echo esc_url( $iwpfpc_inveris_oss_logo_url ); ?>" alt="Logo: inveris Open Source Software"></a>
			<?php else : ?>
			<a href="https://inveris.de/" target="_blank"><img src="<?php echo esc_url( $iwpfpc_inveris_plugin_logo_url ); ?>" alt="Logo: WordPress Plugins by inveris"></a>
			<?php endif; ?>
		</div>
	</div>
</div>
