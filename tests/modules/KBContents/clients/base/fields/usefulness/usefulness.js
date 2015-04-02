describe('modules.kbcontents.clients.base.fields.usefulness', function() {
    var sandbox, app, field,
        module = 'KBContents',
        fieldName = 'usefulness',
        fieldType = 'usefulness',
        model,
        apiCallStub;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        Handlebars.templates = {};
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit', module);
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'detail', module);
        SugarTest.testMetadata.set();
        
        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
        apiCallStub = sandbox.stub(app.api, 'call', function(method, url, data, callbacks) {
            callbacks.success({});
        });
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', {}, module, model, null, true);
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
    });

    it('should have default vote values', function() {
        expect(field.model.get('useful')).toEqual(0);
        expect(field.model.get('notuseful')).toEqual(0);
    });

    it('should be able vote and set useful when useful button clicked', function() {
        var voteSpy = sinon.spy(field, 'vote');
        var getLastStateKeySpy = sandbox.spy(field, 'getLastStateKey');
        model.set({
            id: 'test_model_id'
        });

        field.render();
        field.$('[data-action="useful"]').click();
        expect(voteSpy).toHaveBeenCalledWith(true);
        expect(getLastStateKeySpy).toHaveBeenCalled();
        expect(field.voted).toEqual(true);
        expect(field.votedUseful).toEqual(true);
        expect(field.votedNotUseful).toEqual(false);
    });

    it('should be able vote and set notuseful when notuseful button clicked', function() {
        var voteSpy = sandbox.spy(field, 'vote');
        var getLastStateKeySpy = sandbox.spy(field, 'getLastStateKey');
        model.set({
            id: 'test_model_id'
        });

        field.render();
        field.$('[data-action="notuseful"]').click();
        expect(voteSpy).toHaveBeenCalledWith(false);
        expect(getLastStateKeySpy).toHaveBeenCalled();
        expect(field.voted).toEqual(true);
        expect(field.votedUseful).toEqual(false);
        expect(field.votedNotUseful).toEqual(true);
    });
});
