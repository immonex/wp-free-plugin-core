<?php
/**
 * This file contains the rendering code for the plugin options page (if any).
 *
 * @package immonex-wp-free-plugin-core
 */

$iwpfpc_options_logo = $this->plugin_dir . '/assets/options-logo.png';

if ( file_exists( $iwpfpc_options_logo ) ) {
	$iwpfpc_options_logo_url = plugins_url(
		'/assets/options-logo.png',
		$this->plugin_infos['plugin_main_file']
	);
	$iwpfpc_options_logo_alt = $this->plugin_infos['name'];
	$iwpfpc_is_custom_logo   = true;
} else {
	$iwpfpc_options_logo_url = plugins_url(
		'/vendor/immonex/wp-free-plugin-core/assets/immonex-os-logo-small.png',
		$this->plugin_infos['plugin_main_file']
	);
	$iwpfpc_options_logo_alt = 'Logo: immonex Open Source Software';
	$iwpfpc_is_custom_logo   = false;
}

$iwpfpc_immonex_os_logo_url = plugins_url(
	'/vendor/immonex/wp-free-plugin-core/assets/immonex-os-logo-tiny.png',
	$this->plugin_infos['plugin_main_file']
);

$iwpfpc_inveris_oss_logo_url = plugins_url(
	'/vendor/immonex/wp-free-plugin-core/assets/inveris-oss-logo-tiny.png',
	$this->plugin_infos['plugin_main_file']
);
?>
<style>
	.inveris-free-plugin-options .tab-footer-info {
		padding: 8px;
		color: #A0A0A0;
		font-size: 12px;
		text-align: right;
	}

	.inveris-free-plugin-options .options-logo {
		position: absolute;
		top: 8px;
		right: 20px;
	}

	.inveris-free-plugin-options h1.options-hl {
		margin-bottom:48px
	}

	.inveris-free-plugin-options .developer-logos a {
		margin-bottom: 16px;
		margin-right: 24px;
	}

	@media screen and (min-width: 800px) {
		.inveris-free-plugin-options .options-page-footer {
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.inveris-free-plugin-options .developer-logos a {
			margin: 0 0 0 24x;
		}
	}
</style>

<div id="main" class="wrap inveris-free-plugin-options">
	<?php echo isset( $this->plugin_infos['logo_link_url'] ) ? '<a href="' . $this->plugin_infos['logo_link_url'] . '" target="_blank">' : ''; ?>
	<img src="<?php echo esc_url( $iwpfpc_options_logo_url ); ?>" alt="<?php echo $iwpfpc_options_logo_alt; ?>" class="options-logo">
	<?php echo isset( $this->plugin_infos['logo_link_url'] ) ? '</a>' : ''; ?>

	<?php echo isset( $this->plugin_infos['name'] ) ? '<h1 class="options-hl">' . $this->plugin_infos['name'] . '</h1>' : ''; ?>

	<?php $this->display_tab_nav(); ?>

	<?php
	if (
		isset( $this->option_page_tabs[ $this->current_tab ] ) &&
		trim( $this->option_page_tabs[ $this->current_tab ]['content'] )
	) :
		echo $this->option_page_tabs[ $this->current_tab ]['content'];
	else :
		?>
	<form method="post" action="options.php" style="clear:both">
		<div class="inveris-free-plugin-options-inside">
			<?php settings_fields( isset( $this->option_page_tabs[ $this->current_tab ]['attributes']['plugin_slug'] ) ? $this->option_page_tabs[ $this->current_tab ]['attributes']['plugin_slug'] . '_options' : $this->plugin_slug . '_options' ); ?>
			<?php do_settings_sections( $section_page ); ?>
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
			if ( count( $this->plugin_infos['footer'] ) > 0 ) {
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
			<?php if ( $iwpfpc_is_custom_logo ) : ?>
			<a href="https://immonex.dev/" target="_blank"><img src="<?php echo esc_url( $iwpfpc_immonex_os_logo_url ); ?>" alt="Logo: immonex Open Source Software"></a>
			<?php endif; ?>
			<a href="https://inveris.de/" target="_blank"><img src="<?php echo esc_url( $iwpfpc_inveris_oss_logo_url ); ?>" alt="Logo: inveris Open Source Software"></a>
		</div>
	</div>
</div>
