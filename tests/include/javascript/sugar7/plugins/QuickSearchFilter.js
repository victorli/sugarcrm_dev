describe('Plugins.Quicksearchfilter', function() {

    var app, field, filtersBeanPrototype;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.declareData('base', 'Filters');
        filtersBeanPrototype = app.data.getBeanClass('Filters').prototype;
        field = SugarTest.createField('base', 'account_name', 'relate', 'edit');
    });

    afterEach(function() {
        sinon.collection.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('getModuleQuickSearchMeta', function() {
        it('should call "Data.Base.FiltersBean#getModuleQuickSearchMeta"', function() {
            var stubGetModuleQuickSearchMeta = sinon.collection.stub(filtersBeanPrototype, 'getModuleQuickSearchMeta');

            var moduleName = 'Accounts';
            field.getModuleQuickSearchMeta(moduleName);
            expect(stubGetModuleQuickSearchMeta).toHaveBeenCalledWith(moduleName);
        });
    });

    describe('getModuleQuickSearchFields', function() {
        var stubGetModuleQuickSearchMeta;
        var moduleName = 'Accounts';

        beforeEach(function() {
            stubGetModuleQuickSearchMeta = sinon.collection.stub(filtersBeanPrototype, 'getModuleQuickSearchMeta');
        });

        afterEach(function() {
            stubGetModuleQuickSearchMeta.restore();
        });

        it('should call "Data.Base.FiltersBean#getModuleQuickSearchMeta"', function() {
            field.getModuleQuickSearchMeta(moduleName);
            expect(stubGetModuleQuickSearchMeta).toHaveBeenCalledWith(moduleName);
        });

        it('should return "fieldNames" property', function() {
            var fieldNames = ['name'];
            stubGetModuleQuickSearchMeta.returns({
                fieldNames: fieldNames
            });

            expect(field.getModuleQuickSearchMeta(moduleName).fieldNames).toEqual(fieldNames);
        });
    });

    describe('getFilterDef', function() {
        it('should call "Data.Base.FiltersBean#buildSearchTermFilter"', function() {
            var stubBuildSearchTermFilter = sinon.collection.stub(filtersBeanPrototype, 'buildSearchTermFilter');

            var moduleName = 'Accounts';
            var searchTerm = 'Luis Filipe Madeira';
            field.getFilterDef(moduleName, searchTerm);
            expect(stubBuildSearchTermFilter).toHaveBeenCalledWith(moduleName, searchTerm);
        });
    });
});
