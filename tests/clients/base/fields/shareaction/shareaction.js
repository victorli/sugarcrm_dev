describe('Base.Field.Shareaction', function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        app.drawer = { open: $.noop };

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('share', 'view', 'base', 'subject');
        SugarTest.loadHandlebarsTemplate('share', 'view', 'base', 'body');
        SugarTest.loadHandlebarsTemplate('share', 'view', 'base', 'body-html');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'emailaction');
        SugarTest.loadComponent('base', 'field', 'shareaction');
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        app.drawer = undefined;
        SugarTest.testMetadata.dispose();
        field.dispose();
        field = null;
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it('should set up email options with subject, html body, and text body', function() {
        field = SugarTest.createField('base', 'shareaction', 'shareaction', 'edit');
        expect(field.emailOptions.subject).toContain('TPL_RECORD_SHARE_SUBJECT');
        expect(field.emailOptions.html_body).toContain('TPL_RECORD_SHARE_BODY');
        expect(field.emailOptions.text_body).toContain('TPL_RECORD_SHARE_BODY');
    });
});
