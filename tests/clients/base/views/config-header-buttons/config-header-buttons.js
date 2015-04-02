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
describe('Base.View.ConfigHeaderButtons', function() {
    var app,
        context,
        view,
        options;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());

        options = {
            context: context
        };

        sinon.collection.stub(app.controller.context, 'get').withArgs('module').returns('Opportunities');
        sinon.collection.stub(app.lang, 'getModuleName').withArgs('Opportunities', {plural: true}).returns('Opps');

        view = SugarTest.createView('base', null, 'config-header-buttons');
    });

    afterEach(function() {
        sinon.collection.restore();
        view = null;
    });

    it('will have custom module name in moduleLangObj', function() {
        expect(view.moduleLangObj.module).toBe('Opps');
    });

    describe('cancelConfig()', function() {
        beforeEach(function() {
            sinon.collection.stub(app.router, 'goBack', function() {});
        });
        it('if app.drawer exists, should call app.drawer.close()', function() {
            app.drawer = {
                close: function() {}
            };
            sinon.collection.spy(app.drawer, 'close');
            view.cancelConfig();
            expect(app.drawer.close).toHaveBeenCalled();
            delete app.drawer;
        });

        describe('if app.drawer does not exists', function() {
            it('should call app.router.goBack()', function() {
                view.cancelConfig();
                expect(app.router.goBack).toHaveBeenCalled();
            });
        });
    });

    describe('saveConfig()', function() {
        var button;
        beforeEach(function() {
            button = SugarTest.createField({
                client: 'base',
                name: 'save_button',
                type: 'button',
                viewName: 'detail',
                fieldDef: {
                    label: 'LBL_SAVE_BUTTON_LABEL'
                }
            });

            sinon.collection.stub(button, 'setDisabled').withArgs(true).returns(true);
            view.fields['save_button'] = button;
        });

        afterEach(function() {
            button = null;
        });

        it('will disable the save button', function() {
            sinon.collection.stub(view, '_saveConfig');
            view.saveConfig();
            expect(button.setDisabled).toHaveBeenCalledWith(true);
        });

        it('will not disable if beforeSave returns false', function() {
            sinon.collection.stub(view, 'triggerBefore').returns(false);
            view.saveConfig();
            expect(button.setDisabled).not.toHaveBeenCalled();
        });
    });

    describe('_saveConfig()', function() {
        var button;
        beforeEach(function() {
            button = SugarTest.createField({
                client: 'base',
                name: 'save_button',
                type: 'button',
                viewName: 'detail',
                fieldDef: {
                    label: 'LBL_SAVE_BUTTON_LABEL'
                }
            });

            sinon.collection.stub(button, 'setDisabled').withArgs(false).returns(true);
            view.fields['save_button'] = button;
        });

        afterEach(function() {
            button = null;
        });

        it('on xhr error will enable the button', function() {
            sinon.collection.stub(app.api, 'call', function(method, url, data, callbacks) {
                callbacks.error({});
            });

            view._saveConfig();
            expect(button.setDisabled).toHaveBeenCalledWith(false);
        });
    });
});
