/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

describe("ForecastWorksheets.View.RecordList", function() {

    var app, view, layout, moduleName = 'ForecastWorksheets';

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.testMetadata.init();
        SugarTest.loadFile("../include/javascript/sugar7", "utils", "js", function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
        SugarTest.loadFile('../include/javascript/sugar7/plugins', 'DirtyCollection', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
        
        SugarTest.loadPlugin('ClickToEdit');
        SugarTest.loadPlugin('DirtyCollection');

        app.user.set({'id': 'test_userid'});

        SugarTest.loadHandlebarsTemplate('base', 'field', 'base', 'noaccess');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'recordlist', moduleName);
        SugarTest.testMetadata.addViewDefinition("list", {
            "favorite": false,
            "selection": {
                "type": "multi",
                "actions": []
            },
            "rowactions": {
                "actions": []
            },
            "panels": [
                {
                    "name": "panel_header",
                    "header": true,
                    "fields": ["name", "likely_case", "best_case", "worst_case"]
                }
            ]
        }, "ForecastWorksheets");

        SugarTest.testMetadata.set();
        app.data.reset();

        app.data.declareModel(moduleName, SugarTest.app.metadata.getModule(moduleName));

        context = app.context.getContext();
        context.set({
            module: moduleName,
            'selectedUser': app.user.toJSON(),
            'selectedRanges': [],
            'selectedTimePeriod': 'test_timeperiod',
            'selectedTimePeriodStartEnd': {
                'start': '2014-01-01',
                'end': '2014-03-31'
            }
        });
        context.parent = undefined;
        context.prepare();

        layout = SugarTest.createLayout("base", moduleName, "list", null, null);
        view = SugarTest.createView("base", moduleName, "recordlist", null, context, true, layout, true);

    });

    afterEach(function() {
        view.dispose();
        layout.dispose();
        delete app.plugins.plugins['field']['ClickToEdit'];
        delete app.plugins.plugins['view']['CteTabbing'];
        delete app.plugins.plugins['view']['DirtyCollection'];
        app.user.unset('id');
    });

    it("should have default recordlist plugins defined", function() {
        expect(_.indexOf(view.plugins, 'ListColumnEllipsis')).not.toEqual(-1);
        expect(_.indexOf(view.plugins, 'ErrorDecoration')).not.toEqual(-1);
        expect(_.indexOf(view.plugins, 'Editable')).not.toEqual(-1);
    });

    it("should have additional plugins defined", function() {
        expect(_.indexOf(view.plugins, 'CteTabbing')).not.toEqual(-1);
        expect(_.indexOf(view.plugins, 'DirtyCollection')).not.toEqual(-1);
    });

    it('should not have ReorderableColumns plugin', function() {
        expect(_.indexOf(view.plugins, 'ReorderableColumns')).toEqual(-1);
    });

    it('should not have MassCollection plugin', function() {
        expect(_.indexOf(view.plugins, 'MassCollection')).toEqual(-1);
    });

    describe('beforeRenderCallback', function() {
        describe('when layout hidden', function() {
            var layoutShowStub, layoutVisibleStub;
            beforeEach(function() {
                layoutShowStub = sinon.stub(view.layout, 'show', function() {
                });
                layoutVisibleStub = sinon.stub(view.layout, 'isVisible', function() {
                    return false;
                });
            });
            afterEach(function() {
                layoutShowStub.restore();
                layoutVisibleStub.restore();
            });

            it('should return true when user is not a manager and call show', function() {
                view.selectedUser.is_manager = false;
                var ret = view.beforeRenderCallback();
                expect(ret).toBeTruthy();
                expect(layoutShowStub).toHaveBeenCalled();
            });
            it('should return true when user is manager and showOpps is true and call show', function() {
                view.selectedUser.is_manager = true;
                view.selectedUser.showOpps = true;
                var ret = view.beforeRenderCallback();
                expect(ret).toBeTruthy();
                expect(layoutShowStub).toHaveBeenCalled();
            });
            it('should return false when user is manager and showOpps is false', function() {
                view.selectedUser.is_manager = true;
                view.selectedUser.showOpps = false;
                var ret = view.beforeRenderCallback();
                expect(ret).toBeFalsy();
                expect(layoutShowStub).not.toHaveBeenCalled();
            });
        });

        describe('when layout visible', function() {
            var layoutHideStub, layoutVisibleStub;
            beforeEach(function() {
                layoutHideStub = sinon.stub(view.layout, 'hide', function() {
                });
                layoutVisibleStub = sinon.stub(view.layout, 'isVisible', function() {
                    return true;
                });
            });
            afterEach(function() {
                layoutHideStub.restore();
                layoutVisibleStub.restore();
            });

            it('should return false when user is manager and showOpps is false', function() {
                view.selectedUser.is_manager = true;
                view.selectedUser.showOpps = false;
                var ret = view.beforeRenderCallback();
                expect(ret).toBeFalsy();
                expect(layoutHideStub).toHaveBeenCalled();
            });
        });
    });

    describe('renderCallback', function() {
        var layoutShowStub, layoutHideStub, layoutHideStub;
        beforeEach(function() {
            layoutShowStub = sinon.stub(view.layout, 'show', function() {
            });
            layoutHideStub = sinon.stub(view.layout, 'hide', function() {
            });
        });
        afterEach(function() {
            layoutShowStub.restore();
            layoutHideStub.restore();
            layoutVisibleStub.restore();
        });

        it('should run hide when user is a manager and show opps is false', function() {
            layoutVisibleStub = sinon.stub(view.layout, 'isVisible', function() {
                return true;
            });

            view.selectedUser.is_manager = true;
            view.selectedUser.showOpps = false;
            view.renderCallback();

            expect(layoutShowStub).not.toHaveBeenCalled();
            expect(layoutHideStub).toHaveBeenCalled();
        });

        it('should run show when user is a manager and show opps is true', function() {
            layoutVisibleStub = sinon.stub(view.layout, 'isVisible', function() {
                return false;
            });

            tplViewStub = sinon.stub(app.template, 'getView', function(){
                return function() {};
            });

            view.selectedUser.is_manager = true;
            view.selectedUser.showOpps = true;
            view.renderCallback();

            expect(layoutShowStub).toHaveBeenCalled();
            expect(layoutHideStub).not.toHaveBeenCalled();

            tplViewStub.restore();
        });
    });

    describe("filteredCollection", function() {
        beforeEach(function() {
            // add some models
            var m1 = new Backbone.Model({'name': 'test1', 'commit_stage': 'include', 'date_closed': '2014-01-05'});
            var m2 = new Backbone.Model({'name': 'test2', 'commit_stage': 'include', 'date_closed': '2014-01-05'});
            var m3 = new Backbone.Model({'name': 'test3', 'commit_stage': 'exclude', 'date_closed': '2014-01-05'});

            view.collection.add([m1, m2, m3]);
        });
        afterEach(function() {
            view.filters = [];
            view.collection.reset();
            view.filteredCollection.reset();
        });

        it("with no filters, filteredCollection should contain 3 records", function() {
            view.filterCollection();
            expect(view.filteredCollection.length).toEqual(3);
        });
        it("with include filter, filteredCollection should contain 2 records", function() {
            view.filters = ['include'];
            view.filterCollection();
            expect(view.filteredCollection.length).toEqual(2);
        });
        it("with exclude filter, filteredCollection should contain 1 records", function() {
            view.filters = ['exclude'];
            view.filterCollection();
            expect(view.filteredCollection.length).toEqual(1);
        });
        it("with 2 filters, filteredCollection should contain 3 records", function() {
            view.filters = ['include', 'exclude'];
            view.filterCollection();
            expect(view.filteredCollection.length).toEqual(3);
        });
    });

    describe("checkForDraftRows", function() {
        var layoutStub, ctxStub;
        beforeEach(function() {
            // add some models
            var m1 = new Backbone.Model({'name': 'test1', 'date_modified': '2013-05-14 16:20:15', 'date_closed': '2014-01-05'});
            view.collection.add([m1]);

            // set that we can edit
            view.canEdit = true;
            layoutStub = sinon.stub(view.layout, 'isVisible', function() {
                return true;
            });

            context = app.context.getContext();
            context.set({
                module: 'Forecasts'
            });
            context.prepare();

            ctxStub = sinon.stub(context, 'trigger', function() {
            });

            view.context.parent = context;
        });
        afterEach(function() {
            view.collection.reset();
            layoutStub.restore();
            view.context.parent = undefined;
        });

        it("should not trigger event", function() {
            view.checkForDraftRows('2013-05-14 16:21:15');
            expect(ctxStub).not.toHaveBeenCalled();
        });

        it("should trigger event", function() {
            view.checkForDraftRows('2013-05-14 16:19:15');
            expect(ctxStub).toHaveBeenCalled();
        });

        it('should trigger when date is undefined and has rows', function() {
            view.checkForDraftRows(undefined);
            expect(ctxStub).toHaveBeenCalled();
        });

        it('should not trigger event when date is undefined and collection is empty', function() {
            view.collection.reset();

            // should be called during reset
            expect(ctxStub).toHaveBeenCalled();

            ctxStub.restore();
            ctxStub = sinon.stub(context, 'trigger', function() {});
            view.checkForDraftRows(undefined);

            // should not be called during checkForDraftRows when undefined
            expect(ctxStub).not.toHaveBeenCalled();
        });

        it('should call layout.once when layout not visible but can edit', function() {
            layoutStub.restore();
            layoutStub = sinon.stub(view.layout, 'isVisible', function() {
                return false;
            });
            sinon.stub(view.layout, 'once');

            view.checkForDraftRows(undefined);

            expect(view.layout.once).toHaveBeenCalled();

            view.layout.once.restore();

        });

        it('should not call layout.once when layout not visible and can not edit', function() {
            layoutStub.restore();
            layoutStub = sinon.stub(view.layout, 'isVisible', function() {
                return false;
            });
            view.canEdit = false;
            sinon.stub(view.layout, 'once');
            view.checkForDraftRows(undefined);

            expect(view.layout.once).not.toHaveBeenCalled();

            view.layout.once.restore();
        });

    });

    describe('updateSelectedUser', function() {
        var collectionFetchStub;
        beforeEach(function() {
            collectionFetchStub = sinon.stub(view.collection, 'fetch', function() {
            });
        });
        afterEach(function() {
            collectionFetchStub.restore()
            view.canEdit = false;
        });
        it("should change canEdit to be true", function() {
            view.updateSelectedUser({id: 'test_userid'});
            expect(view.canEdit).toBeTruthy();
        });
        it("should change canEdit to be false", function() {
            view.updateSelectedUser({id: 'test_user2'});
            expect(view.canEdit).toBeFalsy();
        });
        it("should call collection.fetch() is_manager is False", function() {
            view.updateSelectedUser({id: 'test_user2', is_manager: false});
            expect(collectionFetchStub).toHaveBeenCalled();
        });
        it("should call collection.fetch() with is_manager is True and showOpps is True", function() {
            view.updateSelectedUser({id: 'test_userid', is_manager: true, showOpps: true});
            expect(collectionFetchStub).toHaveBeenCalled();
        });
    });

    describe('updateTimeperiod', function() {
        var collectionFetchStub, layoutVisibleStub;

        beforeEach(function() {
            collectionFetchStub = sinon.stub(view.collection, 'fetch', function() {
            });
        });
        afterEach(function() {
            collectionFetchStub.restore()
            layoutVisibleStub.restore()
        });

        it('should update selectedTimePeriod and call collection.fetch when layout is visible', function() {
            layoutVisibleStub = sinon.stub(view.layout, 'isVisible', function() {
                return true;
            });
            view.updateSelectedTimeperiod({id: 'hello world'});

            expect(view.selectedTimeperiod).toEqual({id: 'hello world'});
            expect(collectionFetchStub).toHaveBeenCalled();
        });

        it('should update selectedTimePeriod and not call collection.fetch when layout is not visible', function() {
            layoutVisibleStub = sinon.stub(view.layout, 'isVisible', function() {
                return false;
            });
            view.updateSelectedTimeperiod({id: 'hello world'});

            expect(view.selectedTimeperiod).toEqual({id: 'hello world'});
            expect(collectionFetchStub).not.toHaveBeenCalled();
        })


    });

    describe('saveWorksheet', function() {
        var m, saveStub;
        beforeEach(function() {
            m = new Backbone.Model({'hello': 'world'});
            saveStub = sinon.stub(m, 'save', function() {
            });
            view.collection.add(m);
        });

        afterEach(function() {
            view.collection.reset();
            saveStub.restore();
            m = undefined;
        });

        it('should return zero with no dirty models', function() {
            expect(view.saveWorksheet()).toEqual(0);
        });

        it('should return 1 when one model is dirty', function() {
            m.set({'hello': 'jon1'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
        });

        describe("Forecasts worksheet save dirty models with correct timeperiod after timeperiod changes", function() {
            var m, saveStub, safeFetchStub;
            beforeEach(function() {
                m = new Backbone.Model({'hello': 'world'});
                saveStub = sinon.stub(m, 'save', function() {
                });
                safeFetchStub = sinon.stub(view.collection, 'fetch', function() {
                });
                view.collection.add(m);
            });

            afterEach(function() {
                view.collection.reset();
                saveStub.restore();
                safeFetchStub.restore();
                m = undefined;
            });

            it('model should contain the old timeperiod id', function() {
                m.set({'hello': 'jon1'});
                view.updateSelectedTimeperiod('my_new_timeperiod');
                expect(view.saveWorksheet()).toEqual(1);
                expect(saveStub).toHaveBeenCalled();
                expect(safeFetchStub).toHaveBeenCalled();

                expect(m.get('timeperiod_id')).toEqual('test_timeperiod');
                expect(view.selectedTimeperiod).toEqual('my_new_timeperiod');
                expect(view.dirtyTimeperiod).toEqual(undefined);
            });
        });

    });

    describe('sync', function() {
        var stubs = [], options = {};
        beforeEach(function() {
            stubs.push(sinon.stub(app.api, 'buildURL', function() {}));
            stubs.push(sinon.stub(app.api, 'call', function() {}));
            stubs.push(sinon.stub(app.data, 'getSyncCallbacks', function() {}));
        });

        afterEach(function() {
            _.each(stubs, function(stub) {
                stub.restore();
            });
            options = {}
        });

        describe('timeperiod_id', function(){
            afterEach(function() {
               options = {}
            });

            it('should be set to view.selectedTimeperiod', function() {
                view.sync('read', view.collection, options);
                expect(_.isUndefined(options.params.timeperiod_id)).toBeFalsy();
                expect(options.params.timeperiod_id).toEqual(view.selectedTimeperiod);
            });
        });

        describe('user_id', function(){
            afterEach(function() {
               options = {}
            });

            it('should be set to view.selectedUser.id', function() {
                view.sync('read', view.collection, options);
                expect(_.isUndefined(options.params.user_id)).toBeFalsy();
                expect(options.params.user_id).toEqual(view.selectedUser.id);
            });
        });

        describe("orderBy should", function() {
            beforeEach(function() {
                options = {}
            });

            afterEach(function() {
                delete view.collection.orderBy;
                options = {}
            });

            it('not be set', function() {
                view.sync('read', view.collection, options);
                expect(_.isUndefined(options.params.order_by)).toBeTruthy();
            });

            it('be set', function() {
                view.collection.orderBy = {
                    'field' : 'best_case',
                    'direction' : '_desc',
                    'column_name' : 'best_case'
                }
                view.sync('read', view.collection, options);
                expect(_.isUndefined(options.params.order_by)).toBeFalsy();
                expect(options.params.order_by).toEqual('best_case:_desc');
            });

            it('should convert parent_name to name', function() {
                view.collection.orderBy = {
                    'field' : 'parent_name',
                    'direction' : '_desc',
                    'column_name' : 'parent_name'
                }
                view.sync('read', view.collection, options);
                expect(options.params.order_by).toEqual('name:_desc');
                expect(view.collection.orderBy.field).toEqual('parent_name');
            });
        });
    });

    describe('setRowActionButtonStates', function() {
        var fieldDef = {}, field, fieldStub, viewFields = [];
        beforeEach(function() {
            fieldDef = {
                'event': 'list:preview:fire',
                'type': 'button',
                'name': 'test_btn'
            }
            viewFields = view.fields;

            field = SugarTest.createField('base', 'rowaction', 'rowaction', 'list', fieldDef);
            fieldStub = sinon.stub(field, 'setDisabled', function() {});
            view.fields = [field];
        });

        afterEach(function() {
            view.fields = viewFields;
            fieldStub.restore();
            delete field;
        });

        it('should call field.setDisabled(false)', function() {
            field.model.set('parent_deleted', 0);
            view.setRowActionButtonStates();
            expect(fieldStub).toHaveBeenCalledWith(false);
        });

        it('should call field.setDisabled(true) ', function() {
            field.model.set('parent_deleted', 1);
            view.setRowActionButtonStates();
            expect(fieldStub).toHaveBeenCalledWith(true);
        });
    });
    
    describe("when Tab is pressed", function() {
        beforeEach(function() {
            e = {
                which: 9,
                shiftKey: false,
                preventDefault: function(){}
            };
            view.currentIndex = 0;
            view.currentCTEList = [
                $("<div>"),
                $("<div>"),
                $("<div>"),
                $("<div>")
            ];
        });
        
        describe("when shift is not pressed", function() {
            beforeEach(function() {
                view.handleKeyDown(e);
            });
            
            it("should increment the currentIndex", function() {
                expect(view.currentIndex).toBe(1);
                
            });
        });
        
        describe("when shift is pressed", function() {
            beforeEach(function() {
                e.shiftKey = true;
                view.handleKeyDown(e);
            });
            it("should should reset the currentIndex to the end", function() {
                expect(view.currentIndex).toBe(3);
            });
        });
    });
    
    describe("when resetCTEFields is called", function() {
        var sandbox = sinon.sandbox.create();
        beforeEach(function() {
            
            view.currentIndex = 1;
            view.currentCTEList = [
                $("<div>"),
                $("<div>"),
                $("<div>"),
                $("<div>")
            ];
            sandbox.stub(view.$el, "find", function(){
                return [
                    $("<div>"),
                    $("<div>"),
                    $("<div>")
                ];
            });
            view.resetCTEList();
        });
        
        afterEach(function() {
            sandbox.restore();
        });
        
        it("should set currentIndex to 0", function() {
            expect(view.currentIndex).toEqual(0);
        });
    });

    describe('getCommitTotals', function() {
        var m1, m2, m3;
        beforeEach(function() {
            // add some models
            m1 = new Backbone.Model({
                'name': 'test1',
                'commit_stage': 'include',
                'likely_case': 500,
                'date_closed': '2014-01-05'
            });
            m2 = new Backbone.Model({
                'name': 'test2',
                'commit_stage': 'include',
                'likely_case': 500,
                'date_closed': '2014-01-05'
            });
            m3 = new Backbone.Model({
                'name': 'test3',
                'commit_stage': 'exclude',
                'likely_case': 500,
                'date_closed': '2014-01-05'
            });

            view.collection.add([m1, m2, m3]);
        });

        afterEach(function() {
            m1 = null;
            m2 = null;
            m3 = null;
            view.collection.reset();
        });

        it('will return object with correct values', function() {
            var totals = view.getCommitTotals();

            expect(totals.likely_case).toEqual(1000);
            expect(totals.overall_amount).toEqual(1500);
        });
        it('will not include model that is outside timeperiod', function() {
            m1.set('date_closed', '2013-01-05');
            var totals = view.getCommitTotals();

            expect(totals.likely_case).toEqual(500);
            expect(totals.overall_amount).toEqual(1000);
        });

        describe('filteredTotals', function() {
            var getStub;
            beforeEach(function() {
                getStub = sinon.collection.stub(view.context, 'get')
                    .withArgs('selectedTimePeriodStartEnd').returns({end: '2014-03-31', start: '2014-01-01'});
            });

            afterEach(function() {
                sinon.collection.restore();
            });

            it('will only include items when filter is include', function() {
                getStub.withArgs('selectedRanges').returns(['include']);
                var totals = view.getCommitTotals();

                expect(totals.filtered_amount).toEqual(1000);
                expect(totals.overall_amount).toEqual(1500);
            });

            it('will include all items when filter is include and exclude', function() {
                getStub.withArgs('selectedRanges').returns(['include', 'exclude']);
                var totals = view.getCommitTotals();

                expect(totals.filtered_amount).toEqual(1500);
                expect(totals.overall_amount).toEqual(1500);
            });

            it('will include all items when filter is empty', function() {
                getStub.withArgs('selectedRanges').returns([]);
                var totals = view.getCommitTotals();

                expect(totals.filtered_amount).toEqual(1500);
                expect(totals.overall_amount).toEqual(1500);
            });
        });
    });
});
