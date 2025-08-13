<?php
/**
 * Plugin Name:       Easy WA Contact Button
 * Description:       Crea un pulsante/form per inviare richieste via WhatsApp (B&B, escursioni, musei, noleggi).
 * Version:           1.0.1
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Author:            Marco Bellu
 * Text Domain:       easy-wa-contact-button
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 */


if ( ! defined( 'ABSPATH' ) ) { exit; }

final class EWA_Plugin {
    const OPT_NUMBER  = 'ewa_default_number';
    const OPT_MSG_BNB = 'ewa_msg_bnb';
    const OPT_MSG_EXC = 'ewa_msg_excursion';

    // Chiavi legacy per migrazione soft (se arrivi dal vecchio plugin)
    const LEGACY_NUM  = 'whatsapp_booking_default_number';
    const LEGACY_BNB  = 'whatsapp_booking_default_message';
    const LEGACY_EXC  = 'whatsapp_booking_excursion_message';

    public function __construct() {
    
        add_action('admin_init',         [$this, 'register_settings']);
        add_action('admin_menu',         [$this, 'admin_menu']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        // Shortcode nuovo + alias compatibilità con quello vecchio
        add_shortcode('ewa-contact-button',   [$this, 'render_shortcode']);
        add_shortcode('whatsapp-book-button', [$this, 'render_shortcode']);
    }

    

    private function maybe_migrate_options() {
        if ( ! get_option(self::OPT_NUMBER) && ($legacy = get_option(self::LEGACY_NUM)) ) {
            update_option(self::OPT_NUMBER, $this->sanitize_phone($legacy));
        }
        if ( ! get_option(self::OPT_MSG_BNB) && ($legacy = get_option(self::LEGACY_BNB)) ) {
            update_option(self::OPT_MSG_BNB, $legacy);
        }
        if ( ! get_option(self::OPT_MSG_EXC) && ($legacy = get_option(self::LEGACY_EXC)) ) {
            update_option(self::OPT_MSG_EXC, $legacy);
        }
    }

    public function sanitize_phone($raw) {
        return preg_replace('/\D+/', '', (string)$raw);
    }

    public function register_settings() {
        register_setting('ewa_contact', self::OPT_NUMBER, [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_phone'],
            'default' => '393000000000',
        ]);
        register_setting('ewa_contact', self::OPT_MSG_BNB, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default' => __('Salve, vorrei prenotare dal {checkin} al {checkout} per {ospiti} ospiti.', 'easy-wa-contact-button'),
        ]);
        register_setting('ewa_contact', self::OPT_MSG_EXC, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default' => __('Salve, sono interessato all\'escursione del {date} per {participants} partecipanti.', 'easy-wa-contact-button'),
        ]);

        add_settings_section('ewa_contact_sec', __('Impostazioni generali', 'easy-wa-contact-button'), '__return_false', 'ewa_contact');

        add_settings_field(self::OPT_NUMBER, __('Numero WhatsApp predefinito', 'easy-wa-contact-button'), function() {
            printf('<input type="text" class="regular-text" name="%1$s" value="%2$s"/>',
                esc_attr(self::OPT_NUMBER),
                esc_attr(get_option(self::OPT_NUMBER, '393000000000'))
            );
        }, 'ewa_contact', 'ewa_contact_sec');

        add_settings_field(self::OPT_MSG_BNB, __('Template messaggio B&B', 'easy-wa-contact-button'), function() {
            $val = get_option(self::OPT_MSG_BNB);
            echo '<textarea class="large-text" rows="4" name="'.esc_attr(self::OPT_MSG_BNB).'">'.esc_textarea($val).'</textarea>';
            echo '<p class="description">'.esc_html__('Placeholders: {checkin}, {checkout}, {ospiti}, {data}, {persone}, {titolo}', 'easy-wa-contact-button').'</p>';
        }, 'ewa_contact', 'ewa_contact_sec');

        add_settings_field(self::OPT_MSG_EXC, __('Template messaggio Escursioni', 'easy-wa-contact-button'), function() {
            $val = get_option(self::OPT_MSG_EXC);
            echo '<textarea class="large-text" rows="4" name="'.esc_attr(self::OPT_MSG_EXC).'">'.esc_textarea($val).'</textarea>';
            echo '<p class="description">'.esc_html__('Placeholders: {date}, {participants}, {titolo}', 'easy-wa-contact-button').'</p>';
        }, 'ewa_contact', 'ewa_contact_sec');
    }

    public function admin_menu() {
        add_options_page(
            __('Easy WA Contact — Impostazioni', 'easy-wa-contact-button'),
            __('Easy WA Contact', 'easy-wa-contact-button'),
            'manage_options',
            'ewa-contact-settings',
            [$this, 'settings_page']
        );
    }

    public function settings_page() {
        if ( ! current_user_can('manage_options') ) { return; } ?>
        <div class="wrap">
            <h1><?php esc_html_e('Impostazioni Easy WA Contact', 'easy-wa-contact-button'); ?></h1>
            <p><em><?php esc_html_e('WhatsApp è un marchio registrato di Meta Platforms, Inc. Questo plugin non è affiliato, sponsorizzato né approvato da Meta o da WhatsApp.', 'easy-wa-contact-button'); ?></em></p>
            <form method="post" action="options.php">
                <?php settings_fields('ewa_contact'); do_settings_sections('ewa_contact'); submit_button(__('Salva modifiche', 'easy-wa-contact-button')); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_assets() {
        $ver = '1.0.0';
        wp_register_style('ewa-contact-button', plugins_url('assets/css/ewa-contact-button.css', __FILE__), [], $ver);
        wp_enqueue_style('ewa-contact-button');

        wp_register_script('ewa-contact-button', plugins_url('assets/js/ewa-contact-button.js', __FILE__), [], $ver, true);

        $default_date = gmdate('Y-m-d', strtotime('+1 day'));
		$tpl_bnb_default = __('Salve, vorrei prenotare dal {checkin} al {checkout} per {ospiti} ospiti.', 'easy-wa-contact-button');
		$tpl_exc_default = __('Salve, sono interessato all\'escursione del {date} per {participants} partecipanti.', 'easy-wa-contact-button');

        wp_localize_script('ewa-contact-button', 'EWA_CONTACT', [
            'defaultNumber' => get_option(self::OPT_NUMBER, '393000000000'),
            'tplBnb'        => get_option(self::OPT_MSG_BNB, $tpl_bnb_default) ?: $tpl_bnb_default,
			'tplExc'        => get_option(self::OPT_MSG_EXC, $tpl_exc_default) ?: $tpl_exc_default,
            'defaultDate'   => $default_date,
            'i18n'          => [
                'send'        => __('Invia richiesta', 'easy-wa-contact-button'),
                'date'        => __('Data', 'easy-wa-contact-button'),
                'checkin'     => __('Check-in', 'easy-wa-contact-button'),
                'checkout'    => __('Check-out', 'easy-wa-contact-button'),
                'guests'      => __('Ospiti', 'easy-wa-contact-button'),
                'participants'=> __('Partecipanti', 'easy-wa-contact-button'),
                'select'      => __('Seleziona', 'easy-wa-contact-button'),
                'validation'  => [
                    'date_required'    => __('Seleziona una data valida.', 'easy-wa-contact-button'),
                    'participants_req' => __('Seleziona il numero di partecipanti.', 'easy-wa-contact-button'),
                    'checkin_required' => __('Seleziona una data di check-in valida.', 'easy-wa-contact-button'),
                    'checkout_required'=> __('Seleziona una data di check-out valida.', 'easy-wa-contact-button'),
                    'checkout_after'   => __('Il check-out deve essere successivo al check-in.', 'easy-wa-contact-button'),
                    'guests_required'  => __('Seleziona il numero di ospiti.', 'easy-wa-contact-button'),
                ],
            ],
        ]);
        wp_enqueue_script('ewa-contact-button');
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'number' => '',
            'mode'   => 'bnb', // bnb|excursion
        ], $atts, 'ewa-contact-button');

        $number = $atts['number'] ? $this->sanitize_phone($atts['number']) : get_option(self::OPT_NUMBER, '393000000000');
        $mode   = $atts['mode'] === 'excursion' ? 'excursion' : 'bnb';

        static $seq = 0; $seq++;
        $id = 'ewa-contact-' . $seq;

        ob_start(); ?>
        <div id="<?php echo esc_attr($id); ?>" class="ewa-contact" data-mode="<?php echo esc_attr($mode); ?>" data-number="<?php echo esc_attr($number); ?>" aria-live="polite">
            <h3 class="ewa-contact__title">
                <?php echo $mode === 'excursion'
                    ? esc_html__('Richiesta Escursione via WhatsApp', 'easy-wa-contact-button')
                    : esc_html__('Prenotazione/Contatto via WhatsApp (B&B)', 'easy-wa-contact-button'); ?>
            </h3>

            <div class="ewa-contact__fields">
                <?php if ($mode === 'excursion'): ?>
                    <div class="ewa-field">
                        <label for="<?php echo esc_attr($id); ?>-date"><?php esc_html_e('Data:', 'easy-wa-contact-button'); ?></label>
                        <input type="date" id="<?php echo esc_attr($id); ?>-date" min="<?php echo esc_attr(gmdate('Y-m-d')); ?>" required aria-required="true">
                    </div>
                    <div class="ewa-field">
                        <label for="<?php echo esc_attr($id); ?>-participants"><?php esc_html_e('Partecipanti:', 'easy-wa-contact-button'); ?></label>
                        <select id="<?php echo esc_attr($id); ?>-participants" required aria-required="true">
                            <option value="" selected disabled><?php esc_html_e('Seleziona', 'easy-wa-contact-button'); ?></option>
                            <?php for ( $i = 1; $i <= 20; $i++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
							<?php endfor; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="ewa-field">
                        <label for="<?php echo esc_attr($id); ?>-ci"><?php esc_html_e('Check-in:', 'easy-wa-contact-button'); ?></label>
                        <input type="date" id="<?php echo esc_attr($id); ?>-ci" min="<?php echo esc_attr(gmdate('Y-m-d')); ?>" required aria-required="true">
                        <span class="ewa-help"><?php esc_html_e('Data di arrivo', 'easy-wa-contact-button'); ?></span>
                    </div>
                    <div class="ewa-field">
                        <label for="<?php echo esc_attr($id); ?>-co"><?php esc_html_e('Check-out:', 'easy-wa-contact-button'); ?></label>
                        <input type="date" id="<?php echo esc_attr($id); ?>-co" required aria-required="true">
                        <span class="ewa-help"><?php esc_html_e('Data di partenza', 'easy-wa-contact-button'); ?></span>
                    </div>
                    <div class="ewa-field">
                        <label for="<?php echo esc_attr($id); ?>-guests"><?php esc_html_e('Ospiti:', 'easy-wa-contact-button'); ?></label>
                        <select id="<?php echo esc_attr($id); ?>-guests" required aria-required="true">
                            <option value="" selected disabled><?php esc_html_e('Seleziona', 'easy-wa-contact-button'); ?></option>
                            <?php for ( $i = 1; $i <= 12; $i++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
							<?php endfor; ?>
                        </select>
                        <span class="ewa-help"><?php esc_html_e('Numero di ospiti', 'easy-wa-contact-button'); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <button type="button" class="ewa-contact__send"><?php esc_html_e('Invia richiesta', 'easy-wa-contact-button'); ?></button>
            <p class="ewa-contact__error" role="alert" style="display:none"></p>
        </div>
        <?php
        return ob_get_clean();
    }
}

new EWA_Plugin();
