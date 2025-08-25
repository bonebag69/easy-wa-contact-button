<?php
/**
 * Plugin Name:       Chat2Book Button
 * Description:       Crea un pulsante/form per inviare richieste via chat (WhatsApp, ecc.) per B&B, escursioni, musei e noleggi.
 * Version:           1.0.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Marco Bellu
 * Text Domain:       chat2book-button
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class C2BOOK_Plugin {

	const OPT_NUMBER = 'c2book_default_number';
	const OPT_MSG_BNB = 'c2book_msg_bnb';
	const OPT_MSG_EXC = 'c2book_msg_exc';

	public function __construct() {
		add_shortcode( 'c2book-button', [ $this, 'render_shortcode' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
		add_action( 'admin_post_c2book_save_settings', [ $this, 'handle_settings_save' ] );
		add_action( 'admin_init', [ $this, 'maybe_migrate_old_options' ] );
	}

	public function maybe_migrate_old_options() {
		$flag = get_option( 'c2book_migrated', '0' );
		if ( '1' === $flag ) {
			return;
		}
		$map = [
			'whatsapp_booking_default_number'   => self::OPT_NUMBER,
			'whatsapp_booking_default_message'  => self::OPT_MSG_BNB,
			'whatsapp_booking_excursion_message'=> self::OPT_MSG_EXC,
			'easy_wa_default_number'            => self::OPT_NUMBER,
			'easy_wa_msg_bnb'                   => self::OPT_MSG_BNB,
			'easy_wa_msg_exc'                   => self::OPT_MSG_EXC,
		];
		foreach ( $map as $old => $new ) {
			$val = get_option( $old, null );
			if ( null !== $val && '' === get_option( $new, '' ) ) {
				update_option( $new, $val );
			}
		}
		update_option( 'c2book_migrated', '1' );
	}

	public function enqueue_assets() {
		$ver = '1.0.2';

		wp_register_style(
			'c2book-button',
			plugins_url( 'assets/css/c2book-button.css', __FILE__ ),
			[],
			$ver
		);
		wp_enqueue_style( 'c2book-button' );

		wp_register_script(
			'c2book-button',
			plugins_url( 'assets/js/c2book-button.js', __FILE__ ),
			[],
			$ver,
			true
		);

		$default_date = gmdate( 'Y-m-d', strtotime( '+1 day' ) );

		$tpl_bnb = get_option(
			self::OPT_MSG_BNB,
			/* translators: 1: check-in date, 2: check-out date, 3: number of guests */
			__( 'Salve, vorrei prenotare dal {checkin} al {checkout} per {ospiti} ospiti.', 'chat2book-button' )
		);

		$tpl_exc = get_option(
			self::OPT_MSG_EXC,
			/* translators: 1: excursion date, 2: number of participants */
			__( 'Salve, sono interessato all’escursione del {date} per {participants} partecipanti.', 'chat2book-button' )
		);

		wp_localize_script(
			'c2book-button',
			'C2BOOK_CONTACT',
			[
				'defaultNumber' => get_option( self::OPT_NUMBER, '393000000000' ),
				'tplBnb'        => $tpl_bnb,
				'tplExc'        => $tpl_exc,
				'defaultDate'   => $default_date,
				'i18n'          => [
					'send'         => __( 'Invia richiesta', 'chat2book-button' ),
					'date'         => __( 'Data', 'chat2book-button' ),
					'checkin'      => __( 'Check-in', 'chat2book-button' ),
					'checkout'     => __( 'Check-out', 'chat2book-button' ),
					'guests'       => __( 'Ospiti', 'chat2book-button' ),
					'participants' => __( 'Partecipanti', 'chat2book-button' ),
					'select'       => __( 'Seleziona', 'chat2book-button' ),
					'validation'   => [
						'date_required'     => __( 'Seleziona una data valida.', 'chat2book-button' ),
						'participants_req'  => __( 'Seleziona il numero di partecipanti.', 'chat2book-button' ),
						'checkin_required'  => __( 'Seleziona una data di check-in valida.', 'chat2book-button' ),
						'checkout_required' => __( 'Seleziona una data di check-out valida.', 'chat2book-button' ),
						'checkout_after'    => __( 'Il check-out deve essere successivo al check-in.', 'chat2book-button' ),
						'guests_required'   => __( 'Seleziona il numero di ospiti.', 'chat2book-button' ),
					],
				],
			]
		);

		wp_enqueue_script( 'c2book-button' );
	}

	/**
	 * Shortcode [c2book-button mode="bnb|excursion" number="393..."]
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			[
				'mode'   => 'bnb',
				'number' => '',
			],
			$atts,
			'c2book-button'
		);

		$mode   = ( 'excursion' === $atts['mode'] ) ? 'excursion' : 'bnb';
		$number = $atts['number'] ? preg_replace( '/\D+/', '', $atts['number'] ) : get_option( self::OPT_NUMBER, '393000000000' );

		$instance = esc_attr( wp_unique_id( 'c2book_' ) );
		$title    = esc_html( get_the_title() ?: get_bloginfo( 'name' ) );

		ob_start();
		?>
		<div class="c2book-container" data-c2book-instance="<?php echo $instance; ?>" data-c2book-mode="<?php echo esc_attr( $mode ); ?>" data-c2book-number="<?php echo esc_attr( $number ); ?>">
			<h3 class="c2book-title">
				<?php
				echo ( 'excursion' === $mode )
					? esc_html__( 'Richiesta Escursione via Chat', 'chat2book-button' )
					: esc_html__( 'Prenotazione B&B via Chat', 'chat2book-button' );
				?>
			</h3>

			<div class="c2book-fields">
				<?php if ( 'excursion' === $mode ) : ?>
					<div>
						<label for="<?php echo $instance; ?>-date"><?php esc_html_e( 'Data:', 'chat2book-button' ); ?></label>
						<input type="date" id="<?php echo $instance; ?>-date" required />
					</div>
					<div>
						<label for="<?php echo $instance; ?>-participants"><?php esc_html_e( 'Partecipanti:', 'chat2book-button' ); ?></label>
						<select id="<?php echo $instance; ?>-participants" required>
							<option value="" disabled selected><?php esc_html_e( 'Seleziona', 'chat2book-button' ); ?></option>
							<?php for ( $i = 1; $i <= 20; $i++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
							<?php endfor; ?>
						</select>
					</div>
				<?php else : ?>
					<div>
						<label for="<?php echo $instance; ?>-checkin"><?php esc_html_e( 'Check-in:', 'chat2book-button' ); ?></label>
						<input type="date" id="<?php echo $instance; ?>-checkin" required />
						<span class="description"><?php esc_html_e( 'Data di arrivo', 'chat2book-button' ); ?></span>
					</div>
					<div>
						<label for="<?php echo $instance; ?>-checkout"><?php esc_html_e( 'Check-out:', 'chat2book-button' ); ?></label>
						<input type="date" id="<?php echo $instance; ?>-checkout" required />
						<span class="description"><?php esc_html_e( 'Data di partenza', 'chat2book-button' ); ?></span>
					</div>
					<div>
						<label for="<?php echo $instance; ?>-guests"><?php esc_html_e( 'Ospiti:', 'chat2book-button' ); ?></label>
						<select id="<?php echo $instance; ?>-guests" required>
							<option value="" disabled selected><?php esc_html_e( 'Seleziona', 'chat2book-button' ); ?></option>
							<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
							<?php endfor; ?>
						</select>
						<span class="description"><?php esc_html_e( 'Numero di ospiti', 'chat2book-button' ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<button type="button" class="c2book-send">
				<?php esc_html_e( 'Invia richiesta', 'chat2book-button' ); ?>
			</button>

			<input type="hidden" id="<?php echo $instance; ?>-title" value="<?php echo $title; ?>" />
		</div>
		<?php
		return ob_get_clean();
	}

	public function register_settings_page() {
		add_options_page(
			__( 'Impostazioni Chat2Book', 'chat2book-button' ),
			__( 'Chat2Book', 'chat2book-button' ),
			'manage_options',
			'c2book-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$number  = get_option( self::OPT_NUMBER, '393000000000' );
		$tpl_bnb = get_option( self::OPT_MSG_BNB, __( 'Salve, vorrei prenotare dal {checkin} al {checkout} per {ospiti} ospiti.', 'chat2book-button' ) );
		$tpl_exc = get_option( self::OPT_MSG_EXC, __( 'Salve, sono interessato all’escursione del {date} per {participants} partecipanti.', 'chat2book-button' ) );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Impostazioni Chat2Book', 'chat2book-button' ); ?></h1>

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<?php wp_nonce_field( 'c2book_save_settings' ); ?>
				<input type="hidden" name="action" value="c2book_save_settings" />

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="c2book_default_number"><?php esc_html_e( 'Numero chat predefinito (internazionale, senza +)', 'chat2book-button' ); ?></label></th>
						<td><input type="text" id="c2book_default_number" name="c2book_default_number" value="<?php echo esc_attr( $number ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="c2book_msg_bnb"><?php esc_html_e( 'Template messaggio B&B', 'chat2book-button' ); ?></label></th>
						<td>
							<textarea id="c2book_msg_bnb" name="c2book_msg_bnb" class="large-text" rows="3"><?php echo esc_textarea( $tpl_bnb ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Placeholders: {checkin}, {checkout}, {ospiti}, {data}, {persone}, {titolo}', 'chat2book-button' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="c2book_msg_exc"><?php esc_html_e( 'Template messaggio Escursioni', 'chat2book-button' ); ?></label></th>
						<td>
							<textarea id="c2book_msg_exc" name="c2book_msg_exc" class="large-text" rows="3"><?php echo esc_textarea( $tpl_exc ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Placeholders: {date}, {participants}, {titolo}', 'chat2book-button' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button( esc_html__( 'Salva impostazioni', 'chat2book-button' ) ); ?>
			</form>

			<p class="description">
				<?php echo esc_html__( 'Nota: “WhatsApp” è un marchio di Meta. Questo plugin non è affiliato, sponsorizzato o approvato da Meta/WhatsApp.', 'chat2book-button' ); ?>
			</p>
		</div>
		<?php
	}

	public function handle_settings_save() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'c2book_save_settings' ) ) {
			wp_die( esc_html__( 'Non autorizzato.', 'chat2book-button' ) );
		}

		$number = isset( $_POST['c2book_default_number'] ) ? sanitize_text_field( wp_unslash( $_POST['c2book_default_number'] ) ) : '';
		$number = preg_replace( '/\D+/', '', $number );

		update_option( self::OPT_NUMBER, $number );
		update_option( self::OPT_MSG_BNB, isset( $_POST['c2book_msg_bnb'] ) ? sanitize_textarea_field( wp_unslash( $_POST['c2book_msg_bnb'] ) ) : '' );
		update_option( self::OPT_MSG_EXC, isset( $_POST['c2book_msg_exc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['c2book_msg_exc'] ) ) : '' );

		wp_safe_redirect( admin_url( 'options-general.php?page=c2book-settings&updated=1' ) );
		exit;
	}
}

new C2BOOK_Plugin();
