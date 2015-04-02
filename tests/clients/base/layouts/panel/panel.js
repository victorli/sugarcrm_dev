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

describe("Base.Layout.Panel", function () {

    var app, layout, togglePanelStub;

    beforeEach(function () {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', "Cases", "panel", null, null);
        togglePanelStub = sinon.stub(layout, 'togglePanel');
    });

    afterEach(function () {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        togglePanelStub.restore();
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe("Toggle Show/Hide", function() {
        it("should retrieve last state when collection reset", function() {
            var lastStateGetStub = sinon.stub(app.user.lastState, 'get');
            layout.collection.reset([]);
            expect(lastStateGetStub).toHaveBeenCalled();
            lastStateGetStub.restore();
        });
        it("should toggle panel when collection reset", function() {
            layout.collection.reset([]);
            expect(togglePanelStub).toHaveBeenCalled();
            expect(togglePanelStub.lastCall.args[0]).toBe(false);
            expect(togglePanelStub.lastCall.args[1]).toBe(false);

            layout.collection.reset([{id: 'test'}]);
            expect(togglePanelStub).toHaveBeenCalled();
            expect(togglePanelStub.lastCall.args[0]).toBe(true);
            expect(togglePanelStub.lastCall.args[1]).toBe(false);
        });
        it("should toggle panel depending on last state", function() {
            var state = 'hide';
            var lastStateGetStub = sinon.stub(app.user.lastState, 'get', function() {
                return state;
            });
            layout.collection.reset([{id: 'test'}]);
            expect(lastStateGetStub).toHaveBeenCalled();
            expect(togglePanelStub).toHaveBeenCalled();
            expect(togglePanelStub.lastCall.args[0]).toBe(false);
            expect(togglePanelStub.lastCall.args[1]).toBe(false);

            state = 'show';

            layout.collection.reset([]);
            expect(lastStateGetStub).toHaveBeenCalled();
            expect(togglePanelStub).toHaveBeenCalled();
            expect(togglePanelStub.lastCall.args[0]).toBe(true);
            expect(togglePanelStub.lastCall.args[1]).toBe(false);

            lastStateGetStub.restore();
        });
        it("should set last state when toggling panel", function() {
            togglePanelStub.restore();
            var lastStateSetStub = sinon.stub(app.user.lastState, 'set');

            layout.togglePanel(false, false);
            expect(lastStateSetStub).not.toHaveBeenCalled();

            layout.togglePanel(true, false);
            expect(lastStateSetStub).not.toHaveBeenCalled();

            layout.togglePanel(true);
            expect(lastStateSetStub).toHaveBeenCalled();
            expect(lastStateSetStub.lastCall.args[1]).toBe('show');

            layout.togglePanel(false);
            expect(lastStateSetStub).toHaveBeenCalled();
            expect(lastStateSetStub.lastCall.args[1]).toBe('hide');

            lastStateSetStub.restore();
        });
    });

    describe('_hideComponent', function() {
        it('should always call show on a create subpanel', function() {
            var component = {
                    show: function() {},
                    hide: function() {}
                },
                showSpy = sinon.spy(component, 'show');
            layout.context.set('isCreateSubpanel', true);

            layout._hideComponent(component, false);
            expect(showSpy).toHaveBeenCalled();
            showSpy.restore();
        });
    });
});
