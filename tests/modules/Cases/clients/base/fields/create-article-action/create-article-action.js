describe('BaseCasesCreateArticleActionField', function() {

    var app, field, moduleName = 'Cases';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        field = SugarTest.createField({
            'client': 'base',
            'name': 'create-article-action',
            'type': 'create-article-action',
            'viewName': 'edit',
            'fieldDef': {},
            'module': moduleName,
            'loadFromModule': true
        });
    });

    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field = null;
    });

    it('should load the rowaction template', function() {
        var fieldType;
        sinon.collection.stub(field, '_super', function() {
            fieldType = field.type;
        });
        field._loadTemplate();
        expect(fieldType).toEqual('rowaction');
    });
});
