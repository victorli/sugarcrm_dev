describe("Preview Header View", function() {

    var app, view;

    beforeEach(function() {
        app = SugarTest.app;
        var context = app.context.getContext();
        view = SugarTest.createView("base","Accounts", "preview-header", null, context);
        view.model = new Backbone.Model();
        app.drawer = {
                isActive:function() {
                    
                }
        };
    });
    
    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view.model = null;
        view = null;
        delete app.drawer;
    });

    it("should trigger preview:close on preview close", function() {
        var spy = sinon.spy();

        app.events.off('preview:close');
        app.events.on('preview:close', spy);
        view.triggerClose();
        expect(spy).toHaveBeenCalled();
    });
});
