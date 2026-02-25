<?php


class Functions {

    public static function getRoles($f='') {
        foreach($f as $key => $fQuery) {
            $SQLWhere .= "AND {$key} like '%{$fQuery}%' ";
        }
        $Return = \Db::getResult("select * from sp_roles WHERE 1 {$SQLWhere}");
        return $Return;
    }

    public static function getUserInfo($userid=''){
        if(!$userid) die('UserID Required...');
        $Return = \Db::getRow("SELECT * FROM sp_users a JOIN sp_roles b ON a.role_id=b.role_id WHERE userid='{$userid}'");
        return $Return;
    }

    public static function getStates(){

    }

    public static function getCountries(){

    }

    public static function isSysAdmin() {
       // \Session::get('login');
    }

    /**
     * Check if the user exist in the database by email.
     * @param $f
     * @return array|false
     */
    public static function CheckUserExist($f){
        $j = \Db::getRow("select * from sp_users where email ='{$f}'");
        return (($j) ? $j : false);
    }

    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     *
     * @param string $email The email address
     * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
     * @param string $d Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
     * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @param boole $img True to return a complete IMG tag False for just the URL
     * @param array $atts Optional, additional key/value attributes to include in the IMG tag
     * @return String containing either just a URL or a complete image tag
     * @source https://gravatar.com/site/implement/images/php/
     */
    public static function getAvatar($email, $s = 80, $d = 'mp', $r = 'g', $img = false, $atts = array()) {

            $url = 'https://www.gravatar.com/avatar/';
            $url .= md5( strtolower( trim( $email ) ) );
            $url .= "?s=$s&d=$d&r=$r";
            if ( $img ) {
                $url = '<img src="' . $url . '"';
                foreach ( $atts as $key => $val )
                    $url .= ' ' . $key . '="' . $val . '"';
                $url .= ' />';
            }
            return $url;

    }

    /** Create Encryption for security in the application
     *
     * @param string $st The string that needs to be encrypted
     * Usage: echo \Functions::Encrypt("Carlos");
     *
     */
    public static function Encrypt($st){
        $encrypted = openssl_encrypt($st, 'AES-128-CTR', HASH_PASSWORD_KEY, OPENSSL_RAW_DATA, '1234567890123456');
        return base64_encode($encrypted);
    }


    /** Create Encryption for security in the application
     *
     * @param string $st The string that needs to be encrypted
     * Usage: echo \Functions::Decrypt("a/pxWERALz2YMd4l32U3ew==");
     *
     */
    public static function Decrypt($st){
        $decrypted = openssl_decrypt(base64_decode($st), 'AES-128-CTR', HASH_PASSWORD_KEY, OPENSSL_RAW_DATA, '1234567890123456');
        return $decrypted;
    }

    public static function DefualtRole(){
       Return \Db::getRow("SELECT role_id, role_name FROM sp_roles WHERE defaultrole='1'");
    }


    public static function encryptData($data, $key) {
        $ivLength = openssl_cipher_iv_length($cipher = 'AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        // Convert to hex to ensure binary safety, then to base64 to make it URL-friendly
        $encryptedBase64 = base64_encode($iv . $encrypted);
        // Replace URL-unfriendly characters from base64 encoding
        $urlSafeEncrypted = strtr($encryptedBase64, '+/', '-_');
        // Optionally remove '=' if present
        $urlSafeEncrypted = rtrim($urlSafeEncrypted, '=');
        return $urlSafeEncrypted;
    }

    public static function decryptData($urlSafeEncrypted, $key) {
        $ivLength = openssl_cipher_iv_length($cipher = 'AES-256-CBC');
        // Reverse the URL-safe transformations
        $base64Encrypted = strtr($urlSafeEncrypted, '-_', '+/');
        // Decode from base64 to binary
        $binaryData = base64_decode($base64Encrypted);
        $iv = substr($binaryData, 0, $ivLength);
        $encrypted = substr($binaryData, $ivLength);
        $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }


        /*// Usage
        $key = 'your-256-bit-secret-key'; // Make sure to use a secure key
        $originalData = "Your secret data";

        $encryptedData = encryptData($originalData, $key);
        echo "Encrypted: " . $encryptedData . "\n";

        $decryptedData = decryptData($encryptedData, $key);
        echo "Decrypted: " . $decryptedData . "\n";
        */

    // This gets the system default organization.
    // Ideally for single organization applications with users going straight into the org or for api calls that have no business name assigned to the field.
    public static function DefaultOrg(){
       Return \Db::getRow("SELECT orgid, catid, name FROM sp_orgs WHERE defaultorg='1'");
    }

    public static function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }



    public static function ip_in_range($ip, $range) {
        if (strpos($range, '/') == false)
            $range .= '/32';

        // $range is in IP/CIDR format eg 127.0.0.1/24
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }

    public static function CheckCloudFlare($ip) {
        $cf_ips = array(
            '173.245.48.0/20',
            '103.21.244.0/22',
            '103.22.200.0/22',
            '103.31.4.0/22',
            '141.101.64.0/18',
            '108.162.192.0/18',
            '190.93.240.0/20',
            '188.114.96.0/20',
            '197.234.240.0/22',
            '198.41.128.0/17',
            '162.158.0.0/15',
            '104.16.0.0/13',
            '104.24.0.0/14',
            '172.64.0.0/13',
            '131.0.72.0/22'
        );
        $is_cf_ip = false;
        foreach ($cf_ips as $cf_ip) {
            if (self::ip_in_range($ip, $cf_ip)) {

                $is_cf_ip = true;
                break;
            }
        } return $is_cf_ip;
    }

    public static function in_array_all($needles, $haystack) {
        return empty(array_diff($needles, $haystack));
    }

    public static function hasOrgAdminAccess($userArray) {
        foreach ($userArray as $org) {
            if ($org['OrgAdmin'] == 1) {
                return true;
            }
        }
        return false;
    }


    static function getDomainIP($domain) {
        // Validate domain format
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            return "Invalid domain format";
        }

        $result = [];

        try {
            // Method 1: Get single IP using gethostbyname()
            $ip = gethostbyname($domain);
            if ($ip !== $domain) {
                $result['primary_ip'] = $ip;
            }

            // Method 2: Get all DNS A records
            $dns_records = dns_get_record($domain, DNS_A);
            if (!empty($dns_records)) {
                $result['dns_records'] = array_column($dns_records, 'ip');
            }

            // Method 3: Get all IPv4 addresses
            if (checkdnsrr($domain, 'A')) {
                $ip_array = gethostbynamel($domain);
                if ($ip_array) {
                    $result['all_ipv4'] = $ip_array;
                }
            }

            if (empty($result)) {
                return "Could not resolve domain";
            }

            return $result;

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }



} // End Class