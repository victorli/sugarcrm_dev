describe('Base.View.SelectionListContext', function() {
    var view, layout, app, moduleName, viewName, model1, event, renderStub;
    beforeEach(function() {
        moduleName = 'Accounts';
        viewName = 'selection-list-context';
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', viewName);

        var context = app.context.getContext();
        context.set({
            mass_collection: new Backbone.Collection([
                {id: 1, name: 'toto'}, {id: 2, name: 'tata'}, {id: 3, name: 'titi'}
            ])
        });

        view = SugarTest.createView('base', moduleName, viewName, null, context, null, layout);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        view = null;
    });

    describe('Initialize:', function() {
        it('should initialize view properties', function() {
           expect(view.maxPillsDisplayed).toBeDefined();
           expect(view.pills).toBeDefined();
        });
    });

    describe('addPill:', function() {
        beforeEach(function() {
            model1 = new Backbone.Model({name: 'toto'});
            renderStub = sinon.collection.stub(view, 'render');
        });
        it('should add a model to the pills array', function() {
            view.addPill(model1);

            expect(_.find(view.pills, function(pill) {
                return _.isEqual(pill.id, model1.id);
            })).toBeDefined();
        });
        it('should render the view', function() {
            view.addPill(model1);

            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('removePill:', function() {
        beforeEach(function() {
            model1 = {id: '1', name: 'toto'};
            view.pills = [{id: '1', name: 'toto'}, {id: '2', name: 'tata'}, {id: '3', name: 'titi'}];
            renderStub = sinon.collection.stub(view, 'render');

        });
        it('should remove the passed model from the pills array', function() {
            var expectedPillsArray = [{id: '2', name: 'tata'}, {id: '3', name: 'titi'}];

            view.removePill(model1);

            expect(!_.contains(view.pills, model1)).toBe(true);
            expect(_.isEqual(view.pills, expectedPillsArray)).toBe(true);
        });
        it('should render the view', function() {
            view.removePill(model1);

            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('removeAllPill:', function() {
        beforeEach(function() {
            view.pills = [{id: '1', name: 'toto'}, {id: '2', name: 'tata'}, {id: '3', name: 'titi'}];
            renderStub = sinon.collection.stub(view, 'render');
        });
        it('should render the view with no pills', function() {
            view.removeAllPills();

            expect(_.isEmpty(view.pills)).toBe(true);
            expect(renderStub).toHaveBeenCalled();
        });
        it('should trigger a "mass_collection:clear" event', function() {
            var triggerStub = sinon.collection.stub(view.context, 'trigger');
            view.removeAllPills();

            expect(triggerStub).toHaveBeenCalledWith('mass_collection:clear');

        });
    });

    describe('closePill:', function() {
        beforeEach(function() {
            view.massCollection = view.context.get('mass_collection');
            model1 = view.massCollection.get('1');
            var pillHtml = ' <li class="select2-search-choice" data-id="'+ model1.id +'"><div>' +
                '<div class="ellipsis_inline" title="toto">toto</div>' +
                '</div><a class="select2-search-choice-close" data-close-pill="true" tabindex="-1"></a></li>';
            view.$el.append(pillHtml);

        });
        it('should remove the pill and trigger a "mass_collection:remove" event', function() {
            var removePillStub = sinon.collection.stub(view, 'removePill');
            sinon.collection.spy(view.context, 'trigger');

            view.closePill(model1.id.toString());

            expect(removePillStub).toHaveBeenCalledWith({id: '1'});
            expect(view.context.trigger).toHaveBeenCalledWith('mass_collection:remove', model1);
        });
    });

    //FIXME: SC-4092: Need to fix resetPills method first before running this test.
    xdescribe('resetPills:', function() {
        it('should reset the pills array to match the given models array', function() {
            var models = [{id: '1', name: 'toto'}, {id: '2', name: 'tata'}, {id: '3', name: 'titi'}];
            view.resetPills(models);

           expect(_.isEqual(view.pills, models)).toBe(true);
           expect(view.pills.length).toEqual(models.length);
        });
    });
});

