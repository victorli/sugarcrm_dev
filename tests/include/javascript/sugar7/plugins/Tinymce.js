describe('Plugins.Tinymce', function() {
    var module = 'KBContents',
        fieldName = 'htmleditable',
        fieldType = 'htmleditable_tinymce',
        app, field, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        SugarTest.testMetadata.init();
        Handlebars.templates = {};
        SugarTest.loadComponent('base', 'field', fieldType);
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'detail', module);
        SugarTest.loadHandlebarsTemplate('file', 'field', 'base', 'detail', 'EmbeddedFiles');
        SugarTest.loadPlugin('Tinymce');
        SugarTest.testMetadata.set();
        app.data.declareModels();

        field = SugarTest.createField('base', fieldName, fieldType, 'edit', {}, module);
    });

    afterEach(function() {
        delete app.plugins.plugins['field']['Tinymce'];
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        field.dispose();
        field = null;
        app.cache.cutAll();
        app.view.reset();
    });

    it('Append input for embedded files on render.', function() {
        var name = 'testName';
        field.$embeddedInput = $('<input />', {name: name, type: 'file'});
        field.render();
        expect(field.$el.find('input[name=' + name + ']').length).toEqual(1);
    });

    it('Clear element on file type mismatching.', function() {
        var winObj = {
            tinyMCEPopup: {
                alert: sinonSandbox.stub()
            }
        };
        var fakeFileObj = {name: 'filename.txt', type: 'text/plain'};
        var clearFileSpy = sinonSandbox.spy(field, 'clearFileInput');

        field.render();
        field.$embeddedInput[0].files[0] = fakeFileObj;
        // The fake file is text, image required.
        field.tinyMCEFileBrowseCallback('fakeName', 'fakeUrl', 'image', winObj);
        field.$embeddedInput.change();

        expect(clearFileSpy).toHaveBeenCalledOnce();
    });

});
