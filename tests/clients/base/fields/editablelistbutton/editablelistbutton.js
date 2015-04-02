describe("BaseEditablelistbuttonField", function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        app.view.Field.prototype._renderHtml = function() {};
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'editablelistbutton');

        field = SugarTest.createField("base","editablelistbutton", "editablelistbutton");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });
    it('should be able to trigger filtering to the filterpanel layout.', function() {
        var getModuleStub = sinon.stub(app.metadata, 'getModule', function(module) {
            return {activityStreamEnabled:true};
        });
        //Fake layouts
        field.view = new Backbone.View();
        field.view.layout = new Backbone.View();
        field.view.layout.layout = SugarTest.createLayout('base', 'Accounts', 'filterpanel', {});
        field.view.layout.layout.name = 'filterpanel';
        var applyLastFilterStub = sinon.stub(field.view.layout.layout, 'applyLastFilter');

        //Call the method
        field._refreshListView();

        expect(applyLastFilterStub).toHaveBeenCalled();
        expect(applyLastFilterStub).toHaveBeenCalledWith(field.collection);
        applyLastFilterStub.restore();
        getModuleStub.restore();
    });
});
