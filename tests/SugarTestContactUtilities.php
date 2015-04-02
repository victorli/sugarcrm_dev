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

require_once 'modules/Contacts/Contact.php';

class SugarTestContactUtilities
{
    private static $_createdContacts = array();

    private function __construct() {}

    /**
     *
     * @param string $id
     * @param array $contactValues
     * @return Contact
     */
    public static function createContact($id = '', $contactValues = array(), $class = 'Contact')
    {
        $time = mt_rand();
        $contact = new $class();

        if (isset($contactValues['first_name'])) {
            $contact->first_name = $contactValues['first_name'];
        } else {
            $contact->first_name = 'SugarContactFirst' . $time;
        }
        if (isset($contactValues['last_name'])) {
            $contact->last_name = $contactValues['last_name'];
        } else {
            $contact->last_name = 'SugarContactLast';
        }
        if (isset($contactValues['email'])) {
            $contact->email1 = $contactValues['email'];
        } else {
            $contact->email1 = 'contact@'. $time. 'sugar.com';
        }

        if(!empty($id))
        {
            $contact->new_with_id = true;
            $contact->id = $id;
        }
        $contact->save();
        $GLOBALS['db']->commit();
        self::$_createdContacts[] = $contact;
        return $contact;
    }

    public static function setCreatedContact($contact_ids) {
    	foreach($contact_ids as $contact_id) {
    		$contact = new Contact();
    		$contact->id = $contact_id;
        	self::$_createdContacts[] = $contact;
    	} // foreach
    } // fn

    public static function removeAllCreatedContacts()
    {
        $contact_ids = self::getCreatedContactIds();
        if ($contact_ids) {
            $GLOBALS['db']->query('DELETE FROM contacts WHERE id IN (\'' . implode("', '", $contact_ids) . '\')');
        }
    }

    /**
     * removeCreatedContactsEmailAddresses
     *
     * This function removes email addresses that may have been associated with the contacts created
     *
     * @static
     * @return void
     */
    public static function removeCreatedContactsEmailAddresses()
    {
        $contact_ids = self::getCreatedContactIds();
        if ($contact_ids) {
            $GLOBALS['db']->query('DELETE FROM email_addresses WHERE id IN (SELECT DISTINCT email_address_id FROM email_addr_bean_rel WHERE bean_module =\'Contacts\' AND bean_id IN (\'' .
                implode("', '", $contact_ids) . '\'))');
            $GLOBALS['db']->query('DELETE FROM emails_beans WHERE bean_module=\'Contacts\' AND bean_id IN (\'' .
                implode("', '", $contact_ids) . '\')');
            $GLOBALS['db']->query('DELETE FROM email_addr_bean_rel WHERE bean_module=\'Contacts\' AND bean_id IN (\'' .
                implode("', '", $contact_ids) . '\')');
        }
    }

    public static function removeCreatedContactsUsersRelationships()
    {
        $contact_ids = self::getCreatedContactIds();
        if ($contact_ids) {
            $GLOBALS['db']->query('DELETE FROM contacts_users WHERE contact_id IN (\'' . implode("', '", $contact_ids) .
                '\')');
        }
    }

    public static function getCreatedContactIds()
    {
        $contact_ids = array();
        foreach (self::$_createdContacts as $contact) {
            $contact_ids[] = $contact->id;
        }
        return $contact_ids;
    }
}


class ContactMock extends Contact
{
    public function getNotificationEmailTemplate($test = false)
    {
        if ($test) {
            $templateName = $this->getTemplateNameForNotificationEmail();
            return $this->createNotificationEmailTemplate($templateName);    
        }
        
        return $this->createNotificationEmailTemplate($templateName);

    }
}
