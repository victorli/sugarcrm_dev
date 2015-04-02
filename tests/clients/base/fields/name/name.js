describe('Base.Field.Name', function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'name', 'name', 'detail', {});
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.dispose();
        sinon.collection.restore();
    });

    describe('Render', function() {
        using('different view names and values', [
            {
                view: 'audit',
                linkValue: undefined,
                expected: false
            },
            {
                view: 'preview',
                linkValue: undefined,
                expected: true
            },
            {
                view: 'preview',
                linkValue: false,
                expected: false
            },
            {
                view: 'other',
                linkValue: undefined,
                expected: undefined
            }
        ], function(options) {
            it('should set def.link appropriately on preview and audit view', function() {
                field.view.name = options.view;
                field.def.link = options.linkValue;
                field.render();
                expect(field.def.link).toEqual(options.expected);
            });
        });
    });
});
