<?php
class_exists('CApi') or die();
CApi::Inc('common.plugins.change-password');

class CCustomChangePasswordPlugin extends AApiChangePasswordPlugin
{
    /**
     * @param CApiPluginManager $oPluginManager
     */
    public function __construct(CApiPluginManager $oPluginManager)
    {
    parent::__construct('1.0', $oPluginManager);
    }
    /**
     * @param CAccount $oAccount
     * @return bool
     */
    public function validateIfAccountCanChangePassword($oAccount)
    {
    $bResult = false;
    if ($oAccount instanceof CAccount)
    {
        $bResult = true;
    }
    return $bResult;
    }
    /**
     * @param CAccount $oAccount
     * @return bool
     */
    public function ChangePasswordProcess($oAccount)
    {
    $bResult = true;
    if (0 < strlen($oAccount->PreviousMailPassword) &&
        $oAccount->PreviousMailPassword !== $oAccount->IncomingMailPassword)
    {
    $username = $oAccount->Email;
    $password = $oAccount->PreviousMailPassword; 
    $newpassword = $oAccount->IncomingMailPassword;
    $loginUrl = 'https://yourdomain/postfixadmin/users/login.php';
    $changeUrl = 'https://yourdomain/postfixadmin/users/password.php';

    $ch = curl_init();
 
    curl_setopt($ch, CURLOPT_URL, $loginUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERAGENT,"Webmail-Pro login postfixadmin");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'fUsername='.$username.'&fPassword='.$password);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $store = curl_exec($ch);
    if (strpos($store, 'Your email address or password are not correct') !== false) {
	CApi::Log("curl_exec threw error \"" . curl_error($ch) . "\" for $query");
        $bResult = false;
        curl_close($ch);
	throw new CApiManagerException(Errs::UserManager_AccountOldPasswordNotCorrect);
    }

    curl_setopt($ch, CURLOPT_URL, $changeUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERAGENT,"Webmail-Pro change pass postfixadmin");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'fPassword_current='.$password.'&fPassword='.$newpassword.'&fPassword2='.$newpassword);
    $content = curl_exec($ch);

    if (strpos($content, 'Password is too short') !== false) {
	CApi::Log("curl_exec threw error \"" . curl_error($ch) . "\" for $query");
	$bResult = false;
	curl_close($ch);
	throw new CApiManagerException(Errs::UserManager_AccountNewPasswordRejected);
	$bResult = false;
    }

    if (strpos($content, 'Your password must contain at least 2 digit') !== false) {
	CApi::Log("curl_exec threw error \"" . curl_error($ch) . "\" for $query");
	$bResult = false;
	curl_close($ch);
	throw new CApiManagerException(Errs::UserManager_AccountNewPasswordRejected);
    }

    }

    return $bResult;
    }
}

return new CCustomChangePasswordPlugin($this);
