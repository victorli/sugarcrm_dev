describe('Plugins.KBContents', function() {
    var moduleName = 'KBContents',
        app, view, viewMeta, sandbox, context;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        context = app.context.getContext({
            module: moduleName
        });
        viewMeta = {
            buttons: [
                {name: 'button1'}
            ]
        };
        app.drawer = {
            open: function() {
            },
            close: function() {
            }
        };
        context.set('model', app.data.createBean(moduleName));
        context.parent = new Backbone.Model();
        context.parent.set('module', moduleName);
        SugarTest.loadFile(
            '../modules/KBContents/clients/base/plugins',
            'KBContent',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'recordlist', moduleName);
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'record', moduleName);
        SugarTest.loadComponent('base', 'view', 'create');
        SugarTest.loadComponent('base', 'view', 'create', moduleName);
        layout = SugarTest.createLayout('base', moduleName, 'list', null, context.parent);
        view = SugarTest.createView('base', moduleName, 'create', viewMeta, null, moduleName, layout);
    });

    afterEach(function() {
        sandbox.restore();
        view.model.off();
        view.context.off();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        delete app.plugins.plugins['view']['KBContent'];
        view = null;
        layout = null;
    });

    it('Validations should be in inline edit.', function() {
        view = SugarTest.createView('base', moduleName, 'recordlist', viewMeta, null, moduleName, layout);
        var validationStub = sandbox.stub(view, '_initValidationHandler');
        sandbox.stub(view, 'toggleRow');
        view.context.trigger('list:editrow:fire', view.model, {def: {}});
        expect(validationStub).toHaveBeenCalledOnce();
    });

    it('Force duplicate should be true is a check duplicate button exists.', function() {
        viewMeta = {
            buttons: [
                {name: 'check_duplicate'}
            ]
        };
        view = SugarTest.createView('base', moduleName, 'record', viewMeta, context, moduleName);
        expect(view.forceDuplicate).toBe(true);
    });

    it('Force duplicate should be true is a check duplicate is a subbutton.', function() {
        viewMeta = {
            buttons: [
                {name: 'button2', buttons: [{name: 'check_duplicate'}]}
            ]
        };
        view = SugarTest.createView('base', moduleName, 'record', viewMeta, context, moduleName);
        expect(view.forceDuplicate).toBe(true);
    });

    it('Overridden validate should not be called if no duplicate check button.', function() {
        var modelDoValidateStub = sandbox.stub(view.model, 'doValidate');
        var proto = Object.getPrototypeOf(view);
        proto.editExisting = sandbox.stub();
        view.editExisting(view.model);
        expect(modelDoValidateStub).not.toHaveBeenCalled();
    });

    it('Duplicate check should contain additional data.', function() {
        viewMeta = {
            buttons: [
                {name: 'check_duplicate'}
            ]
        };
        view = SugarTest.createView('base', moduleName, 'record', viewMeta, context, moduleName);
        var modelDoValidateStub = sandbox.stub(view.model, 'doValidate');
        var model = new Backbone.Model({
            id: 1,
            kbarticle_id: 2,
            kbdocument_id: 2
        });
        view.editExisting(model);

        expect(view.model.get('kbarticle_id')).toEqual(2);
        expect(view.model.get('kbdocument_id')).toEqual(2);
        expect(modelDoValidateStub).toHaveBeenCalled();
    });

    it('AfterSave should hide duplicates on false.', function() {
        var hideDupStub = sandbox.stub(view, 'hideDuplicates');
        sandbox.stub(app.router, 'navigate');
        sandbox.stub(app.alert, 'show');
        view.model.id = 1;

        view.afterSave(false);
        expect(hideDupStub).toHaveBeenCalled();

        hideDupStub.reset();
        view.afterSave(true);
        expect(hideDupStub).not.toHaveBeenCalled();
    });

    it('Render duplicate checklist if a check duplicate button available.', function() {
        viewMeta = {
            buttons: [
                {name: 'check_duplicate'}
            ]
        };
        view = SugarTest.createView('base', moduleName, 'create', viewMeta, context, moduleName);
        var renderDupeCheckListStub = sandbox.stub(view, 'renderDupeCheckList');
        view._render();
        expect(renderDupeCheckListStub).toHaveBeenCalled();
    });

    it('Create article should call an appropriate drawer.', function() {
        var drawerStub = sandbox.stub(app.drawer, 'open');
        var model = new Backbone.Model({
            name: 'fakeName'
        });
        sandbox.stub(app.template, 'getField', function() {
            return function() {
                return 'fakeTemplate';
            };
        });
        view.createArticle(model);
        expect(drawerStub).toHaveBeenCalled();
        expect(drawerStub.args[0][0].context.model.get('name')).toEqual('fakeName');
        expect(drawerStub.args[0][0].context.model.get('kbdocument_body')).toEqual('fakeTemplate');
    });

    it('Created localizations and revisions should have draft status.', function() {
        sandbox.stub(app.data, 'createBean', function() {
            var prefillModel = new Backbone.Model();
            prefillModel.copy = function() {
            };
            return prefillModel;
        });
        var fakeModel = new Backbone.Model({
            module: moduleName,
            name: 'fakeName',
            status: ''
        });
        sandbox.stub(fakeModel, 'fetch', function(options) {
            options.success();
        });
        var createLocStub = sandbox.stub(view, '_onCreateLocalization');
        var createRevStub = sandbox.stub(view, '_onCreateRevision');

        view.createLocalization(fakeModel);
        expect(createLocStub).toHaveBeenCalled();
        expect(createLocStub.args[0][0].get('status')).toEqual('draft');

        fakeModel.set('status', '');
        view.createRevision(fakeModel);
        expect(createRevStub).toHaveBeenCalled();
        expect(createRevStub.args[0][0].get('status')).toEqual('draft');
    });

    it('Check possibility to create a localization.', function() {
        var fakeModel = new Backbone.Model({
            module: moduleName,
            name: 'fakeName',
            related_languages: []
        });
        sandbox.stub(app.metadata, 'getModule', function() {
            return {
                languages: ['en', 'fr']
            };
        });

        // No related languages.
        expect(view.checkCreateLocalization(fakeModel)).toBe(true);

        // The same language count.
        fakeModel.set('related_languages', ['en', 'fr']);
        expect(view.checkCreateLocalization(fakeModel)).toBe(false);

        // Not the same language count.
        fakeModel.set('related_languages', ['en', 'fr', 'de']);
        expect(view.checkCreateLocalization(fakeModel)).toBe(true);
    });

    it('Create localization.', function() {
        var fakeModel = new Backbone.Model({
            module: moduleName,
            language: 'fakeLang',
            kbarticle_id: 'fakeArticleId'
        });
        var createRelatedDrawerStub = sandbox.stub(view, '_openCreateRelatedDrawer');
        var checkLocStub = sandbox.stub(view, 'checkCreateLocalization', function() {
            return false;
        });
        sandbox.stub(view, 'getAvailableLangsForLocalization', function() {
            return ['en', 'fr'];
        });

        view._onCreateLocalization(fakeModel);
        expect(createRelatedDrawerStub).not.toHaveBeenCalled();
        checkLocStub.restore();

        sandbox.stub(view, 'checkCreateLocalization', function() {
            return true;
        });
        view._onCreateLocalization(fakeModel);
        expect(createRelatedDrawerStub).toHaveBeenCalled();
        expect(fakeModel.get('language')).toBe(undefined);
        expect(fakeModel.get('kbarticle_id')).toBe(undefined);
        expect(fakeModel.get('related_languages')).toEqual(['en', 'fr']);
    });

    it('Create revision. Should inherit parents data.', function() {
        var fakePrefillModel = new Backbone.Model({
            module: moduleName,
            useful: 1,
            notuseful: 0,
            related_languages: ['fr']
        });
        var fakeParentModel = new Backbone.Model({
            module: moduleName,
            useful: 0,
            notuseful: 1,
            language: 'en'
        });
        var createRelatedDrawerStub = sandbox.stub(view, '_openCreateRelatedDrawer');

        view._onCreateRevision(fakePrefillModel, fakeParentModel);
        expect(createRelatedDrawerStub).toHaveBeenCalled();
        expect(fakePrefillModel.get('useful')).toBe(0);
        expect(fakePrefillModel.get('notuseful')).toBe(1);
        expect(fakePrefillModel.get('related_languages')).toEqual(['en']);
    });

    it('Creating related should trigger duplicate check .', function() {
        var fakePrefillModel = new Backbone.Model();
        var fakeParentModel = new Backbone.Model({
            id: 1
        });
        var triggerStub = sandbox.stub(fakePrefillModel, 'trigger');
        sandbox.stub(app.drawer, 'open');

        view._openCreateRelatedDrawer(fakePrefillModel, fakeParentModel);

        expect(triggerStub).toHaveBeenCalled();
        expect(triggerStub.args[0][0]).toEqual('duplicate:field');
    });

    it('Expiration date dependencies. Error when expiration date is lower than publishing.', function() {
        var fakeModel = app.data.createBean(moduleName);
        fakeModel.set('exp_date', '2010-10-10');
        fakeModel.set('active_date', '');
        fakeModel.set('status', 'published-in');
        var errors = {};
        sandbox.stub(fakeModel, 'getSyncedAttributes');
        sandbox.stub(fakeModel, 'changedAttributes', function() {
            return [];
        });
        sandbox.stub(app.date.fn, 'formatServer', function() {
            return '2011-11-11';
        });

        // Publish article with exp date. Should set the active_date automatically.
        view._doValidateExpDateField(fakeModel, [], errors, sandbox.stub());
        expect(errors['active_date']).not.toBe(undefined);
    });

    it('Active date dependencies. Approved status requires publishing date.', function() {
        var errors = {};
        var fakeModel = app.data.createBean(moduleName);
        fakeModel.set('active_date', '');
        fakeModel.set('status', 'approved');
        // Check if the field on view.
        var getFieldStub = sandbox.stub(view, 'getField', function(name) {
            return {name: name};
        });
        view._doValidateActiveDateField(fakeModel, [], errors, sandbox.stub());
        expect(errors['active_date']).not.toBe(undefined);
        getFieldStub.restore();

        // The field isn't on view.
        sandbox.stub(view, 'getField');
        view._doValidateActiveDateField(fakeModel, [], errors, sandbox.stub());
        expect(errors['status']).not.toBe(undefined);
    });

    it('Change publishing and expiration dates to current on manual change after validation.', function() {
        var fakeModel = app.data.createBean(moduleName);
        var expectedDate = '2010-10-10';
        fakeModel.set('active_date', '');
        fakeModel.set('exp_date', '');
        sandbox.stub(fakeModel, 'getSyncedAttributes');
        sandbox.stub(fakeModel, 'changedAttributes', function() {
            return [];
        });
        sandbox.stub(app.date.fn, 'formatServer', function() {
            return expectedDate;
        });

        fakeModel.set('status', 'expired');
        view._validationComplete(fakeModel, true);
        expect(fakeModel.get('exp_date')).toEqual(expectedDate);

        fakeModel.set('status', 'published-in');
        view._validationComplete(fakeModel, true);
        expect(fakeModel.get('active_date')).toEqual(expectedDate);
    });

});
