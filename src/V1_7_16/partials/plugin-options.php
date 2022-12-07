<?php
/**
 * This file contains the rendering code for the plugin options page (if any).
 *
 * @package immonex\WordPressFreePluginCore
 */

// @codingStandardsIgnoreLine
$iwpfpc_plugin_infos        = apply_filters( "{$this->plugin_slug}_plugin_infos", array() );
$iwpfpc_has_tabbed_sections = ! empty( $this->option_page_tabs[ $this->current_tab ]['attributes']['tabbed_sections'] );

// @codingStandardsIgnoreLine
do_action( "{$iwpfpc_plugin_infos['prefix']}render_option_page_header" );

$this->display_tab_nav();

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

// @codingStandardsIgnoreLine
do_action( "{$iwpfpc_plugin_infos['prefix']}render_option_page_footer" );
