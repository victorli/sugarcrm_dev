describe('Base.Field.CollectionCount', function() {
    var app, field, template,
        module = 'Bugs',
        fieldName = 'foo';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        template = SugarTest.loadHandlebarsTemplate('collection-count', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();
        fieldDef = {};
        field = SugarTest.createField('base', fieldName, 'collection-count', 'detail', fieldDef, module);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.dispose();
        sinon.collection.restore();
    });

    describe('render', function() {

        beforeEach(function() {
            sinon.collection.stub(app.lang, 'get', function(key) {
                return key;
            });
            field.collection = app.data.createBeanCollection(module);
        });

        using('different collection properties', [
            {
                length: 0,
                next_offset: -1,
                expected: ''
            },
            {
                length: 5,
                next_offset: -1,
                expected: 'TPL_LIST_HEADER_COUNT'
            },
            {
                length: 20,
                next_offset: 20,
                expected: 'TPL_LIST_HEADER_COUNT_TOTAL'
            }
        ], function(option) {
            it('should display a proper count representation', function() {
                field.collection.length = option.length;
                field.collection.next_offset = option.next_offset;
                field.collection.dataFetched = true;

                field.render();
                expect(field.countLabel.toString()).toBe(option.expected);
            });
        });

        it('should display the total cached count', function() {
            field.collection.length = 20;
            field.collection.total = 500;
            field.collection.dataFetched = true;

            field.render();
            expect(field.countLabel.toString()).toBe('TPL_LIST_HEADER_COUNT_TOTAL');
        });
    });

    describe('paginate', function() {
        it('should fetch the total count when paginating', function() {
            sinon.collection.stub(app.BeanCollection.prototype, 'fetchTotal');
            sinon.collection.stub(app.alert);

            field.context.trigger('paginate');
            expect(app.BeanCollection.prototype.fetchTotal).toHaveBeenCalled();
        });
    });

    describe('reset', function() {
        it('should keep the counts in sync with the collection', function() {
            sinon.collection.spy(field, 'render');

            field.collection.length = 20;
            field.collection.total = 500;
            field.collection.dataFetched = true;

            field.collection.trigger('reset');

            expect(field.render.calledOnce).toBe(true);
            expect(field.countLabel.toString()).toBe('TPL_LIST_HEADER_COUNT_TOTAL');

            field.collection.length = 20;
            field.collection.total = null;
            field.collection.next_offset = -1;

            field.collection.trigger('reset');

            expect(field.render.calledTwice).toBe(true);
            expect(field.countLabel.toString()).toBe('TPL_LIST_HEADER_COUNT');
        });
    });
});
