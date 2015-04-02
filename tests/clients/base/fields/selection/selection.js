describe("Base.Field.Selection", function() {
    var app, field, Address;

    beforeEach(function() {
        app = SugarTest.app;
        var def = {};
        field = SugarTest.createField("base", null, "selection", "list", def);

        var Account = Backbone.Model.extend({});
        field.model = new Account({
            id: 'aaa',
            name: 'boo'
        });
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field._loadTemplate = null;
        field = null;
        Address = null;
    });

    it("should store the model when it is selected", function() {
        var fieldSpy = sinon.spy(field.context, "trigger");
        field.check();
        var actual = field.context.get("selection_model"),
            expected = field.model;
        expect(actual).toBe(expected);
        expect(field.context.trigger.calledWith("change:selection_model")).toBeTruthy();

        fieldSpy.restore();

        fieldSpy = sinon.spy(field.context, "trigger");
        field.uncheck();
        actual = field.context.get("selection_model");
        expect(actual).toBeUndefined();
        expect(field.context.trigger.calledWith("change:selection_model")).toBeTruthy();

    });
});