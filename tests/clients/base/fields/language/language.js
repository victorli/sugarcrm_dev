describe("BaseLanguageField", function() {
    var app, field, _oDefaultLanguage,
        fieldName = 'test_language';

    beforeEach(function() {
        delete Handlebars.templates;
        Handlebars.templates = {};
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'field', 'language');
        SugarTest.testMetadata.set();
        field = SugarTest.createField("base", fieldName, "language", "edit", {options: "available_language_dom"});
        _oDefaultLanguage = app.lang.getDefaultLanguage();
    });

    afterEach(function() {
        app.lang.setDefaultLanguage(_oDefaultLanguage);
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field = null;
    });

    it("should load enum templates", function() {
        expect(field.type).toEqual('language');
        var stubLoadTemplate = sinon.stub(app.view.Field.prototype, '_loadTemplate', function() {
            expect(field.type).toEqual('enum');
        });
        field._loadTemplate();
        expect(field.type).toEqual('language');
        stubLoadTemplate.restore();
    });

    it("should set the default based on the application default language", function() {
        app.lang.setDefaultLanguage('test_TEST');
        expect(field.model.get(fieldName)).toBeUndefined();
        field.render();
        expect(field.model.get(fieldName)).toEqual('test_TEST');
    });

    it("should set the default based if the user\'s preferred language is disabled", function() {
        app.lang.setDefaultLanguage('it_IT');
        field.model.set(fieldName, 'en_us');
        field.items = {
            'fr_FR': 'French',
            'it_IT': 'Italiano'
        };
        field.render();
        expect(field.model.get(fieldName)).toEqual('it_IT');
    });
});
