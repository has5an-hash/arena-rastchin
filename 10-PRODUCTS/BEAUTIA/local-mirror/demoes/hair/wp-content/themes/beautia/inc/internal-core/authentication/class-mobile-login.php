<?php
defined('ABSPATH') || exit;

class Beautia_Mobile_Login {
    public function __construct() {
        add_action('wp_ajax_nopriv_beautia_send_otp', [$this, 'send']);
        add_action('wp_ajax_nopriv_beautia_verify_otp', [$this, 'verify']);
    }

    public function send() {
        check_ajax_referer('beautia_login_nonce', 'nonce');
        $mobile = sanitize_text_field(wp_unslash($_POST['mobile'] ?? ''));
        if (!$mobile) wp_send_json_error(['message' => 'شماره موبایل وارد نشده']);

        $security = new Beautia_OTP_Security();
        if (!$security->can_send($mobile)) {
            wp_send_json_error(['message' => 'لطفا کمی صبر کنید']);
        }

        $otp = new Beautia_OTP();
        $code = $otp->generate($mobile);

        do_action('beautia_send_sms_otp', $mobile, $code);

        wp_send_json_success(['message' => 'کد تایید ارسال شد']);
    }

    public function verify() {
        check_ajax_referer('beautia_login_nonce', 'nonce');
        $mobile = sanitize_text_field(wp_unslash($_POST['mobile'] ?? ''));
        $code = sanitize_text_field(wp_unslash($_POST['code'] ?? ''));

        if (!(new Beautia_OTP())->verify($mobile, $code)) {
            wp_send_json_error(['message' => 'کد تایید اشتباه است']);
        }

        $user = get_user_by('login', $mobile);
        if (!$user) {
            $id = wp_create_user($mobile, wp_generate_password(), $mobile . '@beautia.local');
            if (is_wp_error($id)) wp_send_json_error(['message' => 'خطا در ساخت کاربر']);
            $user = get_user_by('id', $id);
        }

        wp_set_auth_cookie($user->ID, true);
        wp_send_json_success(['message' => 'ورود موفق']);
    }
}
