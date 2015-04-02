describe('View.Fields.Base.BadgeSelectField', function() {
    var app, field, items, module;

    module = 'Calls';
    items = {
        Inbound: 'Inbound',
        Outbound: 'Outbound'
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('badge-select', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('badge-select', 'field', 'base', 'list');
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'field', 'badge-select');
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        if (field) {
            field.dispose();
        }

        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('when the status field is in detail mode', function() {
        beforeEach(function() {
            field = SugarTest.createField('base', 'direction', 'badge-select', 'detail', undefined, module);
            field.items = items;
        });

        using('detail modes', ['detail', 'list'], function(mode) {
            it('should be a boostrap label', function() {
                field.action = mode;
                field.model.set('status', 'foo');
                field.render();
                expect(field.$('.label').length).toBe(1);
            });
        });
    });

    describe('when the status field is in edit mode', function() {
        it('should be an enum', function() {
            field = SugarTest.createField('base', 'direction', 'badge-select', 'edit', undefined, module);
            field.items = items;
            field.action = 'edit';
            field.render();
            expect(field.$('input.select2').length).toBe(1);
        });
    });
});
