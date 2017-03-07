<?php

/*
 *
 * Distributed under the terms of the license described in COPYING
 *
 */
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
    $bResult = false;
    if (0 < strlen($oAccount->PreviousMailPassword) &&
        $oAccount->PreviousMailPassword !== $oAccount->IncomingMailPassword)
    {

    $username = $oAccount->Email;
    $password = $oAccount->PreviousMailPassword; 
    $newpassword = $oAccount->IncomingMailPassword;
    $loginUrl = 'http://admin.stsmail.ro/postfixadmin/users/login.php';

    $ch = curl_init();
 
    curl_setopt($ch, CURLOPT_URL, $loginUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERAGENT,"Webmail-Pro testcurl");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'fUsername='.$username.'&fPassword='.$password);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $store = curl_exec($ch);

    curl_setopt($ch, CURLOPT_URL, 'http://admin.stsmail.ro/postfixadmin/users/password.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'fPassword_current='.$password.'&fPassword='.$newpassword.'&fPassword2='.$newpassword);

    $content = curl_exec($ch);

        if ($content == false) {
    CApi::Log("curl_exec threw error \"" . curl_error($ch) . "\" for $query");
    curl_close($ch);
    throw new CApiManagerException(Errs::UserManager_AccountNewPasswordUpdateError);
        } else {
    curl_close($ch);
    $json_res = json_decode($content);
    if (!$json_res->status)
    {
        throw new CApiManagerException(Errs::UserManager_AccountNewPasswordUpdateError);
    }
        }


    }

    return $bResult;
    }
}

return new CCustomChangePasswordPlugin($this);
