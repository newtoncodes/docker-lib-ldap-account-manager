<?php

define('LDAP_DOMAIN', 'ldap.newton.codes');
define('LDAP_HOST', 'ldap');
define('LDAP_PORT', '389');
define('LDAP_DC', 'dc=ldap,dc=newton,dc=codes');
define('LDAP_USERS_DN', 'ou=users,dc=ldap,dc=newton,dc=codes');
define('LDAP_SEARCH_DN', 'cn=admin,dc=ldap,dc=newton,dc=codes');
define('LDAP_SEARCH_PASSWORD', 'admin');

error_reporting(0);

function hashPassword($password) {
    $salt = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',4)),0,4);
    return '{SSHA}' . base64_encode(sha1( $password.$salt, TRUE ). $salt);
}

function changePassword($user, $oldPassword, $newPassword, $newPasswordCnf) {
    ldap_connect(LDAP_HOST, LDAP_PORT);
    $con = ldap_connect(LDAP_HOST);

    if (!$con) return ['Cannot connect to LDAP server.'];

    ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($con, LDAP_OPT_REFERRALS, 0);

//    $bind = ldap_bind($con, LDAP_SEARCH_DN, LDAP_SEARCH_PASSWORD);
//    if (!$bind) return ['Cannot bind search user.', ldap_errno($con) . ' - ' . ldap_error($con)];

    $userSearch = ldap_search($con, LDAP_USERS_DN,'(|(uid=' . $user . ')(mail=' . $user . '))');
    $userGet = ldap_get_entries($con, $userSearch);
    print_r($userGet);

    $userDn = ldap_get_dn($con, ldap_first_entry($con, $userSearch));
    $username = $userGet[0]['uid'][0];
    $givenNames = $userGet[0]['givenName'];
    $surNames = $userGet[0]['sn'];
    $mails = $userGet[0]['mail'];
    $passwordRetryCount = $userGet[0]['passwordRetryCount'][0];
    $passwordHistory = $userGet[0]['passwordhistory'][0];

    $mail = isset($mails[0]) ? $mails[0] : '';
    $firstName = isset($givenNames[0]) ? $givenNames[0] : '';
    $lastName = isset($surNames[0]) ? $surNames[0] : '';
    $name = $firstName . ' ' . $lastName;

    echo "MAIL: $mail\n";
    echo "NAME: $name\n";
    echo "PASSWORD: hashPassword($newPassword)\n";

    if ( $passwordRetryCount >= 3 ) {
        return ['Error E101 - Your Account is Locked Out!!!'];
    }
    if (ldap_bind($con, $userDn, $oldPassword) === false) {
        return ['Error E101 - Current Username or Password is wrong.'];
    }
    if ($newPassword !== $newPasswordCnf) {
        return ['Error E102 - Your New passwords do not match!'];
    }
    if ($passwordHistory) {
        return ['Error E102 - Your new password matches one of the last 10 passwords that you used, you MUST come up with a new password.'];
    }
    if (strlen($newPassword) < 8) {
        return ['Error E103 - Your new password is too short.<br/>Your password must be at least 8 characters long.'];
    }
    if (!preg_match('/\d/',$newPassword)) {
        return ['Error E104 - Your new password must contain at least one number.'];
    }
    if (!preg_match('/[a-zA-Z]/',$newPassword)) {
        return ['Error E105 - Your new password must contain at least one letter.'];
    }
    if (!preg_match('/[A-Z]/',$newPassword)) {
        return ['Error E106 - Your new password must contain at least one uppercase letter.'];
    }
    if (!preg_match('/[a-z]/',$newPassword)) {
        return ['Error E107 - Your new password must contain at least one lowercase letter.'];
    }
    if (!$userGet) {
        return ['Error E200 - Unable to connect to server, you may not change your password at this time, sorry.'];
    }

//    $auth = ldap_first_entry($con, $userSearch);
//    $mails = ldap_get_values($con, $auth, 'mail');
//    $givenNames = ldap_get_values($con, $auth, 'givenName');
//    $surNames = ldap_get_values($con, $auth, 'sn');

//    if (ldap_modify($con,$userDn, ['userPassword' => hashPassword($newPassword)]) === false){
//        return [
//            'E201 - Your password cannot be change, please contact the administrator.',
//            ldap_errno($con) . ' - ' . ldap_error($con)
//        ];
//    }
//
//    mail($mail,'Password change notice',$name . ',
//Your password on ' . LDAP_DOMAIN . ' for ' . $username . ' was just changed.
//If you did not make this change, please contact the administrator!
//
//Best regards,
//LDAP server');

    return true;
}


$result = null;
if (isset($_POST['submitted'])) {
    $result = changePassword($_POST['username'], $_POST['oldPassword'], $_POST['newPassword1'], $_POST['newPassword2']);
}

var_dump($result);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Password Change Page</title>
        <style type="text/css">
            body { font-family: Verdana,Arial,Courier New; font-size: 0.7em; }
            th { text-align: right; padding: 0.8em; }
            #container { text-align: center; width: 500px; margin: 5% auto; }
            .msg_yes { margin: 0 auto; text-align: center; color: green; background: #D4EAD4; border: 1px solid green; border-radius: 10px; margin: 2px; }
            .msg_no { margin: 0 auto; text-align: center; color: red; background: #FFF0F0; border: 1px solid red; border-radius: 10px; margin: 2px; }
        </style>
    </head>

    <body>
        <div id="container">
            <h2>Password change</h2>

            <p>
                Your new password must be 8 characters long or longer and have at least:<br/>
                one capital letter, one lowercase letter &amp; one number.<br/>
            </p>

            <?php
                if ($result !== null) {
                    if ($result === true) {
                        echo '<div class="msg_yes"><p>Password was successfully changed.</p></div>';
                    }
                    else {
                        echo '<div class="msg_no"><p>Password was not changed!</p>';
                        foreach ($result as $one) { echo "<p>$one</p>"; }
                        echo '</div>';
                    }
                }
            ?>

            <form action="" name="passwordChange" method="post">
                <table style="width: 400px; margin: 0 auto;">
                    <tr><th>Username or Email Address:</th><td><input name="username" type="text" size="20px" autocomplete="off" /></td></tr>
                    <tr><th>Current password:</th><td><input name="oldPassword" size="20px" type="password" /></td></tr>
                    <tr><th>New password:</th><td><input name="newPassword1" size="20px" type="password" /></td></tr>
                    <tr><th>New password (again):</th><td><input name="newPassword2" size="20px" type="password" /></td></tr>
                    <tr>
                        <td colspan="2" style="text-align: center;" >
                            <input name="submitted" type="submit" value="Change Password" />
                            <input type="reset" value="Cancel" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>
