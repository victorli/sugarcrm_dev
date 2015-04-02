<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once('modules/Users/User.php');
/**
 * @ticket 51371
 */
class Bug51371Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function getHashes()
    {
        $checks = array(
            // plain MD5
            array("my passw0rd", "0db22d09a263d458c79581aefcbdb300"),
           // whatever User has
            array("my passw0rd", User::getPasswordHash("my passw0rd")),
            );
       if(defined('CRYPT_EXT_DES') && constant('CRYPT_EXT_DES')) {
            // extended crypt
            $checks[] = array("my passw0rd", "_.012saltIO.319ikKPU");
       }
       if(defined('CRYPT_MD5') && constant('CRYPT_MD5')) {
            // md5 crypt
            $checks[] = array("my passw0rd", '$1$F0l3iEs7$sT3th960AcuSzp9kiSmxh/');
       }
       if(defined('CRYPT_BLOWFISH') && constant('CRYPT_BLOWFISH')) {
            // blowfish
            $checks[] = array("my passw0rd", '$2a$07$usesomesillystringforeETvnK0/TgBVIVHViQjGDve4qlnRzeWS');
       }
       if(defined('CRYPT_SHA256') && constant('CRYPT_SHA256')) {
            // sha-256
            $checks[] = array("my passw0rd", '$5$rounds=5000$usesomesillystri$aKwd34p0LSvMZdW1LolZOPCCsx1mYdTynQn9ZrWrO87');
       }
       return $checks;
    }

    /**
     * @dataProvider getHashes
     */
    public function testUserhash($password, $user_hash)
    {
        $this->assertTrue(User::checkPassword($password, $user_hash));
    }
}