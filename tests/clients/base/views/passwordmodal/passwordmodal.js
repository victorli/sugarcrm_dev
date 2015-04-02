describe("Password Modal", function() {

    var layout, view, modal, app;

    beforeEach(function() {
        view = SugarTest.createView("base","Contacts", "baseeditmodal");
        modal = SugarTest.createView("base","Contacts", "passwordmodal");
        _.extend(view, modal);
        view.model = new Backbone.Model();
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
        layout = null;
    });

    describe("password modal", function() {

        it("should not extend BaseeditmodalView events hash (Bug59041)", function() {
            var BaseeditmodalEvents = view.events;
            view._render();
            expect(view.events["focusin input[name=new_password]"]).toBeDefined();
            expect(view.events["focusin input[name=confirm_password]"]).toBeDefined();
            expect(BaseeditmodalEvents["focusin input[name=new_password]"]).not.toBeDefined();
            expect(BaseeditmodalEvents["focusin input[name=confirm_password]"]).not.toBeDefined();
        });
    });
});
