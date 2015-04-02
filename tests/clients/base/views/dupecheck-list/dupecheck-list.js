describe('Base.View.DupeCheckList', function() {
    var app,
        moduleName = 'Contacts',
        listMeta,
        layout,
        createBeanStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'selection-list');
        SugarTest.loadComponent('base', 'view', 'dupecheck-list');
        SugarTest.testMetadata.init();
        listMeta = {
            'template': 'list',
            'panels':[
                {
                    'name':'panel_header',
                    'fields':[
                        {
                            'name':'first_name'
                        },
                        {
                            'name':'name'
                        },
                        {
                            'name':'status'
                        }
                    ]
                }
            ]
        };
        SugarTest.testMetadata.set();
        layout = SugarTest.createLayout('base', 'Cases', 'list', null, null);
        createBeanStub = sinon.stub(app.data, 'createBean', function() {
            var bean = new Backbone.Model();
            bean.copy = $.noop;
            sinon.stub(bean, 'copy', function(sourceBean) {
                bean.set(sourceBean.attributes);
            });
            return bean;
        });
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
        createBeanStub.restore();
    });

    it('should turn off sorting on all fields', function(){
        var view = SugarTest.createView('base', moduleName, 'dupecheck-list', listMeta);
        view.layout = layout;
        view.render();

        expect(view.$('.sorting').length).toBe(0);
        expect(view.$('.sorting_asc').length).toBe(0);
        expect(view.$('.sorting_desc').length).toBe(0);
    });

    it('should removing all links except rowactions', function(){
        var htmlBefore = '<a href="javascript:void(0)">unwrapped</a><a href="" class="rowaction">wrapped</a>',
            htmlAfter = 'unwrapped<a href="" class="rowaction">wrapped</a>';

        var view = SugarTest.createView('base', moduleName, 'dupecheck-list', listMeta);
        view.layout = layout;
        view.$el = $('<div>' + htmlBefore + '</div>');
        view.render();
        expect(view.$el.html()).toEqual(htmlAfter);
    });

    // FIXME: Should refactor following case on FindDuplicates.js (Filed on SC-1764)
    xit('should be able to set the model via context', function(){
        var model, context, view;

        model = new Backbone.Model();
        model.set('foo', 'bar');
        context = app.context.getContext({
            module: moduleName,
            dupeCheckModel: model
        });
        context.prepare();

        view = SugarTest.createView('base', moduleName, 'dupecheck-list', listMeta, context);
        view.layout = layout;
        expect(view.model.get('foo')).toEqual('bar');
        expect(view.model.copy.callCount).toBe(1);
    });

    // FIXME: Should refactor following case on FindDuplicates.js (Filed on SC-1764)
    xit('should be calling the duplicate check api', function() {
        var ajaxStub;
        var view = SugarTest.createView('base', moduleName, 'dupecheck-list', listMeta);
        view.layout = layout;

        //mock out collectionSync which gets called by overridden sync
        view.collectionSync = function(method, model, options) {
            options.endpoint(options, {'success':$.noop});
        };

        ajaxStub = sinon.stub($, 'ajax', $.noop);

        view.fetchDuplicates(new Backbone.Model());
        expect(ajaxStub.lastCall.args[0].url).toMatch(/.*\/Contacts\/duplicateCheck/);

        ajaxStub.restore();
    });
});
