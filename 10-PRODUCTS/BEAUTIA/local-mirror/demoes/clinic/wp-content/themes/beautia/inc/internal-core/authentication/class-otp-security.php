<?php
defined('ABSPATH') || exit;

class Beautia_OTP_Security {
    public function can_send(string $mobile): bool {
        $key = 'beautia_otp_limit_' . md5($mobile);
        if (get_transient($key)) return false;
        set_transient($key, 1, 60);
        return true;
    }
}
