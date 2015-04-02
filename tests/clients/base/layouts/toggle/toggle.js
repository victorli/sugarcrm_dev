describe("Base.Layout.Toggle", function() {
    var app, layout, defaultMeta, viewA, viewB, viewC,
        moduleName = 'Contacts';

    var createMockView = function(name) {
        return {
            name: name,
            initialize: sinon.stub(),
            render: sinon.stub(),
            el: '<div data-name="' + name + '"></div>',
            dispose: sinon.stub()
        };
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        viewA = createMockView('view-a');
        viewB = createMockView('view-b');
        viewC = createMockView('view-c');
        SugarTest.addComponent('base', 'view', 'view-a', viewA);
        SugarTest.addComponent('base', 'view', 'view-b', viewB);
        SugarTest.addComponent('base', 'view', 'view-c', viewC);
        SugarTest.testMetadata.set();
        defaultMeta = {
            "default_toggle": 'view-c',
            "available_toggles": {
                'view-a': {},
                'view-c': {}
            },
            "components": [
                {"view":"view-a"},
                {"view":"view-b"},
                {"view":"view-c"}
            ]
        };
        layout = SugarTest.createLayout("base", moduleName, "toggle", defaultMeta);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    it("should render view-b then view-c, but defer rendering of view-a", function(){
        expect(viewA.render.callCount).toBe(0);
        expect(viewB.render.callCount).toBe(1); //not a toggle view
        expect(viewC.render.callCount).toBe(1); //default view to be rendered
        expect(layout._components.length).toBe(2);
        expect(layout.$('div').first().data('name')).toEqual('view-b');
        expect(layout.$('div').slice(1).data('name')).toEqual('view-c');
        expect(layout.$('[data-name="view-a"]').length).toBe(0);
    });

    it("should call dispose on all views, even though view-a isn't in layout's _components", function(){
        layout.dispose();
        expect(viewA.dispose.callCount).toBe(1);
        expect(viewB.dispose.callCount).toBe(1);
        expect(viewC.dispose.callCount).toBe(1);
    });

    it("should render view-a and hide view-c when showcomponent trigger is fired for view-a", function(){
        layout.trigger('toggle:showcomponent', 'view-a');
        expect(viewA.render.callCount).toBe(1);
        expect(layout._components.length).toBe(3);
        expect(layout.$('[data-name="view-a"]').length).toBe(1);
        expect(layout.$('[data-name="view-c"]').hasClass('hide')).toBe(true);
    });

    it("should toggle between views each time showComponent is called but render is only called once", function(){
        layout.showComponent('view-a');
        expect(layout.$('[data-name="view-a"]').hasClass('hide')).toBe(false);
        expect(layout.$('[data-name="view-c"]').hasClass('hide')).toBe(true);
        layout.showComponent('view-c');
        expect(layout.$('[data-name="view-a"]').hasClass('hide')).toBe(true);
        expect(layout.$('[data-name="view-c"]').hasClass('hide')).toBe(false);
        expect(viewA.render.callCount).toBe(1);
        expect(viewB.render.callCount).toBe(1);
    });
});
