describe('View.Views.Base.SearchListView', function() {

    var view, app, model;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('search-list', {
            'panels': {
                1: {
                    name: 'primary',
                    fields: [{name: 'name'}]
                },
                2: {
                    name: 'secondary',
                    fields: [{name: 'description'}]
                }
            }
        });
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', 'GlobalSearch', 'search-list');
        app = SUGAR.App;
        model = app.data.createBean();

        SugarTest.loadFile('../include/javascript/sugar7', 'utils-search', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        sinon.collection.stub(app.metadata, 'getModule', function() {
            return fixtures.search.getModule1_return;
        });
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
        model = null;
        sinon.collection.restore();
    });

    describe('parseModels', function() {
        using('different highlighted fields', [
            {
                highlights: [
                    {
                        name: 'description',
                        value: 'This is the description.',
                        highlighted: true
                    }
                ],
                expectedPrimaryFields: [
                    {
                        name: 'name',
                        primary: true,
                        ellipsis: false
                    }
                ],
                expectedSecondaryFields: [
                    {
                        name: 'description',
                        secondary: true,
                        ellipsis: false,
                        highlighted: true,
                        link: false,
                        value: 'This is the description.'
                    }
                ]
            },
            {
                highlights: [
                    {
                        name: 'description',
                        value: 'This is the description.'
                    },
                    {
                        name: 'name',
                        value: 'James Dean'
                    }
                ],
                expectedPrimaryFields: [
                    {
                        name: 'name',
                        primary: true,
                        ellipsis: false,
                        value: 'James Dean'
                    }],
                expectedSecondaryFields: [
                    {
                        name: 'description',
                        secondary: true,
                        ellipsis: false,
                        link: false,
                        value: 'This is the description.'
                    }
                ]
            },
            {
                highlights: [],
                expectedPrimaryFields: [
                    {
                        name: 'name',
                        primary: true,
                        ellipsis: false
                    }
                ],
                expectedSecondaryFields: [
                    {
                        name: 'description',
                        secondary: true,
                        ellipsis: false,
                        link: false
                    }
                ]
            }
        ], function(val) {
            it('should create "primaryFields" and "secondaryFields" property on the model',
                function() {
                    model.set('_highlights', val.highlights);
                    model.fields = {};
                    view.parseModels([model]);
                    expect(model.primaryFields).toEqual(val.expectedPrimaryFields);
                    expect(model.secondaryFields).toEqual(val.expectedSecondaryFields);
                }
            );
        });
    });
});
