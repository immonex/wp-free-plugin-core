<?php
/**
 * This file contains the rendering code for the default plugin options
 * page footer.
 *
 * @package immonex\WordPressFreePluginCore
 */

$iwpfpc_is_custom_logo = file_exists( $this->plugin_dir . '/assets/options-logo.png' );

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
</div><!-- #main -->
