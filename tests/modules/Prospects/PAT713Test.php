<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'modules/ModuleBuilder/parsers/relationships/OneToManyRelationship.php';

/**
 * One to Many relationship created between Targets module
 * and another module results in "Targets" field in related module cannot be filled.
 * @ticket PAT-713
 * @author bsitnikovski@sugarcrm.com
 */
class BugPAT713Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function modules()
    {
        return array(
            array('Cases'),
            array('Contacts'),
        );
    }

    /**
     * @dataProvider modules
     * @param string $relatedModule
     */
    public function testLinkFieldRname($relatedModule)
    {
        $relationship = new OneToManyRelationship(array(
            'rhs_label' => $relatedModule,
            'lhs_label' => 'Targets',
            'rhs_subpanel' => 'default',
            'lhs_module' => 'Prospects',
            'rhs_module' => $relatedModule,
            'relationship_type' => 'one-to-many',
            'readonly' => false,
            'deleted' => false,
            'relationship_only' => false,
            'for_activities' => false,
            'is_custom' => false,
            'from_studio' => true,
            'relationship_name' => 'prospects_25478',
        ));
        $vardefs   = $relationship->buildVardefs();

        if (isset($vardefs[$relatedModule][1]) &&
            isset($vardefs[$relatedModule][1]['link']) &&
            $vardefs[$relatedModule][1]['type'] == 'relate') {
            $linkField   = $vardefs[$relatedModule][1];
            $relatedBean = BeanFactory::getBean($relatedModule);
            $fieldMap    = array_keys($relatedBean->field_name_map);

            $this->assertContains($linkField['rname'], $fieldMap, 'Rname field does not exist in related module.');

            $fieldDef = $relatedBean->field_name_map[$linkField['rname']];

            $this->assertFalse($fieldDef['type'] == 'relate', 'Related field does not belong to related module.');
        } else {
            $this->fail('Link field not found.');
        }
    }
}
