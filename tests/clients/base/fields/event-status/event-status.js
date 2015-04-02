describe('View.Fields.Base.EventStatusField', function() {
    var app, field, items, module;

    module = 'Meetings';
    items = {
        Planned: 'Scheduled',
        Held: 'Held',
        'Not Held': 'Canceled',
        foo: 'Foo Moo'
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('badge-select', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('badge-select', 'field', 'base', 'list');
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'field', 'badge-select');
        SugarTest.loadComponent('base', 'field', 'event-status');
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
        var checkClasses = function(plain, success, important, pending) {
            expect(field.$('.label').length).toBe(plain);
            expect(field.$('.label-success').length).toBe(success);
            expect(field.$('.label-important').length).toBe(important);
            expect(field.$('.label-pending').length).toBe(pending);
        };

        beforeEach(function() {
            field = SugarTest.createField('base', 'status', 'event-status', 'detail', undefined, module);
            field.items = items;
        });

        using('detail modes', ['detail', 'list'], function(mode) {
            it('should be a bootstrap label', function() {
                field.action = mode;
                field.model.set('status', 'foo');
                field.render();
                checkClasses(1, 0, 0, 0);
            });
        });

        it('should be a success bootstrap label when the meeting was held', function() {
            field.model.set('status', 'Held');
            field.render();
            checkClasses(1, 1, 0, 0);
        });

        it('should be an important bootstrap label when the meeting was not held', function() {
            field.model.set('status', 'Not Held');
            field.render();
            checkClasses(1, 0, 1, 0);
        });

        it('should be a plain bootstrap label when the meeting is planned', function() {
            field.model.set('status', 'Planned');
            field.render();
            checkClasses(1, 0, 0, 1);
        });
    });

    describe('when the status field is in edit mode', function() {
        beforeEach(function() {
            field = SugarTest.createField('base', 'status', 'event-status', 'edit', undefined, module);
            field.items = items;
        });

        it('should be an enum', function() {
            field.action = 'edit';
            field.render();
            expect(field.$('input.select2').length).toBe(1);
        });
    });
});
