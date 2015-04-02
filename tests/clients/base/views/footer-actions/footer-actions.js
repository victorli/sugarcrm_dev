describe('Base.View.FooterActions', function () {
    var view,
        sandbox,
        app = SUGAR.App;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('footer-actions', 'view', 'base');
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', 'Contacts', 'footer-actions');
        sandbox = sinon.sandbox.create();
    });

    afterEach(function () {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
    });

    describe('Shortcuts button', function() {
        it('should display if shortcuts are enabled and if user is authenticated', function() {
            sandbox.stub(app.api, 'isAuthenticated', function() {
                return true;
            });
            sandbox.stub(app.shortcuts, 'isEnabled', function() {
                return true;
            });

            view.render();

            expect(view.$('[data-action=shortcuts]').length).not.toBe(0);
        });

        it('should not display if shortcuts are disabled', function() {
            sandbox.stub(app.api, 'isAuthenticated', function() {
                return true;
            });
            sandbox.stub(app.shortcuts, 'isEnabled', function() {
                return false;
            });

            view.render();

            expect(view.$('[data-action=shortcuts]').length).toBe(0);
        });

        it('should not display if user is not authenticated', function() {
            sandbox.stub(app.api, 'isAuthenticated', function() {
                return false;
            });
            sandbox.stub(app.shortcuts, 'isEnabled', function() {
                return true;
            });

            view.render();

            expect(view.$('[data-action=shortcuts]').length).toBe(0);
        });
    });
});
