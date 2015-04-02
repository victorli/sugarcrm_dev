describe('Base.Layout.Subpanel', function() {
    var layout, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'panel');
        SugarTest.loadComponent('base', 'layout', 'subpanel');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.collection.restore();
        SugarTest.testMetadata.dispose();
    });

    describe('initialize', function() {
        var testMeta, testLayout, testParams;

        beforeEach(function() {
            testMeta = {
                components: [],
                last_state: {
                    id: 'jasmin-test'
                }
            };
            testParams = {
                def: {
                    'override_subpanel_list_view': 'jasmine_test'
                }
            };
            testLayout = SugarTest.createLayout('base', 'Accounts', 'subpanel', testMeta, undefined, false, testParams);
        });

        afterEach(function() {
            testLayout.dispose();
        });

        it('will set dataView variable and attribute on context to jasmine_test', function() {
            expect(testLayout.dataView).toEqual('jasmine_test');
            expect(testLayout.context.get('dataView')).toEqual('jasmine_test');
        });
    });
});
