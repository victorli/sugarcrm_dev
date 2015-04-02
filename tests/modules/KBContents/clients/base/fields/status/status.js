describe('modules.kbcontents.clients.base.fields.status', function() {
    var app, field,
        module = 'KBContents',
        fieldName = 'status',
        fieldType = 'status',
        model;

    beforeEach(function() {
        Handlebars.templates = {};
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit', module);
        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', {}, module, model, null, true);
    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
    });

    it('should be initialized with default status', function() {
        field.render();
        expect(model.get('status')).toEqual('draft');
        expect(field.statusClass).toEqual('label-pending');
        expect(field.statusLabel).toEqual('draft');
    });

    it('should be rendered with valid status', function() {
        model.set({
            status: 'published-in'
        });
        field.render();
        expect(model.get('status')).toEqual('published-in');
        expect(field.statusClass).toEqual('label-published');
        expect(field.statusLabel).toEqual('published-in');
    });

    it('should return valid data type when checking access to action', function() {
        var valid = (typeof field._checkAccessToAction() === 'boolean');
        expect(valid).toEqual(true);
    });

});
