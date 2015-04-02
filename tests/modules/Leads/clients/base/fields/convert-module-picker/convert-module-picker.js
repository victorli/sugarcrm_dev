describe('Leads.Fields.ConvertModulePicker', function() {
    var app, field, convertModuleList, requiredModuleList, requiredModuleIds;

    beforeEach(function() {
        var context,
            type = 'convert-module-picker';

        app = SugarTest.app;
        context = app.context.getContext();

        requiredModuleIds = ['Foo', 'Bar'];
        requiredModuleList = [
            {id: 'Foo', text: 'FooText', required: true},
            {id: 'Bar', text: 'BarText', required: true}
        ];
        convertModuleList = _.union(requiredModuleList, [
            {id: 'Baz', text: 'BazText', required: false}
        ]);
        context.set('convertModuleList', convertModuleList);

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(type, 'field', 'base', 'edit', 'Leads');
        SugarTest.testMetadata.set();

        field = SugarTest.createField({
            name: type,
            type: type,
            context: context,
            viewName: 'edit',
            module: 'Leads',
            loadFromModule: true
        });
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it('should have parsed out required modules on initialize', function() {
        expect(field.requiredModules).toEqual(requiredModuleList);
    });

    it('should initialize the select2 widget with the required modules', function() {
        field.render();
        expect(field.$select2.select2('val')).toEqual(requiredModuleIds);
        expect(field.$select2.select2('data')).toEqual(requiredModuleList);
    });

    it('should initialize the model with the required module names', function() {
        field.render();
        expect(field.model.get(field.name)).toEqual(requiredModuleIds);
    });

    it('should update the model when the select2 data changes', function() {
        var baz = _.findWhere(convertModuleList, {id: 'Baz'}),
            selectedModules;

        field.render();

        expect(_.contains(field.model.get(field.name), 'Baz')).toBe(false);
        selectedModules = field.$select2.select2('data');
        selectedModules.push(baz);
        field.$select2.select2('data', selectedModules, true);

        expect(_.contains(field.model.get(field.name), 'Baz')).toBe(true);
    });

    it('should add module to removed module list when removed', function() {
        var event = {
            removed: {
                id: 'Foo'
            }
        };
        field.render();

        field.handleChange(event);
        expect(field.removedModules).toEqual(['Foo']);
    });

    it('should auto-add module when module is completed and not previously removed', function() {
        field.render();
        expect(_.contains(field.$select2.select2('val'), 'Baz')).toBe(false);
        field.context.trigger('lead:convert-panel:complete', 'Baz');
        expect(_.contains(field.$select2.select2('val'), 'Baz')).toBe(true);
    });

    it('should not auto-add module when module is completed, but previously removed', function() {
        field.render();
        field.removedModules = ['Baz'];
        expect(_.contains(field.$select2.select2('val'), 'Baz')).toBe(false);
        field.context.trigger('lead:convert-panel:complete', 'Baz');
        expect(_.contains(field.$select2.select2('val'), 'Baz')).toBe(false);
    });
});
