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
?>
<style>
	.inveris-free-plugin-options .special-info {
		display: flex;
		align-items: center;
		margin-bottom: 32px;
		padding: 8px;
		color: #FFF;
		background-color: #733578;
		font-size: 16px;
		font-weight: bold;
	}

	.inveris-free-plugin-options-icon {
		margin: 12px;
		border: 2px solid #303030;
		display: inline-block;
		position: relative;
		vertical-align: top;
		box-sizing: content-box;
	}
	.inveris-free-plugin-options-icon:after,
	.inveris-free-plugin-options-icon:before {
		background: #fed;
		border: 2px solid #303030;
		content: '';
		position: absolute;
	}

	.inveris-free-plugin-options-icon-info {
		height: 26px;
		width: 26px;
		border-radius: 100%;
		border-color: #FFF;
	}
	.inveris-free-plugin-options-icon-info:before {
		height: 1px;
		left: 10px;
		top: 5px;
		width: 2px;
		background: #FFF;
		border-color: #FFF;
	}
	.inveris-free-plugin-options-icon-info:after {
		height: 6px;
		left: 10px;
		top: 12px;
		width: 2px;
		background: #FFF;
		border-color: #FFF;
	}

	.inveris-free-plugin-options .special-info a {
		color: #FFF;
	}

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
		z-index: 10;
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

	<?php if ( ! empty( $this->plugin_infos['special_info'] ) ) : ?>
	<div class="special-info">
		<div>
			<div class="inveris-free-plugin-options-icon inveris-free-plugin-options-icon-info"></div>
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
