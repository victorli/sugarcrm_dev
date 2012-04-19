<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2012 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


require_once 'include/Smarty/plugins/function.sugar_button.php';
require_once 'include/Smarty/plugins/function.sugar_menu.php';
require_once 'include/SugarHtml/SugarHtml.php';
require_once 'include/Sugar_Smarty.php';

class FunctionSugarButtonTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_smarty = new Sugar_Smarty;

    }

    public function providerCustomCode() {
        $onclick = 'this.form.module.value=\'Contacts\';';
        $expected_onclick = 'var _form = document.getElementById(\'DetailView\');_form.module.value=\'Contacts\';_form.submit();';
        return array(

            //set #0: simple input code
            array(
                '<input type="submit" value="{$APP.BUTTON_LABEL}" onclick="'.$onclick.'">',
                array(
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'onclick' => $onclick,
                    'value' => '{$APP.BUTTON_LABEL}'
                ),
                '<input type="button" value="{$APP.BUTTON_LABEL}" onclick="'.$expected_onclick.'"/>',
            ),
            //set #1:
            array(
                '<input type="submit" disabled value="{$APP.BUTTON_LABEL}" onclick="'.$onclick.'">',
                array(
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'onclick' => $onclick,
                    'value' => '{$APP.BUTTON_LABEL}',
                    'disabled' => '',
                ),
                '<input type="button" disabled value="{$APP.BUTTON_LABEL}" onclick="'.$expected_onclick.'"/>',
            ),

            //set #2: custom code contains smarty conditional statement
            array(
                '<input type="submit" value="{$APP.BUTTON_LABEL}" onclick="{if $bean->access(\'edit\')}'.$onclick.'{/if}">',
                array(
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'onclick' => '{if $bean->access(\'edit\')}'.$onclick.'{/if}',
                    'value' => '{$APP.BUTTON_LABEL}'
                ),
                '<input type="button" value="{$APP.BUTTON_LABEL}" onclick="var _form = document.getElementById(\'DetailView\');{if $bean->access(\'edit\')}_form.module.value=\'Contacts\';{/if};_form.submit();"/>',
            ),
            //set #3: attributes wrapped with smarty
            array(
                //custom code
                '<input {if $bean->access(\'edit\')}type="submit"{else}type="hidden"{/if} value="{$APP.BUTTON_LABEL}" {if $bean->access(\'edit\')}onclick="'.$onclick.'"{else}onclick="alert(\'nope\');"{/if} id="button_submit">',
                //parsed array
                array(
                    'id' => 'button_submit',
                    'tag' => 'input',
                    'self_closing' => true,
                    'smarty' => array(
                        array(
                            'template' => '{if $bean->access(\'edit\')}[CONTENT0]{else}[CONTENT1]{/if}',
                            '[CONTENT0]' => array(
                                'type' => 'submit'
                            ),
                            '[CONTENT1]' => array(
                                'type' => 'hidden'
                            )
                        ),
                        array(
                            'template' => '{if $bean->access(\'edit\')}[CONTENT0]{else}[CONTENT1]{/if}',
                            '[CONTENT0]' => array(
                                'onclick' => $onclick
                            ),
                            '[CONTENT1]' => array(
                                'onclick' => 'alert(\'nope\');'
                            )
                        )
                    ),

                    'value' => '{$APP.BUTTON_LABEL}'
                ),
                '<input {if $bean->access(\'edit\')}type="submit"{else}type="hidden"{/if} {if $bean->access(\'edit\')}onclick="'.$expected_onclick.'"{else}onclick="alert(\'nope\');"{/if} value="{$APP.BUTTON_LABEL}" id="button_submit"/>',
            ),

            //set #4: attributes wrapped with smarty
            array(
                //custom code
                '<input type="submit"{if $bean->access(\'edit\')}onclick="'.$onclick.'"{else}onclick="this.form.module.value=\'{$APP.MODULE}\';this.form.action.value=\'{$APP.ACTION}\';"{/if} value="{$APP.BUTTON_LABEL}" id="button_submit">',
                //parsed array
                array(
                    'id' => 'button_submit',
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'smarty' => array(
                        array(
                            'template' => '{if $bean->access(\'edit\')}[CONTENT0]{else}[CONTENT1]{/if}',
                            '[CONTENT0]' => array(
                                'onclick' => $onclick
                            ),
                            '[CONTENT1]' => array(
                                'onclick' => 'this.form.module.value=\'{$APP.MODULE}\';this.form.action.value=\'{$APP.ACTION}\';'
                            )
                        )
                    ),

                    'value' => '{$APP.BUTTON_LABEL}'
                ),
                '<input {if $bean->access(\'edit\')}onclick="'.$expected_onclick.'"{else}onclick="var _form = document.getElementById(\'DetailView\');_form.module.value=\'{$APP.MODULE}\';_form.action.value=\'{$APP.ACTION}\';_form.submit();"{/if} type="button" value="{$APP.BUTTON_LABEL}" id="button_submit"/>',
            ),

            //set #5: recursive smarty wrapper within the attributes
            array(
                '<input type="submit" value="{$APP.BUTTON_LABEL}" {$APP.DISABLED} {if $bean->access(\'edit\')}{if $APP.CONTAINER = true}onclick="'.$onclick.'"{/if}{else if $bean->access(\'delete\') }onclick="del();"{else}onclick="alert(\'nope\');"{/if} id="button_submit">',
                array(
                    'id' => 'button_submit',
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'value' => '{$APP.BUTTON_LABEL}',
                    'smarty' => array(
                        array(
                            'template' => '{$APP.DISABLED}'
                        ),
                        array(
                            'template' => '{if $bean->access(\'edit\')}[CONTENT0]{else if $bean->access(\'delete\') }[CONTENT1]{else}[CONTENT2]{/if}',
                            '[CONTENT0]' => array(
                                'smarty' => array(
                                    array(
                                        'template' => '{if $APP.CONTAINER = true}[CONTENT0]{/if}',
                                        '[CONTENT0]' => array(
                                            'onclick' => $onclick
                                        ),
                                    )
                                ),
                            ),
                            '[CONTENT1]' => array(
                                'onclick' => 'del();'
                            ),
                            '[CONTENT2]' => array(
                                'onclick' => 'alert(\'nope\');'
                            ),
                        ),
                    ),
                ),
                '<input {$APP.DISABLED} {if $bean->access(\'edit\')}{if $APP.CONTAINER = true}onclick="'.$expected_onclick.'"{/if}{else if $bean->access(\'delete\') }onclick="del();"{else}onclick="alert(\'nope\');"{/if} type="button" value="{$APP.BUTTON_LABEL}" id="button_submit"/>',
            ),
            //set #6: Begins with smarty conditional statement
            array(
                '{if $fields.status.value != "Held"} <input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" class="button" onclick="'.$onclick.'" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"  type="submit">{/if}',
                array(
                    'smarty' => array(
                        array(
                            'template' => '{if $fields.status.value != "Held"}[CONTENT0]{/if}',
                            '[CONTENT0]' => array(
                                'tag' => 'input',
                                'type' => 'submit',
                                'self_closing' => true,
                                'title' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
                                'class' => 'button',
                                'onclick' => $onclick,
                                'value' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}'
                            ),
                        )
                    ),
                ),
                '{if $fields.status.value != "Held"}<input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" class="button" onclick="'.$expected_onclick.'" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" type="button"/>{/if}'
            ),
            //set #7: Begins with smarty conditional statement and contains recursive conditional statement inside the context
            array(
                '{ if($fields.status.value != "Held") } <input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" {if $bean->access(\'edit\')}{if $APP.CONTAINER = true}onclick="'.$onclick.'"{/if}{else if $bean->access(\'delete\') }onclick="del();"{else}onclick="alert(\'nope\');"{/if}  class="button" onclick="'.$onclick.'" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"  type="submit">{/if}',
                array(
                    'smarty' => array(
                        array(
                            'template' => '{ if($fields.status.value != "Held") }[CONTENT0]{/if}',
                            '[CONTENT0]' => array(
                                'tag' => 'input',
                                'type' => 'submit',
                                'class' => 'button',
                                'value' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
                                'onclick' => $onclick,
                                'self_closing' => true,
                                'title' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
                                'smarty' => array(
                                    array(
                                        'template' => '{if $bean->access(\'edit\')}[CONTENT0]{else if $bean->access(\'delete\') }[CONTENT1]{else}[CONTENT2]{/if}',
                                        '[CONTENT0]' => array(
                                            'smarty' => array(
                                                array(
                                                    'template' => '{if $APP.CONTAINER = true}[CONTENT0]{/if}',
                                                    '[CONTENT0]' => array(
                                                        'onclick' => $onclick
                                                    ),
                                                )
                                            ),
                                        ),
                                        '[CONTENT1]' => array(
                                            'onclick' => 'del();'
                                        ),
                                        '[CONTENT2]' => array(
                                            'onclick' => 'alert(\'nope\');'
                                        ),
                                    )
                                ),

                            ),
                        )
                    ),
                ),
                '{ if($fields.status.value != "Held") }<input {if $bean->access(\'edit\')}{if $APP.CONTAINER = true}onclick="'.$expected_onclick.'"{/if}{else if $bean->access(\'delete\') }onclick="del();"{else}onclick="alert(\'nope\');"{/if} title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" class="button" onclick="'.$expected_onclick.'" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" type="button"/>{/if}'
            ),
            //set #8: The submit button is encapsulated with another form
            array(
                '<form name="blah">   <input type="hidden" name="id1">    <input type="hidden" name="id2">     <input type="submit" onclick="'.$onclick.'"></form>',
                array(
                    'tag' => 'form',
                    'name' => 'blah',
                    'self_closing' => false,
                    'container' => array(
                        array(
                            'tag' => 'input',
                            'type' => 'hidden',
                            'name' => 'id1',
                            'self_closing' => true,
                        ),
                        array(
                            'tag' => 'input',
                            'type' => 'hidden',
                            'name' => 'id2',
                            'self_closing' => true,
                        ),
                        array(
                            'tag' => 'input',
                            'type' => 'submit',
                            'onclick' => $onclick,
                            'self_closing' => true,
                        ),
                    ),
                ),
                '<form name="blah"><input type="hidden" name="id1"/><input type="hidden" name="id2"/><input type="submit" onclick="'.$onclick.'"/></form>',
            ),

            //set #9: custom code encapsulated smarty conditional statement, and contains additional hidden fields
            array(
                '{if $fields.status.value != "Held"} <input type="hidden" name="id1" value="true">    <input type="hidden" name="id2">     <input type="submit" {if $APP.CONTAINER = true}onclick="'.$onclick.'"{else}onclick="stop();"{/if} value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}">{/if}',
                array(
                    'smarty' => array(
                        array(
                            'template' => '{if $fields.status.value != "Held"}[CONTENT0]{/if}',
                            '[CONTENT0]' => array(
                                array(
                                    'tag' => 'input',
                                    'type' => 'hidden',
                                    'name' => 'id1',
                                    'self_closing' => true,
                                    'value' => "true"
                                ),
                                array(
                                    'tag' => 'input',
                                    'type' => 'hidden',
                                    'name' => 'id2',
                                    'self_closing' => true,
                                ),
                                array(
                                    'tag' => 'input',
                                    'type' => 'submit',
                                    'value' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
                                    'self_closing' => true,
                                    'smarty' => array(
                                        array(
                                            'template' => '{if $APP.CONTAINER = true}[CONTENT0]{else}[CONTENT1]{/if}',
                                            '[CONTENT0]' => array(
                                                'onclick' => $onclick
                                            ),
                                            '[CONTENT1]' => array(
                                                'onclick' => 'stop();'
                                            ),
                                        )
                                    ),
                                ),
                            ),
                        )
                    ),
                ),
                '{if $fields.status.value != "Held"}<input type="hidden" name="id1" value="true"/><input type="hidden" name="id2"/><input {if $APP.CONTAINER = true}onclick="'.$expected_onclick.'"{else}onclick="stop();"{/if} type="button" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"/>{/if}'
            ),
            //set #10: empty spaces after the equal sign
            array (
                '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" id= "SAVE" disabled onclick="SUGAR.meetings.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();"type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">',
                array(
                    'tag' => 'input',
                    'title' => '{$APP.LBL_SAVE_BUTTON_TITLE}',
                    'id' => 'SAVE',
                    'disabled' => '',
                    'onclick' => 'SUGAR.meetings.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();',
                    'type' => 'button',
                    'name' => "button",
                    'value' => '{$APP.LBL_SAVE_BUTTON_LABEL}',
                    'self_closing' => true,
                ),
                '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" id="SAVE" disabled onclick="SUGAR.meetings.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}"/>',
            ),
            //set #11: empty spaces before the equal sign
            array (
                '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" id    = "SAVE" disabled onclick="SUGAR.meetings.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();"type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">',
                array(
                    'tag' => 'input',
                    'title' => '{$APP.LBL_SAVE_BUTTON_TITLE}',
                    'id' => 'SAVE',
                    'disabled' => '',
                    'onclick' => 'SUGAR.meetings.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();',
                    'type' => 'button',
                    'name' => "button",
                    'value' => '{$APP.LBL_SAVE_BUTTON_LABEL}',
                    'self_closing' => true,
                ),
                '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" id="SAVE" disabled onclick="SUGAR.meetings.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}"/>',
            ),
        );
    }
    /**
     * @dataProvider providerCustomCode
     */
    public function testCustomCode($customCode, $expected_parsed_array, $expected_customCode)
    {
        //Test for parseHtmlTag
        $this->assertEquals($expected_parsed_array, SugarHtml::parseHtmlTag($customCode));
        $params = array(
            'module' => 'Accounts',
            'view' => 'DetailView',
            'id' => array(
                'customCode' => $customCode
            ),
            'form_id' => 'DetailView'
        );

        //Test for smarty_function_sugar_button for customCode
        $this->assertEquals($expected_customCode, smarty_function_sugar_button($params, $this->_smarty));

    }

    public function providerCustomCodeWithHidden() {

        $onclick = 'this.form.module.value=\'Contacts\';this.form.action.value=\'DetailView\';';
        $expected_onclick = 'var _form = document.getElementById(\'DetailView\');_form.module.value=\'Contacts\';_form.action.value=\'DetailView\';_form.submit();';

        return array(
            //set #0: Button with hidden field
            array(
                '<input type="hidden" name="id2" value="2">     <input type="submit"onclick="'.$onclick.'"value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}">',
                '<input type="hidden" name="id2" value="2"/><input type="button" onclick="'.$expected_onclick.'" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"/>',
                array(
                    '<input type="hidden" name="id2" value="2"/>'
                ),
            ),
            //set #1: Button with hidden field wrapping with conditional smarty statement
            array(
                '{if $fields.status.value != "Held"} <input type="hidden" name="id1" value="true">    <input type="hidden" name="id2">     <input type="submit" {if $APP.CONTAINER = true}onclick="'.$onclick.'"{else}onclick="stop();"{/if} value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}">{/if}',
                '{if $fields.status.value != "Held"}<input type="hidden" name="id1" value="true"/><input type="hidden" name="id2"/><input {if $APP.CONTAINER = true}onclick="'.$expected_onclick.'"{else}onclick="stop();"{/if} type="button" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"/>{/if}',
                array(
                    '{if $fields.status.value != "Held"}<input type="hidden" name="id1" value="true"/><input type="hidden" name="id2"/>{/if}',
                ),
            ),
            //set #2: wrapping with conditional smarty statement great equal than two phases
            array(
                '{if $fields.status.value != "Held"}<input type="hidden" name="id1" value="true"><input type="hidden" name="id2"><input type="submit" onclick="'.$onclick.'">{else}<input type="hidden" name="id3" value="true"><input type="submit" onclick="'.$onclick.'">{/if}',
                '{if $fields.status.value != "Held"}<input type="hidden" name="id1" value="true"/><input type="hidden" name="id2"/><input type="button" onclick="'.$expected_onclick.'"/>{else}<input type="hidden" name="id3" value="true"/><input type="button" onclick="'.$expected_onclick.'"/>{/if}',
                array(
                    '{if $fields.status.value != "Held"}<input type="hidden" name="id1" value="true"/><input type="hidden" name="id2"/>{else}<input type="hidden" name="id3" value="true"/>{/if}',
                ),
            ),

            //set #3: hidden fields wrapped with the additional form element
            array(
                '<form name="blah">   <input type="hidden" name="id1">    <input type="hidden" name="id2">     <input type="submit" onclick="'.$onclick.'"></form>',
                '<form name="blah"><input type="hidden" name="id1"/><input type="hidden" name="id2"/><input type="submit" onclick="'.$onclick.'"/></form>',
                null
            )
        );
    }
    /**
     * @dataProvider providerCustomCodeWithHidden
     */
    public function testCustomCodeWithHidden($customCode, $expected_customCode, $expected_hidden_array) {

        $params = array(
            'module' => 'Accounts',
            'view' => 'DetailView',
            'id' => array(
                'customCode' => $customCode
            ),
            'form_id' => 'DetailView'
        );
        $this->assertEquals($expected_customCode, smarty_function_sugar_button($params, $this->_smarty));
        $form = $this->_smarty->get_template_vars('form');

        $this->assertEquals($expected_hidden_array, $form['hidden']);

    }

    public function testBuildSugarHtml() {
        $sugar_html = array(
            'type' => 'submit',
            'value' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
            'htmlOptions' => array(
                'title' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
                'name' => 'button',
                'class' => 'button',
                'onclick' => 'this.form.isSaveFromDetailView.value=true; this.form.status.value=\'Held\'; this.form.action.value=\'Save\';this.form.return_module.value=\'Meetings\';this.form.isDuplicate.value=true;this.form.isSaveAndNew.value=true;this.form.return_action.value=\'EditView\'; this.form.isDuplicate.value=true;this.form.return_id.value=\'{$fields.id.value}\';',

            ),
            'template' => '{if $fields.status.value != "Held"}[CONTENT]{/if}',
        );
        $expected_html = '{if $fields.status.value != "Held"}<input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" name="button" class="button" onclick="var _form = document.getElementById(\'DetailView\');_form.isSaveFromDetailView.value=true; _form.status.value=\'Held\'; _form.action.value=\'Save\';_form.return_module.value=\'Meetings\';_form.isDuplicate.value=true;_form.isSaveAndNew.value=true;_form.return_action.value=\'EditView\'; _form.isDuplicate.value=true;_form.return_id.value=\'{$fields.id.value}\';_form.submit();" type="button" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"/>{/if}';

        $params = array(
            'module' => 'Accounts',
            'view' => 'DetailView',
            'id' => array(
                'sugar_html' => $sugar_html
            ),
            'form_id' => 'DetailView'
        );
        //Test for smarty_function_sugar_button for sugar_html
        $this->assertEquals($expected_html, smarty_function_sugar_button($params, $this->_smarty));

    }
}