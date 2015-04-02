describe('KBContents.Base.Views.PanelTopForLocalizations', function() {

    var app, view, sandbox, context, moduleName = 'KBContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        context = app.context.getContext({
            module: moduleName
        });
        SugarTest.loadFile(
            '../modules/KBContents/clients/base/plugins',
            'KBContent',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });
        context.set('model', new app.Bean());
        context.parent = new Backbone.Model();

        SugarTest.loadComponent(
            'base',
            'view',
            'panel-top-for-localizations',
            moduleName
        );
        SugarTest.loadHandlebarsTemplate(
            'panel-top-for-localizations',
            'view',
            'base',
            null,
            moduleName
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'panel-top-for-localizations',
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
            createRelatedContentStab = sandbox.stub(view, 'createRelatedContent', $.noop());
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
                    view.CONTENT_LOCALIZATION
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
