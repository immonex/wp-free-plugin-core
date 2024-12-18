<?php
/**
 * Class Video_Utils
 *
 * @package immonex\WordPressFreePluginCore
 */

namespace immonex\WordPressFreePluginCore\V2_1_9;

/**
 * Video (embedding) related utilities.
 */
class Video_Utils {

	const PROVIDER_URL_REGEX = [
		'youtube'     => [
			'/https?:\/\/(?:[0-9A-Z-]+\.)?(?:youtu\.be\/|youtube(?:-nocookie)?\.com\S*[^\w\s-])([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>|<\/a>))[?=&+%\w.-]*/i',
			'/(?-is)^(?:(?!https?[:\/]|www\.|m(?:usic)?\.yo|youtu\.?be[\/\.]|watch[\/\?]|embed\/|YTv(?:[=: ]+)|YouTube: )\V)*?(?:(?:https?[:\/]+|www\.|m\.|music\.)+youtu(?<a>\.)?be(?:\.com\/|watch|live(?=\/)|o?embed(?:\/|\?url=\S+?)?|\?app=\w+|shorts|attribution_link\?[&\w\-=]*[au]=\/?|ytsc\w+|[\?&]*(?:li)?[ve]i?\b[\/=]|\?feature=[\-a-z_\.]+|[\?&]t(?:ime_continue)?=\d+|-nocookie|%[23][56FD]|\.com\/shorts\/)*(?(<a>)\/|)|YTv(?:[=: ]+)|YouTube: )(?(?<=watch)\/|)([a-zA-Z\d\-_]{11}\b)(?>[\?&\#][&\#%a-zA-Z\-\d=;_\.]*|&feature=[\-a-z_\.]+)?(?=\s|\Z)/',
		],
		'vimeo'       => '/^.*vimeo\.com\/(?:[a-z]*\/)*([0-9‌​]{6,11})[?]?.*/',
		'dailymotion' => '/^.*(?:dailymotion.com\/(?:video|hub)|dai.ly)\/([^_]+)[^\#]*(\#video=([^_&]+))?/',
		'videopress'  => [
			'/^(?:http(?:s)?:\/\/)?videos\.files\.wordpress\.com\/([a-zA-Z\d]{8,})\//i',
			'/^(?:http(?:s)?:\/\/)?(?:www\.)?video(?:\.word)?press\.com\/(?:v|embed)\/([a-zA-Z\d]{8,})(.+)?/i',
		],
	];

	const PROVIDER_EMBED_PATTERNS = [
		'youtube'     => 'https://www.youtube{NOCOOKIE}.com/embed/{ID}?feature=oembed',
		'vimeo'       => 'https://player.vimeo.com/video/{ID}',
		'dailymotion' => 'https://dailymotion.com/embed/video/{ID}',
		'videopress'  => 'https://videopress.com/embed/{ID}',
	];

	const DEFAULT_IFRAME_TEMPLATE = '<iframe src="{URL}" frameborder="0" class="inx-video-iframe" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';

	/**
	 * Determine video provider and ID of the given URL.
	 *
	 * @since 2.1.3
	 *
	 * @param string $url Video URL.
	 *
	 * @return mixed[]|bool Array with provider key, video ID and original URL
	 *                      or false if no match.
	 */
	public static function split_video_url( $url ) {
		foreach ( self::PROVIDER_URL_REGEX as $provider => $patterns ) {
			if ( ! is_array( $patterns ) ) {
				$patterns = [ $patterns ];
			}

			foreach ( $patterns as $pattern ) {
				if ( preg_match_all( $pattern, $url, $matches ) ) {
					return [
						'provider' => $provider,
						'id'       => $matches[1][0],
						'url'      => $url,
					];
				}
			}
		}

		return false;
	} // split_video_url

	/**
	 * Return the embed URL for the given video URL if determinable.
	 *
	 * @since 2.1.3
	 *
	 * @param string  $url_or_props Source Video URL or video property array.
	 * @param mixed[] $options      Optional Embed options (youtube-nocookie, time).
	 *
	 * @return string:bool Embed URL or false if undeterminable.
	 */
	public static function get_embed_url( $url_or_props, $options = [] ) {
		$video_props = is_string( $url_or_props ) ? self::split_video_url( $url_or_props ) : $url_or_props;

		if ( ! $video_props ) {
			return false;
		}

		$replace = [
			'{NOCOOKIE}' => ! empty( $options['youtube-nocookie'] )
				|| false !== strpos( $video_props['url'], 'youtube-nocookie' ) ? '-nocookie' : '',
			'{ID}'       => $video_props['id'],
		];

		$provider_oembed_url = self::PROVIDER_EMBED_PATTERNS[ $video_props['provider'] ];
		$url_params          = [];

		if ( 'vimeo' === $video_props['provider'] ) {
			if ( ! empty( $options['time'] ) ) {
				$provider_oembed_url .= '#t={TIME}';
				$replace['{TIME}']    = $options['time'];
			}

			/**
			 * Handle private Vimeo videos (extract privacy token and add as h parameter).
			 */
			preg_match( '/(?|(?:[\?|\&]h={1})([\w]+)|\d\/([\w]+))/', $video_props['url'], $h_param );

			if ( ! empty( $h_param ) ) {
				$url_params['h'] = $h_param[1];
			}
		}

		$embed_pattern = str_replace( array_keys( $replace ), $replace, $provider_oembed_url );

		return add_query_arg( $url_params, $embed_pattern );
	} // get_embed_url

} // class Video_Utils
