describe('KBContents.Base.Views.KBSDashletMostUseful', function() {

    var app, view, sandbox, context, meta, moduleName = 'KBContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        context = app.context.getContext({
            module: moduleName
        });
        context.parent = new Backbone.Model();
        context.parent.set('module', moduleName);
        context.prepare();
        meta = {
            config: false
        };

        sandbox.stub(context.parent, 'get', function() {
            return new Backbone.Collection();
        });

        SugarTest.loadComponent(
            'base',
            'view',
            'kbs-dashlet-most-useful',
            moduleName
        );
        SugarTest.loadHandlebarsTemplate(
            'kbs-dashlet-most-useful',
            'view',
            'base',
            null,
            moduleName
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'kbs-dashlet-most-useful',
            meta,
            context,
            moduleName
        );
        sandbox.stub(view.collection, 'sync');
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        Handlebars.templates = {};
        delete app.plugins.plugins['view']['Dashlet'];
        view = null;
    });

    describe('loadMoreData()', function() {
        var paginateStub, next_offset;

        beforeEach(function() {
            paginateStub = sandbox.stub(view.collection, 'paginate');
            next_offset = view.collection.next_offset;
        });

        afterEach(function() {
            view.collection.next_offset = next_offset;
        });

        it('should paginate when next_offset is great then zero', function() {
            view.collection.next_offset = 1;
            view.loadMoreData();
            expect(paginateStub).toHaveBeenCalled();
        });

        it('should not paginate when next_offset is zero or less then zero', function() {
            view.collection.next_offset = 0;
            view.loadMoreData();
            expect(paginateStub).not.toHaveBeenCalled();
        });
    });

    describe('loadData()', function() {
        var fetchStub, dataFetched;

        beforeEach(function() {
            fetchStub = sandbox.stub(view.collection, 'fetch');
            dataFetched = view.collection.dataFetched;
        });

        afterEach(function() {
            view.collection.dataFetched = dataFetched;
        });

        it('should fetch data when data not fetched yet', function() {
            view.collection.dataFetched = false;
            view.loadData();
            expect(fetchStub).toHaveBeenCalled();
        });

        it('should not fetch data when data already fetched', function() {
            view.collection.dataFetched = true;
            view.loadData();
            expect(fetchStub).not.toHaveBeenCalled();
        });
    });

    describe('Render when collection changed', function() {
        var renderStub;
        beforeEach(function() {
            renderStub = sandbox.stub(view, 'render');
            view.bindDataChange();
        });

        it('should render when added item to collection', function() {
            view.collection.trigger('add');
            expect(renderStub).toHaveBeenCalled();
        });

        it('should render when reset collection', function() {
            view.collection.trigger('reset');
            expect(renderStub).toHaveBeenCalled();
        });

        it('should render when removed item from collection', function() {
            view.collection.trigger('remove');
            expect(renderStub).toHaveBeenCalled();
        });
    });
});
