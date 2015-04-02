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
describe('RevenueLineItems.Base.Field.ConvertToQuote', function() {
    var app, field, moduleName = 'RevenueLineItems', context, def, fieldModel, message, sandbox;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('alert', 'view', 'base', 'error');

        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'view', 'alert');

        SugarTest.testMetadata.set();

        app = SUGAR.App;
        context = app.context.getContext();
        def = {
            type: 'convert-to-quote',
            event: 'button:convert_to_quote:click',
            name: 'convert_to_quote_button',
            label: 'LBL_CONVERT_TO_QUOTE',
            acl_module: moduleName
        };

        fieldModel = new Backbone.Model({
            id: 'aaa',
            name: 'boo',
            module: moduleName
        });

        field = SugarTest.createField({
            name: 'convert-to-quote',
            type: 'convert-to-quote',
            viewName: 'detail',
            fieldDef: def,
            module: moduleName,
            model: fieldModel,
            loadFromModule: true
        });
    });

    afterEach(function() {
        field = null;
        app = null;
        context = null;
        def = null;
        fieldModel = null;
        message = null;
        sandbox.restore();
    });

    describe('_toggleDisable', function() {
        beforeEach(function() {
            field.render();
        });

        it('will set disabled class on the field element', function() {
            field.model.set('quote_id', 'my_new_quote', {silent: true});
            field._toggleDisable();
            expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();
        });

        it('will remove disabled class when quote_id changes to empty', function() {
            field.model.set('quote_id', 'my_new_quote', {silent: true});
            field._toggleDisable();
            expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();

            field.model.set('quote_id', '');
            expect(field.getFieldElement().hasClass('disabled')).toBeFalsy();
        });

        it('will remove disabled class when quote_id is unset', function() {
            field.model.set('quote_id', 'my_new_quote', {silent: true});
            field._toggleDisable();
            expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();

            field.model.unset('quote_id');
            expect(field.getFieldElement().hasClass('disabled')).toBeFalsy();
        });
    });

    describe('convertToQuote', function() {
        beforeEach(function() {
            sandbox.stub(app.alert, 'show').returns({
                getCloseSelector: function() {
                    return {
                        remove: function() {}
                    };
                }
            });
            sandbox.stub(app.lang, 'get').returnsArg(0);
        });

        it('will display an alert when product_template_id is empty but category_id is not', function() {
            field.model.set({
                category_id: 'category_id'
            });

            field.convertToQuote();

            expect(app.alert.show).toHaveBeenCalledWith(
                'invalid_items',
                {
                    level: 'error',
                    title: 'LBL_ALERT_TITLE_ERROR:',
                    messages: ['LBL_CONVERT_INVALID_RLI_PRODUCT']
                }
            );
        });

        it('will make xhr call', function() {
            sandbox.stub(app.api, 'call');
            field.convertToQuote();

            expect(app.alert.show).toHaveBeenCalledWith(
                'info_quote',
                {
                    level: 'info',
                    autoClose: false,
                    closeable: false,
                    title: 'LBL_CONVERT_TO_QUOTE_INFO:',
                    messages: ['LBL_CONVERT_TO_QUOTE_INFO_MESSAGE']
                }
            );

            expect(app.api.call).toHaveBeenCalled();
        });
    });
});
