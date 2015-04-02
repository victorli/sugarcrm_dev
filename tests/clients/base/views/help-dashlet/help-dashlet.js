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
describe('Base.View.HelpDashlet', function() {
    var app,
        view,
        testObj,
        testModule = 'Accounts',
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();

        testObj = {
            title: 'testTitle',
            body: 'testBody',
            more_help: 'testMoreHelp'
        };

        sandbox.stub(app.lang, 'get', function(label, module) {
            var obj = {
                LBL_HELP_RECORD_TITLE: testObj.title,
                LBL_HELP_RECORD: testObj.body,
                LBL_HELP_MORE_INFO: testObj.more_help
            };
            return (obj[label]) ? obj[label] : label;
        });

        var context = app.context.getContext({
            module: 'help-dashlet',
            layout: 'dashlet'
        });
        context.parent = app.context.getContext({
            module: testModule,
            layout: 'record'
        });
        context.prepare();
        context.parent.prepare();

        var layout = app.view.createLayout({
            name: 'dashlet',
            context: context
        });

        var meta = {
            config: false,
            label: 'LBL_TEST_LBL'
        };

        view = SugarTest.createView('base', null, 'help-dashlet', meta, context, false, layout, true);
    });

    afterEach(function() {
        sandbox.restore();
        testObj = null;
        app = null;
    });

    describe('getHelpObject()', function() {
        describe('with proper help values', function() {
            beforeEach(function() {
                view.getHelpObject();
            });

            it('should set the helpObject correctly', function() {
                expect(view.helpObject).not.toBeEmpty();
            });

            it('should set the helpObject.title correctly', function() {
                expect(view.helpObject.title).not.toBeEmpty();
                expect(view.helpObject.title).toEqual(testObj.title);
            });

            it('should set the helpObject.body correctly', function() {
                expect(view.helpObject.body).not.toBeEmpty();
                expect(view.helpObject.body).toEqual(testObj.body);
            });

            it('should set the helpObject.more_help correctly', function() {
                expect(view.helpObject.more_help).not.toBeEmpty();
                expect(view.helpObject.more_help).toEqual(testObj.more_help);
            });
        });

        describe('with missing help title', function() {
            it('should use meta.label for helpObject.title', function() {
                testObj.title = '';
                view.getHelpObject();
                expect(view.helpObject.title).toEqual('LBL_TEST_LBL');
            });
        });

        describe('when meta.preview is true', function() {
            beforeEach(function() {
                sinon.collection.spy(app.help, 'get');
            });

            afterEach(function() {
                sinon.collection.restore();
            });

            it('will call app.help.get with preview for layout', function() {
                view.meta.preview = true;
                sinon.collection.stub(view, 'createMoreHelpLink', function() {
                    return '<a>';
                });
                view.getHelpObject();
                expect(app.help.get).toHaveBeenCalledWith('Accounts', 'preview', {
                    more_info_url: '<a>',
                    more_info_url_close: '</a>'
                });
            });
        });
    });
});
