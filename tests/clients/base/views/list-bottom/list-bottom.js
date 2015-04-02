describe("Base.View.ListBottom", function () {
    var view, app;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list-bottom');
        view = SugarTest.createView("base", "Opportunities", "list", null, null);
        app = SUGAR.App;
    });

    afterEach(function () {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
    });

    it('should module names start with lowercase letters', function() {
        var lowerCaseModuleName = 'opportunities';
        var showMoreLabel = app.lang.get(view.options.meta.showMoreLabel, 'Opportunities', {
            module: app.lang.getModuleName(lowerCaseModuleName, {plural: true})
        });
        expect(view.showMoreLabel).toEqual(showMoreLabel);

    });
});
