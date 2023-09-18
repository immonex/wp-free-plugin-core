<?php
/**
 * Class Mail_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V1_8_21;

/**
 * Mail utility methods.
 */
class Mail_Utils {

	/**
	 * Plugin Bootstrap Data
	 *
	 * @var mixed¢[]
	 */
	private $bootstrap_data;

	/**
	 * Plugin Slug
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * String Utils Instance
	 *
	 * @var String_Utils
	 */
	private $string_utils;

	/**
	 * Template Utils Instance
	 *
	 * @var Template_Utils
	 */
	private $template_utils;

	/**
	 * Current (temporary) HTML Mail Body
	 *
	 * @var string
	 */
	private $current_html_body = '';

	/**
	 * Constructor
	 *
	 * @since 1.4.0
	 *
	 * @param mixed[]        $bootstrap_data Plugin bootstrap data.
	 * @param String_Utils   $string_utils   String utils instance.
	 * @param Template_Utils $template_utils Template utils instance.
	 */
	public function __construct( $bootstrap_data, $string_utils, $template_utils ) {
		$this->bootstrap_data = $bootstrap_data;
		$this->plugin_slug    = $bootstrap_data['plugin_slug'];
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
		$empty_mail_data_defaults = array(
			'to'            => array(),
			'subject'       => '',
			'body'          => '',
			'headers'       => array(),
			'attachments'   => array(),
			'template_data' => array(),
		);

		$org_mail_data = array(
			'to'            => $to,
			'subject'       => $subject,
			'body'          => $body,
			'headers'       => $headers,
			'attachments'   => $attachments,
			'template_data' => $template_data,
		);

		$mail_data = array_merge(
			$empty_mail_data_defaults,
			apply_filters( 'immonex_core_mail_data_before_sending', $org_mail_data, $this->plugin_slug )
		);

		if ( is_array( $mail_data['body'] ) ) {
			$body_txt  = ! empty( $mail_data['body']['txt'] ) ? $mail_data['body']['txt'] : false;
			$body_html = ! empty( $mail_data['body']['html'] ) ? $mail_data['body']['html'] : false;
		} else {
			$body_txt  = $mail_data['body'];
			$body_html = false;
		}

		if ( ! $body_txt && $body_html ) {
			$body_txt = $this->string_utils::html_to_plain_text( $body_html, true );
		}

		if ( ! $body_txt && ! $body_html ) {
			return false;
		}

		if (
			isset( $mail_data['template_data']['preset'] )
			&& 'admin_info' === $mail_data['template_data']['preset']
			&& empty( $mail_data['template_data']['preset_flags']['org_subject'] )
			&& (
				! empty( $mail_data['subject'] )
				&& '[' !== $mail_data['subject'][0]
			)
		) {
			$mail_data['subject'] = wp_sprintf(
				'[%s] %s',
				get_bloginfo( 'name' ),
				trim( $mail_data['subject'] )
			);
		}

		if ( $body_html ) {
			$html_mail_template_file = apply_filters(
				// @codingStandardsIgnoreLine
				$this->plugin_slug . '_html_mail_twig_template_file',
				__DIR__ . '/templates/html-mail.twig'
			);

			if ( ! empty( $mail_data['template_data']['header_text'] ) ) {
				$mail_data['template_data']['header_text'] = wpautop( stripslashes( $mail_data['template_data']['header_text'] ) );
			}
			if ( ! empty( $mail_data['template_data']['footer_text'] ) ) {
				$mail_data['template_data']['footer_text'] = wpautop( stripslashes( $mail_data['template_data']['footer_text'] ) );
			}

			$mail_data['template_data'] = $this->get_html_mail_template_data( $mail_data['template_data'], $body_html );
			$this->current_html_body    = $this->template_utils->render_twig_template( $html_mail_template_file, $mail_data['template_data'] );

			$body_txt = $this->maybe_add_plain_text_header( $body_txt, $mail_data['template_data'] );

			if ( ! $this->has_signature( $body_txt ) ) {
				/**
				 * Plain text body obviously does not contain a "signature":
				 * Add the converted footer of the HTML version if existing.
				 */
				$body_txt = $this->maybe_add_plain_text_footer( $body_txt, $mail_data['template_data'] );
			}

			add_filter(
				'wp_mail_charset',
				function ( $charset ) {
					return 'UTF-8';
				}
			);
			add_action( 'phpmailer_init', array( $this, 'phpmailer_set_alt_body' ) );
			$mail_result = wp_mail( $mail_data['to'], $mail_data['subject'], $body_txt, $mail_data['headers'], $mail_data['attachments'] );
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
			if (
				isset( $mail_data['template_data']['preset'] )
				&& 'admin_info' === $mail_data['template_data']['preset']
				&& ! isset( $mail_data['template_data']['footer'] )
				&& ! isset( $mail_data['template_data']['footer_text'] )
				&& ! $this->has_signature( $body_txt )
			) {
				/**
				 * Plain text body obviously does not contain a "signature":
				 * Add a standard footer for admin info mails.
				 */
				$footer_info = wp_sprintf(
					// translators: %1$s = Plugin name, %2$s = Site title, %3$s = Site URL.
					__( 'This message was generated by the plugin %1$s at %2$s (%3$s).', 'immonex-wp-free-plugin-core' ),
					$this->bootstrap_data['plugin_name'],
					get_bloginfo( 'name' ),
					get_home_url()
				);

				$mail_data['template_data']['footer'] = wp_sprintf(
					'%s%s%s',
					$footer_info,
					PHP_EOL . PHP_EOL,
					'immonex® - ' . __( 'Professional Real Estate Solutions for WordPress', 'immonex-wp-free-plugin-core' )
				);
			}

			$body_txt = $this->maybe_add_plain_text_header( $body_txt, $mail_data['template_data'] );
			$body_txt = $this->maybe_add_plain_text_footer( $body_txt, $mail_data['template_data'] );

			$mail_result = wp_mail( $mail_data['to'], $mail_data['subject'], $body_txt, $mail_data['headers'], $mail_data['attachments'] );
		}

		do_action( 'immonex_core_mail_result', $mail_result, $mail_data, $this->plugin_slug );

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
	 * Check if the given (plain text) mail body contains a "signature" starting
	 * with "--".
	 *
	 * @since 1.5.0
	 *
	 * @param string $body_txt Mail body (plain text).
	 *
	 * @return bool True if a signature exists.
	 */
	private function has_signature( $body_txt ) {
		return 1 === preg_match( '/(^|\r\n|\r|\n)--(\r\n|\r|\n|$)/', $body_txt );
	} // has_signature

	/**
	 * Add a header section to the given plain text mail body based
	 * on the related HTML body contents (header, logo alt text).
	 *
	 * @since 1.5.0
	 *
	 * @param string  $body_txt      Original plain text mail body.
	 * @param mixed[] $template_data HTML template data.
	 *
	 * @return string Extended or original mail body text.
	 */
	private function maybe_add_plain_text_header( $body_txt, $template_data ) {
		$header = '';

		if ( ! empty( $template_data['header'] ) ) {
			$header = $this->string_utils::html_to_plain_text( $template_data['header'], true );
		} elseif ( ! empty( $template_data['header_text'] ) ) {
			$header = $this->string_utils::html_to_plain_text( $template_data['header_text'], true );
		}

		if (
			! empty( $template_data['logo_alt'] )
			&& 'Logo' !== $template_data['logo_alt']
			&& isset( $template_data['layout']['logo_position'] )
			&& preg_match( '/^(header|top)/', $template_data['layout']['logo_position'] )
		) {
			if ( $header ) {
				$header .= PHP_EOL . PHP_EOL;
			}
			$header .= $template_data['logo_alt'];
		}

		return $header ?
			$header . PHP_EOL . str_repeat( '-', 70 ) . PHP_EOL . PHP_EOL . $body_txt :
			$body_txt;
	} // maybe_add_plain_text_header

	/**
	 * Add a footer (signature) to the given plain text mail body based
	 * on the related HTML body contents (footer, logo alt text).
	 *
	 * @since 1.5.0
	 *
	 * @param string  $body_txt      Original plain text mail body.
	 * @param mixed[] $template_data HTML template data.
	 *
	 * @return string Extended or original mail body text.
	 */
	private function maybe_add_plain_text_footer( $body_txt, $template_data ) {
		$footer = '';

		if ( ! empty( $template_data['footer'] ) ) {
			$footer = $this->string_utils::html_to_plain_text( $template_data['footer'], true );
		} elseif ( ! empty( $template_data['footer_text'] ) ) {
			$footer = $this->string_utils::html_to_plain_text( $template_data['footer_text'], true );
		} elseif (
			! empty( $template_data['logo_alt'] )
			&& 'Logo' !== $template_data['logo_alt']
			&& isset( $template_data['layout']['logo_position'] )
			&& 'footer' === substr( $template_data['layout']['logo_position'], 0, 6 )
		) {
			$footer = $template_data['logo_alt'];
		}

		if ( $footer ) {
			return wp_sprintf(
				'%s%s--%s',
				trim( $body_txt ),
				PHP_EOL . PHP_EOL,
				PHP_EOL . $footer
			);
		}

		return $body_txt;
	} // maybe_add_plain_text_footer

	/**
	 * Compile the data/parameters for rendering the default HTML mail
	 * frame template (Twig 3).
	 *
	 * @since 1.4.0
	 *
	 * @param mixed[] $org_data Custom params to override the defaults.
	 * @param string  $body     Main mail body content.
	 *
	 * @return mixed[] Complete set of template contents/params.
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
				'margin_bottom'    => '24px',
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
			$defaults['logo_alt']                = 'immonex® - ' . __( 'Professional Real Estate Solutions for WordPress', 'immonex-wp-free-plugin-core' );
			$defaults['layout']['logo_position'] = 'footer_right';

			if (
				empty( $org_data['footer'] )
				&& empty( $org_data['footer_text'] )
			) {
				$footer = wp_sprintf(
					// translators: %1$s = Plugin name, %2$s = Site URL, %3$s = Site title.
					'<p>' . __( 'This message was generated by the plugin <strong>%1$s</strong> at <a href="%2$s">%3$s</a>.', 'immonex-wp-free-plugin-core' ) . '</p>',
					$this->bootstrap_data['plugin_name'],
					get_home_url(),
					get_bloginfo( 'name' )
				);

				$org_data['footer_text'] = "<small>{$footer}</small>";
			}
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
