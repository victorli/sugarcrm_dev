describe("Base.Layouts.PreviewActivityStream", function() {
    var layout,
        app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('preview-activitystream', 'layout', 'base');
        SugarTest.loadComponent('base', 'layout', 'preview-activitystream');
        SugarTest.loadComponent('base', 'layout', 'activitystream');
        SugarTest.testMetadata.addLayoutDefinition('preview-activitystream', {
            type: 'preview-activitystream'
        });
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        layout = SugarTest.createLayout('base', 'Contacts', 'preview-activitystream');
    });

    afterEach(function() {
        layout.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe('Initialize', function() {
        it('Should not render any activities', function() {
            expect(layout.$('.activitystream-list').children().length).toBe(0);
        });
    });

    describe('fetchActivities()', function() {
        var getModuleStub;
        beforeEach(function() {
            getModuleStub = sinon.stub(app.metadata, 'getModule', function() {
                return {
                    isBwcEnabled: false
                };
            });
        });

        afterEach(function() {
            getModuleStub.restore();
        });

        it('Should fetch a collection of activities', function() {
            var collectionStub = sinon.stub(layout.collection, 'fetch');
            layout.fetchActivities(new Backbone.Model());
            expect(collectionStub.calledOnce).toBe(true);
            collectionStub.restore();
        });

        it('Should not fetch activities when showActivities is set to false', function() {
            var collectionStub = sinon.stub(layout.collection, 'fetch');
            layout.fetchActivities(new Backbone.Model(), null, null, null, false);
            expect(collectionStub.called).toBe(false);
            collectionStub.restore();
        });
    });

    describe('renderActivities()', function() {
        beforeEach(function() {
            layout._previewOpened = true;
        });

        afterEach(function() {
            layout._previewOpened = false;
        });


        it('Should render two activities when the collection size is two but add event', function() {
            var renderPostStub = sinon.stub(layout, 'renderPost');

            layout.collection.reset([new Backbone.Model(), new Backbone.Model()]);
            layout.renderActivities(layout.collection);

            expect(renderPostStub.calledTwice).toBe(true);
            renderPostStub.restore();
        });

        it('Should render two activities when the collection size is two but reset event', function() {
            var renderPostStub = sinon.stub(layout, 'renderPost');

            layout.collection.add([new Backbone.Model(), new Backbone.Model()]);

            expect(renderPostStub.calledTwice).toBe(true);
            renderPostStub.restore();
        });

        it('Should not render any activities when the collection is empty', function() {
            var renderPostStub = sinon.stub(layout, 'renderPost');

            layout.renderActivities(layout.collection);

            expect(renderPostStub.called).toBe(false);
            renderPostStub.restore();
        });

        //FIXME: MAR-2798 rewrite this to not cause an infinite loop
        xit('Should show activities when the collection is not empty', function() {
            var renderPostStub = sinon.stub(layout, 'renderPost');

            layout.collection.add(new Backbone.Model());
            layout.renderActivities(layout.collection);

            expect(layout.$el.css('display')).not.toBe('none');
            renderPostStub.restore();
        });

        it('Should hide activities when the collection is empty', function() {
            var renderPostStub = sinon.stub(layout, 'renderPost');

            layout.renderActivities(layout.collection);

            expect(layout.$el.css('display')).toBe('none');
            renderPostStub.restore();
        });

        //FIXME: MAR-2798 rewrite this to be compliant with new `_.delay` test changes
        xit('Should not render any posts until the preview pane has been opened', function() {
            var renderPostStub = sinon.stub(layout, 'renderPost'),
                fakeTimer = sinon.useFakeTimers();

            layout._previewOpened = false;
            layout.collection.reset(new Backbone.Model());
            layout.renderActivities(layout.collection);

            expect(renderPostStub.called).toBe(false);

            layout._previewOpened = true;
            fakeTimer.tick(501);

            expect(renderPostStub.called).toBe(true);

            fakeTimer.restore();
            renderPostStub.restore();
        });
    });
});
