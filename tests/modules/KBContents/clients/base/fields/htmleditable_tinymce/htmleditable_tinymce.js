describe('modules.kbcontents.clients.base.fields.htmleditable_tinymce', function() {

    var app, field,
        module = 'KBContents',
        fieldName = 'htmleditable',
        fieldType = 'htmleditable_tinymce',
        model;

    beforeEach(function() {
        Handlebars.templates = {};
        SugarTest.loadComponent('base', 'field', 'htmleditable_tinymce');
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'detail', module);
        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', {}, module, model, null, true);
    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
    });

    it('apply document css style to editor', function() {
        var config = field.getTinyMCEConfig();
        expect(config.content_css).toEqual(jasmine.any(Object));
        expect(config.body_class).toEqual('kbdocument-body');
    });
    
});
