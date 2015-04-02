describe('View.Views.Base.QuicksearchResultsView', function() {
    var viewName = 'quicksearch-results',
        elementCount = 5,
        view, layout, attachKeyDownStub, disposeKeydownStub,
        triggerBeforeStub, triggerSpy, countRecordElementsStub;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        layout = SugarTest.app.view.createLayout({});
        view = SugarTest.createView('base', undefined, viewName, null, null, null, layout);

        attachKeyDownStub = sinon.collection.stub(view, 'attachKeydownEvent');
        disposeKeydownStub = sinon.collection.stub(view, 'disposeKeydownEvent');
        countRecordElementsStub = sinon.collection.stub(view, 'countRecordElements', function() {
            return elementCount;
        });
        triggerBeforeStub = sinon.collection.stub(view.layout, 'triggerBefore', function() {
            return true;
        });
        triggerSpy = sinon.collection.spy(view.layout, 'trigger');
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinon.collection.restore();
        layout.dispose();
        layout = null;
        view = null;
    });

    describe('quicksearch:dropdown:close', function() {
        var closeStub;
        beforeEach(function() {
            closeStub = sinon.collection.stub(view, 'close');
        });

        it('should call disposeKeydownEvent and close', function() {
            view.layout.trigger('quicksearch:dropdown:close');
            expect(disposeKeydownStub).toHaveBeenCalled();
            expect(closeStub).toHaveBeenCalled();
        });
    });

    describe('quicksearch:results:open', function() {
        var openStub;
        beforeEach(function() {
            openStub = sinon.collection.stub(view, 'open');
        });
        it('should call render and open', function() {
            view.layout.trigger('quicksearch:results:open', function() {
                expect(openStub).toHaveBeenCalled();
            });
        });
    });

    describe('navigate:focus:receive', function() {
        it('should set the first element active and attachKeydownEvent', function() {
            view.trigger('navigate:focus:receive', true);
            expect(attachKeyDownStub).toHaveBeenCalled();
            expect(view.activeIndex).toEqual(0);
        });

        it('should set the last element active and attachKeydownEvent', function() {
            view.trigger('navigate:focus:receive', false);
            expect(attachKeyDownStub).toHaveBeenCalled();
            expect(view.activeIndex).toEqual(4);
        });
    });

    describe('navigate:focus:lost', function() {
        it('should clear the activeIndex and disposeKeydownEvent', function() {
            view.trigger('navigate:focus:lost');
            expect(disposeKeydownStub).toHaveBeenCalled();
            expect(view.activeIndex).toBeNull();
        });
    });

    describe('isFocusable', function() {
        it('should be focusable with results and records', function() {
            view.results = {
                records: []
            };
            var isFocusable = view.isFocusable();
            expect(isFocusable).toBeTruthy();
        });
    });

    describe('moveForward', function() {
        it('should increment the active index if we are in bounds', function() {
            view.activeIndex = 0;
            view.moveForward();
            expect(view.activeIndex).toEqual(1);
        });

        it('should move to the next component if we are out of bounds', function() {
            view.activeIndex = 4;
            view.moveForward();
            expect(triggerBeforeStub).toHaveBeenCalledOnce();
            expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:next:component');
            expect(disposeKeydownStub).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledWith('navigate:next:component');
        });
    });

    describe('moveBackward', function() {
        it('should decrement the active index if we are in bounds', function() {
            view.activeIndex = 4;
            view.moveBackward();
            expect(view.activeIndex).toEqual(3);
        });

        it('should move to the previous component if we are out of bounds', function() {
            view.activeIndex = 0;
            view.moveBackward();
            expect(triggerBeforeStub).toHaveBeenCalledOnce();
            expect(triggerBeforeStub).toHaveBeenCalledWith('navigate:previous:component');
            expect(disposeKeydownStub).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledOnce();
            expect(triggerSpy).toHaveBeenCalledWith('navigate:previous:component');
        });
    });
});
