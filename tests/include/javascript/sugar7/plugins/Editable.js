describe("Editable Plugin", function() {
    var moduleName = 'Accounts',
        sinonSandbox, view, app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'headerpane');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'tabspanels');
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base', 'businesscard');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.addViewDefinition('record', {
            "panels": [{
                "name": "panel_header",
                "header": true,
                "fields": ["name", "description","case_number","type","created_by","date_entered","date_modified","modified_user_id"]
            }]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        sinonSandbox = sinon.sandbox.create();
        view = SugarTest.createView("base", moduleName, 'record', null, null);
    });

    afterEach(function() {
        view.dispose();
        SugarTest.app.view.reset();
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        view = null;
    });

    it("Should toggle a single field to edit modes", function() {
        view.render();
        view.model.set({
            name: 'Name',
            case_number: 123,
            description: 'Description'
        });

        var keys = _.keys(view.fields),
            randomFieldIndex = parseInt(Math.random() * (keys.length - 1), 10),
            randomField = view.fields[keys[randomFieldIndex]];

        expect(randomField.tplName).toBe(view.action);
        view.toggleField(randomField, true);
        expect(randomField.tplName).toBe('edit');
        view.toggleField(randomField);
        expect(randomField.tplName).toBe(view.action);
    });

    it("Should switch back to the previous mode when it triggers editableHandleMouseDown", function() {
        view.render();
        view.model.set({
            name: 'Name',
            case_number: 123,
            description: 'Description'
        });
        app.drawer = {
            _components: []
        };

        var keys = _.keys(view.fields),
            randomFieldIndex = parseInt(Math.random() * (keys.length - 1), 10),
            randomField = view.fields[keys[randomFieldIndex]];

        view.toggleField(randomField, true);
        expect(randomField.tplName).toBe('edit');
        view.editableHandleMouseDown({target: null}, randomField);
        expect(randomField.tplName).toBe(view.action);

        delete app.drawer;
    });

    it("Should toggle all selected fields to edit modes", function() {
        view.render();
        view.model.set({
            name: 'Name',
            case_number: 123,
            description: 'Description'
        });
        _.each(view.fields, function(field) {
            expect(field.tplName).toBe(view.action);
        });

        view.toggleFields(_.values(view.fields), true);

        waitsFor(function() {
            var last = _.last(_.keys(view.fields));
            return view.fields[last].tplName == 'edit';
        }, 'it took too long to wait switching view', 1000);

        runs(function() {
            _.each(view.fields, function(field) {
                expect(field.tplName).toBe('edit');
            });
        });
    });

    it('Should call the callback function when all fields have been toggled', function() {
        var callbackStub = sinonSandbox.stub();

        view.render();
        view.model.set({
            name: 'Name',
            case_number: 123,
            description: 'Description'
        });

        view.toggleFields(_.values(view.fields), true, callbackStub);

        waitsFor(function() {
            return callbackStub.calledOnce;
        }, 'Callback did not get called in time.', 1000);

        runs(function() {
            _.each(view.fields, function(field) {
                expect(field.tplName).toBe('edit');
            });
        });
    });

    describe("Warning unsaved changes", function() {
        var alertShowStub;
        beforeEach(function() {
            app.router = {
                navigate: $.noop,
                refresh: $.noop,
                hasAccessToModule: $.noop,
                bwcRedirect: $.noop
            };
            alertShowStub = sinonSandbox.stub(app.alert, "show");
            sinonSandbox.stub(Backbone.history, "getFragment");
        });

        afterEach(function() {
            sinonSandbox.restore();
        });

        it("should not alert warning message if unsaved changes are empty", function() {
            app.routing.triggerBefore("route");
            expect(alertShowStub).not.toHaveBeenCalledOnce();

            sinonSandbox.stub(view, "hasUnsavedChanges", function() {
                return false;
            });
            app.routing.triggerBefore("route");
            expect(alertShowStub).not.toHaveBeenCalledOnce();
        });

        // FIXME Unskip this test when MAR-2680 is fixed
        xit("should warn unsaved changes if router is changed with unsaved values", function() {

            sinonSandbox.stub(view, "hasUnsavedChanges", function() {
                return true;
            });
            app.routing.triggerBefore("route");
            expect(alertShowStub).toHaveBeenCalledOnce();
        });

        it("should warn unsaved changes if custom unsaved logic is applied with unsaved values", function() {
            sinonSandbox.stub(view, "hasUnsavedChanges", function() {
                return true;
            });
            var _callback = function() {};
            view.triggerBefore("unsavedchange", {callback: _callback});
            expect(alertShowStub).toHaveBeenCalledOnce();
        });


        it("ALL EDITABLE VIEWS MUST DISPOSE IN JASMINE TEST", function() {
            sinonSandbox.stub(view, "hasUnsavedChanges", function() {
                return true;
            });
            view.dispose();
            app.routing.triggerBefore("route");
            expect(alertShowStub).not.toHaveBeenCalledOnce();
        });
    });

});
