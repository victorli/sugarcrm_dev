describe('KBContents.Base.Views.PanelTopForRevisions', function() {

    var app, view, sandbox, context, moduleName = 'KBContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        context = app.context.getContext({
            module: moduleName
        });
        context.set('model', new app.Bean());
        context.parent = new Backbone.Model();

        SugarTest.loadComponent(
            'base',
            'view',
            'panel-top-for-revisions',
            moduleName
        );
        SugarTest.loadHandlebarsTemplate(
            'panel-top-for-revisions',
            'view',
            'base',
            null,
            moduleName
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'panel-top-for-revisions',
            null,
            context,
            moduleName
        );
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        Handlebars.templates = {};
        view = null;
    });

    describe('createRelatedClicked()', function() {
        var createRelatedContentStab, contextParentGetStub;

        beforeEach(function() {
            createRelatedContentStab = sandbox.stub(view, 'createRelatedContent');
        });

        it('should call createRelatedContent() when parentModule exists',
            function() {
                contextParentGetStub = sandbox.stub(
                    context.parent,
                    'get',
                    function() {
                        return {name: 'Test'};
                    }
                );
                view.createRelatedClicked();
                expect(contextParentGetStub).toHaveBeenCalledWith('model');
                expect(createRelatedContentStab).toHaveBeenCalledWith(
                    {name: 'Test'},
                    view.CONTENT_REVISION
                );
            }
        );

        it('should not call createRelatedContent() when parentModule not exists',
            function() {
                contextParentGetStub = sandbox.stub(
                    context.parent,
                    'get',
                    function() {
                        return undefined;
                    }
                );
                view.createRelatedClicked();
                expect(contextParentGetStub).toHaveBeenCalledWith('model');
                expect(createRelatedContentStab).not.toHaveBeenCalled();
            }
        );
    });
});
