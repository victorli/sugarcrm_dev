describe('View.Layouts.Base.QuicksearchLayout', function() {
    var layout, viewA, viewB, viewC,
        viewAisFocusable, viewBisFocusable, viewCisFocusable;


    var createMockView = function(name) {
        return {
            name: name,
            initialize: $.noop,
            render: $.noop,
            dispose: $.noop,
            trigger: sinon.collection.stub()
        };
    };

    beforeEach(function() {
        SugarTest.testMetadata.init();
        viewA = createMockView('view-a');
        viewB = createMockView('view-b');
        viewC = createMockView('view-c');
        viewA.isFocusable = function() {return viewAisFocusable};
        viewB.isFocusable = function() {return viewBisFocusable};
        viewC.isFocusable = function() {return viewCisFocusable};
        SugarTest.addComponent('base', 'view', 'view-a', viewA);
        SugarTest.addComponent('base', 'view', 'view-b', viewB);
        SugarTest.addComponent('base', 'view', 'view-c', viewC);
        SugarTest.testMetadata.set();
        var defaultMeta = {
            components: [
                {view: 'view-a'},
                {view: 'view-b'},
                {view: 'view-c'}
            ]
        };
        layout = SugarTest.createLayout('base', null, 'quicksearch', defaultMeta);
        layout.initialize(layout.options);
        layout.initComponents();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        layout.dispose();
        sinon.collection.restore();
        layout = viewA = viewB = viewC = viewAisFocusable = viewBisFocusable = viewCisFocusable = null;
    });

    describe('firing navigation events on components', function() {
        it('should trigger events on the component in the forward case', function() {
            layout.trigger('navigate:next:component');
            expect(viewA.trigger).toHaveBeenCalledWith('navigate:focus:receive');
        });

        it('should trigger events on the component in the backward case', function() {
            layout.trigger('navigate:previous:component');
            expect(viewA.trigger).toHaveBeenCalledWith('navigate:focus:receive');
        });
    });

    describe('navigate:next:component', function() {
        beforeEach(function() {
            viewAisFocusable = true;
        });

        it('should find the next focusable component', function() {
            viewBisFocusable = true;
            layout.triggerBefore('navigate:next:component');
            expect(layout.compOnFocusIndex).toEqual(1);
        });

        it('should skip unfocusable components', function() {
            viewBisFocusable = false;
            viewCisFocusable = true;
            layout.triggerBefore('navigate:next:component');
            expect(layout.compOnFocusIndex).toEqual(2);
        });

        it('should skip nav if there are no focusable components', function() {
            viewBisFocusable = false;
            viewCisFocusable = false;
            layout.triggerBefore('navigate:next:component');
            expect(layout.compOnFocusIndex).toEqual(0);
        });
    });

    describe('navigate:previous:component', function() {
        beforeEach(function() {
            viewCisFocusable = true;
            layout.compOnFocusIndex = 2;
        });

        it('should find the previous focusable component', function() {
            viewBisFocusable = true;
            layout.triggerBefore('navigate:previous:component');
            expect(layout.compOnFocusIndex).toEqual(1);
        });

        it('should skip unfocusable components', function() {
            viewAisFocusable = true;
            viewBisFocusable = false;
            layout.triggerBefore('navigate:previous:component');
            expect(layout.compOnFocusIndex).toEqual(0);
        });

        it('should skip nav if there are no focusable components', function() {
            viewAisFocusable = false;
            viewBisFocusable = false;
            layout.triggerBefore('navigate:previous:component');
            expect(layout.compOnFocusIndex).toEqual(2);
        });
    });
    describe('navigate:to:component', function() {
        beforeEach(function() {
            viewAisFocusable = true;
            viewBisFocusable = true;
            layout.compOnFocusIndex = 0;
        });
        it('should navigate directly to the specified component', function() {
            layout.trigger('navigate:to:component', 'view-b');
            expect(viewA.trigger).toHaveBeenCalledWith('navigate:focus:lost');
            expect(viewB.trigger).toHaveBeenCalledWith('navigate:focus:receive');
            expect(layout.compOnFocusIndex).toEqual(1);
        });
    });
    describe('quicksearch:clear', function() {
        beforeEach(function() {
            viewAisFocusable = true;
            layout.compOnFocusIndex = 0;
        });
        it('should lose focus', function() {
            layout.trigger('quicksearch:clear');
            expect(viewA.trigger).toHaveBeenCalledWith('navigate:focus:lost');
        });
    });
});
