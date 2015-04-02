describe('Sugar7.Routes', function() {
    var app, loadViewStub, buildKeyStub, getStub, setStub;

    beforeEach(function() {
        app = SugarTest.app;
        loadViewStub = sinon.collection.stub(app.controller, 'loadView');
        buildKeyStub = sinon.collection.stub(app.user.lastState, 'buildKey');
        getStub = sinon.collection.stub(app.user.lastState, 'get');
        setStub = sinon.collection.stub(app.user.lastState, 'set');

        SugarTest.loadFile('../include/javascript', 'sugar7', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
        app.routing.start();
    });

    afterEach(function() {
        sinon.collection.restore();
    });

    describe('Routes', function() {
        var mockKey = 'foo:key',
            oldIsSynced;

        beforeEach(function () {
            oldIsSynced = app.isSynced;
            app.isSynced = true;
            sinon.collection.stub(app.router, 'index');
            sinon.collection.stub(app.router, 'hasAccessToModule').returns(true);
            sinon.collection.stub(app.api, 'isAuthenticated').returns(true);
            sinon.collection.stub(app, 'sync');
            buildKeyStub.returns(mockKey);
        });

        afterEach(function() {
            app.isSynced = oldIsSynced;
            app.router.navigate('', {trigger: true});
            Backbone.history.stop();
        });

        describe('Activities', function() {
            it('should set last visited Home to activity stream when routing to activity stream', function() {
                app.router.navigate('activities', {trigger: true});

                expect(setStub).toHaveBeenCalledWith(mockKey, 'activities');
                expect(loadViewStub).toHaveBeenCalledWith({
                    layout: 'activities',
                    module: 'Activities',
                    skipFetch: true
                });
            });
        });

        describe('homeRecord', function() {
            var recordStub;

            beforeEach(function() {
                recordStub = sinon.collection.stub(app.router, 'record');
            });

            it('should set last visited Home to dashboard when routing to a dashboard', function() {
                app.router.navigate('Home/test_ID', {trigger: true});

                expect(setStub).toHaveBeenCalledWith(mockKey, 'dashboard');
                expect(recordStub).toHaveBeenCalledWith('Home', 'test_ID');
            });
        });

        describe('Home', function() {
            var redirectStub,
                listStub;

            beforeEach(function () {
                redirectStub = sinon.collection.stub(app.router, 'redirect');
                listStub = sinon.collection.stub(app.router, 'list');
            });

            using('homeOptions', [
                {
                    value: 'dashboard',
                    redirectCalled: false,
                    listRouteCalled: true
                },
                {
                    value: 'activities',
                    redirectCalled: true,
                    listRouteCalled: false
                }
            ], function(option) {
                it('should navigate to the appropriate route according to the lastState', function() {
                    getStub.returns(option.value);
                    app.router.navigate('Home', {trigger: true});

                    expect(redirectStub.calledWith('#activities')).toBe(option.redirectCalled);
                    expect(listStub.called).toBe(option.listRouteCalled);
                });
            });
        });

        describe('404', function() {
            var errorStub, appMetaStub;

            beforeEach(function() {
                appMetaStub = sinon.collection.stub(app.metadata, 'getModule');
                errorStub = sinon.collection.stub(app.error, 'handleHttpError');
            });

            // FIXME: We should ensure that current routes work as expected
            // with valid modules as well, aka, testing route callbacks; will
            // be completed in SC-2761.
            using('module routes', [
                'notexists',
                'notexists/test_ID',
                'notexists/create',
                'notexists/vcard-import',
                'notexists/config',
                'notexists/layout/test_view',
                'notexists/test_ID/edit',
                'notexists/test_ID/layout/test_view',
                'notexists/test_ID/layout/test_view/edit'
            ], function(route) {
                it('should redirect to 404 if module does not exist', function() {
                    app.router.navigate(route, {trigger: true});
                    expect(errorStub).toHaveBeenCalledWith({status: 404});
                });
            });
        });
    });

    describe("Before Route Show Wizard Check", function() {
        var hasAccessStub;

        beforeEach(function() {
            hasAccessStub = sinon.stub(app.acl, 'hasAccess');
            hasAccessStub.returns(true);
        });

        afterEach(function() {
            hasAccessStub.restore();
            app.user.unset('show_wizard', {silent: true});
        });

        it("should return false if user's show_wizard true", function() {
            var route = 'record';
            app.user.set('show_wizard', true);
            var response = app.routing.triggerBefore('route', {route:route});
            expect(response).toBe(false);
        });
    });

    describe("Before Route Access Check", function() {
        var hasAccessStub;

        beforeEach(function() {
            hasAccessStub = sinon.stub(app.acl, 'hasAccess');
            hasAccessStub.withArgs('view', 'Foo').returns(true);
            hasAccessStub.withArgs('view', 'Bar').returns(false);
        });

        afterEach(function() {
            hasAccessStub.restore();
        });

        it("should continue to route if routing to the record view and user has access", function() {
            var route = 'record',
                args = ['Foo'];
            var response = app.routing.triggerBefore("route", {route:route, args:args})

            expect(response).toBe(true);
        });

        it("should continue to route if routing to a view that is not on the check access list", function() {
            var route = 'baz',
                args = ['Foo'];
            var response = app.routing.triggerBefore("route", {route:route, args:args})

            expect(response).toBe(true);
        });

        it("should stop route if routing to the record view and user is missing access", function() {
            var route = 'record',
                args = ['Bar'];
            var response = app.routing.triggerBefore("route", {route:route, args:args})

            expect(response).toBe(false);
        });
    });

    describe('Logout event', function() {

        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.set();
            SugarTest.app.data.declareModels();
            SugarTest.declareData('base', 'Filters');
        });

        it('should clear the filters from cache', function() {
            var filters = app.data.getCollectionClasses().Filters;
            sinon.collection.spy(filters.prototype, 'resetFiltersCacheAndRequests');
            app.trigger('app:logout');
            expect(filters.prototype.resetFiltersCacheAndRequests).toHaveBeenCalled();
        });

    });
});
