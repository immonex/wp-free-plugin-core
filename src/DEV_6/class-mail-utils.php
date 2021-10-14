<?php
/**
 * Class Mail_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\DEV_6;

/**
 * Mail utility methods.
 */
class Mail_Utils {

	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * String utils instance
	 *
	 * @var String_Utils
	 */
	private $string_utils;

	/**
	 * Template utils instance
	 *
	 * @var Template_Utils
	 */
	private $template_utils;

	/**
	 * Current (temporary) HTML mail body
	 *
	 * @var string
	 */
	private $current_html_body = '';

	/**
	 * Constructor
	 *
	 * @since 1.4.0
	 *
	 * @param string         $plugin_slug    Slug of the initiating plugin.
	 * @param String_Utils   $string_utils   String utils instance.
	 * @param Template_Utils $template_utils Template utils instance.
	 */
	public function __construct( $plugin_slug, $string_utils, $template_utils ) {
		$this->plugin_slug    = $plugin_slug;
		$this->string_utils   = $string_utils;
		$this->template_utils = $template_utils;
	} // __construct

	/**
	 * Send a mail as plain text or HTML/text (proxy function for wp_mail).
	 *
	 * @since 1.4.0
	 *
	 * @param string|string[] $to            Recipient(s).
	 * @param string          $subject       Subject.
	 * @param string|string[] $body          Mail body (plain text only or HTML and text).
	 * @param string[]        $headers       Headers.
	 * @param string[]        $attachments   Attachment files (absolute paths).
	 * @param mixed[]         $template_data Data/Parameters for rendering the default HTML frame template.
	 *
	 * @return bool True on successful mail processing.
	 */
	public function send( $to, $subject, $body, $headers = array(), $attachments = array(), $template_data = array() ) {
		if ( is_array( $body ) ) {
			$body_txt  = ! empty( $body['txt'] ) ? $body['txt'] : false;
			$body_html = ! empty( $body['html'] ) ? $body['html'] : false;
		} else {
			$body_txt = $body;
		}

		if ( ! $body_txt && $body_html ) {
			$body_txt = $this->string_utils->html_to_plain_text( $body_html );
		}

		if ( ! $body_txt && ! $body_html ) {
			return false;
		}

		if ( $body_html ) {
			$html_mail_template_file = apply_filters(
				// @codingStandardsIgnoreLine
				$this->plugin_slug . '_html_mail_twig_template_file',
				__DIR__ . '/templates/html-mail.twig'
			);

			$template_data           = $this->get_html_mail_template_data( $template_data, $body_html );
			$this->current_html_body = $this->template_utils->render_twig_template( $html_mail_template_file, $template_data );

			add_action( 'phpmailer_init', array( $this, 'phpmailer_set_alt_body' ) );
			$mail_result = wp_mail( $to, $subject, $body_txt, $headers, $attachments );
			remove_action( 'phpmailer_init', array( $this, 'phpmailer_set_alt_body' ) );

			global $phpmailer;

			if (
				$phpmailer instanceof \PHPMailer\PHPMailer\PHPMailer
				&& $phpmailer->alternativeExists()
			) {
				// @codingStandardsIgnoreLine
				$phpmailer->AltBody = '';
			}
		} else {
			$mail_result = wp_mail( $to, $subject, $body_txt, $headers, $attachments );
		}

		return $mail_result;
	} // send

	/**
	 * Possibly replace the existing PHPMailer mail body by its
	 * HTML version and add the plain text contents as alternative
	 * body (callback).
	 *
	 * @since 1.4.0
	 *
	 * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance.
	 */
	public function phpmailer_set_alt_body( $phpmailer ) {
		if ( ! $this->current_html_body ) {
			return;
		}

		// @codingStandardsIgnoreLine
		$phpmailer->AltBody      = $phpmailer->Body;
		// @codingStandardsIgnoreLine
		$phpmailer->Body         = $this->current_html_body;
		$this->current_html_body = '';
	} // phpmailer_set_alt_body

	/**
	 * Compile the data/parameters for rendering the default HTML mail
	 * frame template (Twig 3).
	 *
	 * @since 1.4.0
	 *
	 * @param mixed[] $org_data Custom params to override the defaults.
	 * @param string  $body     Main mail body content.
	 *
	 * return mixed[] Complete set of template contents/params.
	 */
	private function get_html_mail_template_data( $org_data, $body ) {
		$defaults = array(
			'title'         => get_bloginfo( 'name' ),
			'body'          => '',
			'header'        => '',
			'header_text'   => '',
			'footer'        => '',
			'footer_text'   => '',
			'logo'          => '',
			'logo_alt'      => 'Logo',
			'logo_link_url' => '',
			'layout'        => array(
				'align'            => 'center',
				'max_width'        => '640px',
				'margin_top'       => '24px',
				'border_radius'    => '8px',
				'padding_px'       => '14',
				'logo_position'    => 'footer_right',
				'max_logo_width'   => '160px',
				'max_logo_height'  => '80px',
				'header_text_size' => '16px',
				'header_text_size' => '16px',
				'footer_text_size' => '14px',
			),
			'colors'        => array(
				'text'        => '#303030',
				'text_header' => '#707070',
				'text_footer' => '#707070',
				'bg'          => '#EEE',
				'bg_header'   => '#FFF',
				'bg_body'     => '#FFF',
				'bg_footer'   => '#FFF',
			),
		);

		if (
			! empty( $org_data['preset'] )
			&& 'admin_info' === $org_data['preset']
		) {
			$defaults['logo']                    = 'immonex-wp-logo-tiny.png';
			$defaults['logo_alt']                = 'immonex® | ' . __( 'Real Estate Solutions for WordPress', 'immonex-wp-free-plugin-core' );
			$defaults['layout']['logo_position'] = 'footer_right';
		}

		if ( ! empty( $org_data['layout'] ) ) {
			$org_data['layout'] = array_merge( $defaults['layout'], $org_data['layout'] );
		}

		if ( ! empty( $org_data['colors'] ) ) {
			$org_data['layout'] = array_merge( $defaults['colors'], $org_data['colors'] );
		}

		$data = apply_filters(
			// @codingStandardsIgnoreLine
			$this->plugin_slug . '_html_mail_template_data',
			array_merge( $defaults, $org_data )
		);

		$data['body'] = $body;

		if (
			! empty( $data['logo'] )
			&& 'http' !== strtolower( substr( $data['logo'], 0, 4 ) )
		) {
			$local_logo_file = dirname( dirname( __DIR__ ) ) . "/assets/{$data['logo']}";

			if ( file_exists( $local_logo_file ) ) {
				$data['logo'] = plugins_url( $this->plugin_slug . "/vendor/immonex/wp-free-plugin-core/assets/{$data['logo']}" );
			} else {
				$data['logo'] = '';
			}
		}

		return $data;
	} // get_html_mail_template_data

} // Mail_Utils
