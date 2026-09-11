<?php
defined('ABSPATH') || exit;

class Beautia_OTP {
    private int $expire = 120;

    public function generate(string $mobile): string {
        $code = (string) wp_rand(100000, 999999);
        set_transient('beautia_otp_' . md5($mobile), wp_hash($code), $this->expire);
        return $code;
    }

    public function verify(string $mobile, string $code): bool {
        $saved = get_transient('beautia_otp_' . md5($mobile));
        if (!$saved) return false;
        if (!hash_equals($saved, wp_hash($code))) return false;
        delete_transient('beautia_otp_' . md5($mobile));
        return true;
    }
}
