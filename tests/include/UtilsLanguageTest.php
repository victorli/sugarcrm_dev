<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'include/utils.php';

/**
 * utils.php language tests
 */
class UtilsLanguageTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $backup = array(
        'default_language',
        'disabled_languages',
        'languages'
    );

    public function setUp()
    {
        global $sugar_config;

        foreach ($this->backup as $var) {
            if (!empty($sugar_config[$var])) {
                $this->$var = $sugar_config[$var];
            }
        }

        $sugar_config['default_language'] = 'fr_FR';
        $sugar_config['disabled_languages'] = 'es_ES,fr_FR';
        $sugar_config['languages'] = array(
            'en_us' => 'English (US)',
            'bg_BG' => 'Български',
            'cs_CZ' => 'Česky',
            'da_DK' => 'Dansk',
            'de_DE' => 'Deutsch',
            'el_EL' => 'Ελληνικά',
            'es_ES' => 'Español',
            'fr_FR' => 'Français',
        );
    }

    public function tearDown()
    {
        global $sugar_config;

        foreach ($this->backup as $var) {
            unset($sugar_config[$var]);
            if (!empty($this->$var)) {
                $sugar_config[$var] = $this->$var;
            }
        }
    }

    /**
     * Make sure get_languages doesn't disable the default language
     */
    public function testGetLanguages()
    {
        global $sugar_config;
        $availableLanguages = get_languages();

        $this->assertNotEquals(
            false,
            array_key_exists($sugar_config['default_language'], $availableLanguages),
            'Default language is disabled'
        );
    }
}
