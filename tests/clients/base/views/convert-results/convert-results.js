describe("Base.Views.ConvertResults", function() {
    var app, view, createBeanStub;

    beforeEach(function() {
        app = SugarTest.app;

        metadata = SugarTest.metadata;

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('convert-results', 'view', 'base');
        SugarTest.testMetadata.set();

        createBeanStub = sinon.stub(app.data, 'createBean', function(moduleName, attributes) {
            return new Backbone.Model(attributes);
        });

        view = SugarTest.createView('base', null, 'convert-results', null, null, true);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        createBeanStub.restore();
    });

    it("should have no models in collection", function() {
        expect(view.associatedModels.length).toEqual(0);
    });
});
