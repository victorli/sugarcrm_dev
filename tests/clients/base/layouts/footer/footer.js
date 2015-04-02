describe("BaseFooterLayout", function() {
    var layout, app, sinonSandbox;

    beforeEach(function() {
        sinonSandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'footer');
        SugarTest.loadHandlebarsTemplate('footer', 'layout', 'base');
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Users', 'footer', {});
    });

    afterEach(function() {
        sinonSandbox.restore();
    });

    describe("render", function(){
        it("should load the logo url and render the template", function(){
            var templateStub = sinonSandbox.stub(layout, 'template'),
                placeComponentStub = sinonSandbox.stub(layout, '_placeComponent');
            sinonSandbox.stub(app.metadata, 'getLogoUrl', function() { return 'my_logo.jpg'; });
            layout._components.push(new app.view.View({}));
            layout.render();
            expect(layout.logoUrl).toEqual('my_logo.jpg');
            expect(templateStub).toHaveBeenCalled();
            expect(placeComponentStub).toHaveBeenCalled();
        });
    });
});
