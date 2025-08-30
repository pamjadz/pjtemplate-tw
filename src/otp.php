<?php
final class ArvandOTP {
	private static ?self $_instance = null;
	public static int $timeout;
	private static int $try_expire;
	private static int $max_login_try = 4;
	private string $table;

    private function __construct() {
		self::$timeout = (int)(MINUTE_IN_SECONDS * 4.5);
		self::$try_expire = (int)(MINUTE_IN_SECONDS * 15);

        add_action('wp_ajax_nopriv_arvand_otp', [$this, 'ajax_action_response']);
    }

    public static function instance(): self {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
			self::$_instance->create_db();
		}
		return self::$_instance;
	}

    private function create_db(): void {
        global $wpdb;
		$this->table = $wpdb->prefix . 'users_otp';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
			`identity` VARCHAR(100) NOT NULL,
			`otp` CHAR(64) DEFAULT NULL,
			`time` BIGINT DEFAULT NULL,
			`expire` BIGINT DEFAULT NULL,
			`try_count` INT DEFAULT 0,
			`last_try` BIGINT DEFAULT 0,
			PRIMARY KEY (`identity`)
		) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    private function setotp(string $identity, string|int $otpcode): WP_Error|bool {
        global $wpdb;
        $identity = sanitize_text_field($identity);
        $otpcode = sanitize_text_field($otpcode);

        if( !$identity || !$otpcode ) return false;
		
        $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE `identity` = %s", $identity));
        $now = current_time('U');

        $try_count = 0;
        if ($record) {
            if (($now - (int)$record->last_try) > self::$try_expire) {
                $try_count = 0;
            } else {
                $try_count = (int)$record->try_count;
            }

            if ($try_count >= self::$max_login_try) {
                return new WP_Error('otp_security', 'تلاش بیش از حد برای ورود! 15 دقیقه دیگر تلاش کنید');
            }
            $try_count++;
        } else {
            $try_count = 1;
        }

        $otp_hashed = hash_hmac('sha256', $otpcode, wp_salt());

        $data = [
            'otp' => $otp_hashed,
            'time' => $now,
            'expire' => $now + self::$timeout,
            'try_count' => $try_count,
            'last_try' => $now
        ];

        if ($record) {
            $wpdb->update($this->table, $data, ['identity' => $identity], ['%s','%d','%d','%d','%d'], ['%s']);
        } else {
            $data['identity'] = $identity;
            $wpdb->insert($this->table, $data, ['%s','%s','%d','%d','%d','%d']);
        }

        return true;
    }

    private static function getotp(string $identity): ?object {
        global $wpdb;
        $identity = sanitize_text_field($identity);
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE `identity` = %s", $identity));
    }

    public function ajax_action_response(): void {
        if ( empty( $_POST['inputs'] ) ) {
            wp_send_json(['status' => false, 'notice' => 'پارامترهای لازم ارسال نشده‌اند']);
        }

        parse_str(wp_unslash($_POST['inputs']), $params);

        if (empty($params['arvand_otp']) || !wp_verify_nonce($params['arvand_otp'], 'arvand_otp')) {
            wp_send_json(['status' => false, 'notice' => 'لطفا صفحه را بروز و مجددا تلاش کنید']);
        }

        $action = isset($_POST['target']) ? sanitize_text_field($_POST['target']) : '';
        $output = ['status' => false, 'notice' => false, 'fragment' => false, 'redirect' => false];

        switch ($action) {
            case 'sendcode':
                $username = sanitize_phone_ir($params['username'] ?? '');
                if ($username) {
                    if ('yes' !== get_option('woocommerce_enable_myaccount_registration') && !username_exists($username)) {
                        $output['notice'] = 'کاربری با این شماره یافت نشد';
                        break;
                    }
                    $otpcode = $this->otpcode();
                    $otp = $this->setotp($username, $otpcode);
                    if (is_wp_error($otp)) {
                        $output['notice'] = $otp->get_error_message();
                    } else {
                        $this->setcookie($username, 'authcode');
                        $this->send_otp_to($username, $otpcode);
                        ob_start();
                        self::get_template('authcode', ['record' => $this->getotp($username)]);
                        $output['fragment'] = ob_get_clean();
                    }
                } else {
                    $output['notice'] = 'فرمت شماره موبایل صحیح نمی‌باشد';
                }
                break;

            case 'checkcode':
                $cookie = $this->getcookie();
                $username = $cookie->username ?? '';
                $record = self::getotp($username);

                if (!$record) {
                    $output['notice'] = 'لطفا مجددا تلاش کنید! خطایی ناشناخته وجود دارد';
                    break;
                }

                if ($record->expire < time()) {
                    $this->delcookie();
                    $output['notice'] = 'زمان شما منقضی شد. از اول تلاش کنید';
                    break;
                }

                $authcode = sanitize_text_field($params['authcode'] ?? '');
                if (hash_hmac('sha256', $authcode, wp_salt()) === $record->otp) {
                    if ($this->set_current_user($username)) {
                        $output['redirect'] = esc_url($params['redirect'] ?? wc_get_page_permalink('myaccount'));
                        $this->delcookie();
                    } else {
                        $output['notice'] = 'حساب کاربری شما یافت نشد!';
                    }
                } else {
                    $output['notice'] = 'کد وارد شده صحیح نمی‌باشد';
                }
                break;

            case 'resendcode':
                $cookie = $this->getcookie();
                $username = $cookie->username ?? '';
                $otpcode = $this->otpcode();
                $otp = $this->setotp($username, $otpcode);

                if (is_wp_error($otp)) {
                    $output['notice'] = $otp->get_error_message();
                } else {
                    $this->send_otp_to($username, $otpcode);
                    $output['status'] = true;
                    $output['notice'] = 'کد با موفقیت مجددا ارسال شد.';
                }
                break;

            case 'resetcode':
                $this->delcookie();
                ob_start();
                self::get_template('username');
                $output['fragment'] = ob_get_clean();
                break;
        }

        wp_send_json($output);
    }

    private function setcookie(string $username, string $target): void {
        setcookie('arvand_otp', wp_json_encode(['username' => $username, 'target' => $target]), [
            'expires' => time() + self::$timeout,
            'path' => COOKIEPATH,
            'domain' => COOKIE_DOMAIN,
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function delcookie(): void {
        setcookie('arvand_otp', '', [
            'expires' => time() - 3600,
            'path' => COOKIEPATH,
            'domain' => COOKIE_DOMAIN,
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function otpcode(): int {
        return random_int(10000, 99999);
    }

    private function getcookie(): object|false {
        return isset($_COOKIE['arvand_otp']) ? json_decode( stripslashes($_COOKIE['arvand_otp']) ) : false;
    }

    public static function get_template(string $step = '', array $args = []): void {
        $instance = self::instance();
        $cookie = $instance->getcookie();
        if (!$step) {
            $step = $cookie->target ?? 'username';
        }
        if ($step === 'authcode') {
            $record = $cookie->username ? $instance->getotp($cookie->username) : null;
            $args = wp_parse_args($args, ['record' => $record]);
        }
        wc_get_template("myaccount/otp/{$step}.php", $args);
    }

    public function send_otp_to(string $username, string|int $code): WP_Error|bool {
        $sent = kavenegar_lookup($username, 'verifylogin', $code);
        return $sent->status ? true : new WP_Error('otpsent_failed', $sent->message);
    }

    private function set_current_user(string $username): bool {
		global $wpdb;
        $user = get_user_by('login', $username);
        if( ! $user ) {
			$user_id = wp_insert_user([
				'user_login'	=> $username,
				'user_pass'		=> wp_generate_password(10),
				'user_email'	=> null,
			]);
			if( !is_wp_error($user_id) ) {
				wp_update_user(['ID' => $user_id, 'display_name' => 'کاربر ' . $user_id]);
				$user = get_user_by('ID', $user_id);
			}
        }

        if ($user) {
			$wpdb->update($this->table, ['try_count' => 0, 'last_try' => 0], ['identity' => $username], ['%d','%d'], ['%s']);
			wp_set_auth_cookie($user->ID, true);
			wp_set_current_user($user->ID, $user->user_login);
			do_action('wp_login', $user->user_login, $user);
            return $user;
		}
        return false;
    }
}

ArvandOTP::instance();
