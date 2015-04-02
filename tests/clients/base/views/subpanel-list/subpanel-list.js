describe("Subpanel List View", function() {
    var app, module, parentLayout, layout, view, sinonSandbox;

    beforeEach(function () {
        sinonSandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
        module = 'Cases';
        layout = SugarTest.createLayout("base", module, "subpanels", null, null);
        parentLayout = SugarTest.createLayout("base", module, "list", null, null);
        layout.layout = parentLayout;
        SugarTest.loadComponent('base', 'view', 'subpanel-list');
        view = SugarTest.createView("base", module, 'subpanel-list', null, null, null, layout);
    });

    afterEach(function () {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
        layout = null;
    });

    describe('Toggle list', function() {
        var showStub, hideStub;
        beforeEach(function() {
            showStub = sinonSandbox.stub(view.$el, 'show');
            hideStub = sinonSandbox.stub(view.$el, 'hide');
        });
        it('should toggle list to show', function() {
            view.toggleList(true);
            expect(showStub).toHaveBeenCalled();
            expect(hideStub).not.toHaveBeenCalled();
        });
        it('should toggle list to hide', function() {
            view.toggleList(false);
            expect(showStub).not.toHaveBeenCalled();
            expect(hideStub).toHaveBeenCalled();
        });
    });

    describe('Subpanel metadata intiialization', function() {
        it('should return most specific subpanel view metadata if found', function() {
            sinonSandbox.stub(view.options.context, "get").returns("Accounts");
            var expected = {a:1};
            var getViewStub = sinonSandbox.stub(app.metadata, 'getView').returns(expected);
            var actual = view._initializeMetadata();
            expect(actual).toEqual(expected);
            expect(getViewStub).toHaveBeenCalledThrice();
        });
    });

    describe('initialize', function() {
        var oldConfig;
        beforeEach(function() {
            oldConfig = app.config.maxSubpanelResult;
            app.config.maxSubpanelResult = 7;
        });
        afterEach(function(){
            app.config.maxSubpanelResult = oldConfig;
        });
        it('set the fetch limit on the context to app.config.maxSubpanelResult', function() {
            view = SugarTest.createView("base", 'Cases', 'subpanel-list', null, null, null, layout);
            var opts = view.context.get("collectionOptions");
            expect(opts).toBeDefined();
            expect(opts.limit).toEqual(app.config.maxSubpanelResult);
        });
    });

    describe("Warning unlink", function() {
        var sinonSandbox, alertShowStub, routerStub;
        beforeEach(function() {
            sinonSandbox = sinon.sandbox.create();
            routerStub = sinonSandbox.stub(app.router, "navigate");
            sinonSandbox.stub(Backbone.history, "getFragment");
            alertShowStub = sinonSandbox.stub(app.alert, "show");
        });

        afterEach(function() {
            sinonSandbox.restore();
        });

        it("should not alert warning message if _modelToUnlink is not defined", function() {
            app.routing.triggerBefore("route");
            expect(alertShowStub).not.toHaveBeenCalled();
        });
        it("should return true if _modelToUnlink is not defined", function() {
            sinonSandbox.stub(view, 'warnUnlink');
            expect(view.beforeRouteUnlink()).toBeTruthy();
        });
        it("should return false if _modelToUnlink is defined (to prevent routing to other views)", function() {
            sinonSandbox.stub(view, 'warnUnlink');
            view._modelToUnlink = new Backbone.Model();
            expect(view.beforeRouteUnlink()).toBeFalsy();
        });
        it("should redirect the user to the targetUrl", function() {
            var unbindSpy = sinonSandbox.spy(view, 'unbindBeforeRouteUnlink');
            view._modelToUnlink = app.data.createBean(module);
            view._currentUrl = 'Accounts';
            view._targetUrl = 'Contacts';
            view.unlinkModel();
            expect(unbindSpy).toHaveBeenCalled();
            expect(routerStub).toHaveBeenCalled();
        });
    });
});
