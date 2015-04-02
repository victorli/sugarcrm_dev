describe('Base.Layout.HistoryDefault', function() {
    var layout, app, def;

    beforeEach(function() {
        app = SugarTest.app;
        def = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'default');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        layout = SugarTest.createLayout('base', null, 'history-default', def, null);
    });

    afterEach(function() {
        sinon.collection.restore();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('right pane', function() {

        it('should be always collapsed when the layout is created', function() {
            expect(layout.isSidePaneVisible()).toBe(false);
            layout.toggleSidePane();
            expect(layout.isSidePaneVisible()).toBe(true);

            var newLayout = SugarTest.createLayout('base', null, 'history-default', def, null);
            expect(newLayout.isSidePaneVisible()).toBe(false);
            newLayout.dispose();
        });
    });

});
