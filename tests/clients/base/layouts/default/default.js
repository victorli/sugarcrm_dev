describe('Base.Layout.Default', function() {
    var layout, app, def;

    beforeEach(function() {
        app = SugarTest.app;
        def = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'default');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        layout = SugarTest.createLayout('base', null, 'default', def, null);
    });

    afterEach(function() {
        sinon.collection.restore();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('listeners', function() {
        var toggleSidePaneStub;

        beforeEach(function() {
            sinon.collection.stub(layout, 'processDef');
            toggleSidePaneStub = sinon.collection.stub(layout, 'toggleSidePane');
            layout.initialize({ meta: def });
        });

        it('should toggle side pane when "sidebar:toggle" is triggered', function() {
            layout.trigger('sidebar:toggle');
            expect(toggleSidePaneStub).toHaveBeenCalled();
        });
    });

    describe('isSidePaneVisible', function() {
        var lastStateStub, lastState;

        beforeEach(function() {
            lastStateStub = sinon.collection.stub(app.user.lastState, 'get', function() {
                return lastState;
            });
        });

        using('different states and calling isSidePaneVisible', [
            {
                'lastState': '0',
                'expected': true
            },
            {
                'lastState': '1',
                'expected': false
            },
            {
                'lastState': undefined,
                'expected': true
            }
        ], function (option) {
            it('should return the proper value', function() {
                lastState = option.lastState;
                expect(layout.isSidePaneVisible()).toBe(option.expected);
            });
        });

        describe('when the default hide is set to "1"', function() {
            beforeEach(function (){
                def['default_hide'] = '1';
                layout.initialize({ meta: def });
            });
            it('should default false', function() {
                lastState = undefined;
                expect(layout.isSidePaneVisible()).toBeFalsy();
            });
        });
    });

    describe('toggleSidePane', function() {
        var isSidePaneVisibleStub, isSidePaneVisible,
            lastStateSetStub,
            _toggleVisibilityStub,
            validHideLastStateKey;

        beforeEach(function() {
            isSidePaneVisibleStub = sinon.collection.stub(layout, 'isSidePaneVisible', function() {
                return isSidePaneVisible;
            });
            lastStateSetStub = sinon.collection.stub(app.user.lastState, 'set');
            _toggleVisibilityStub = sinon.collection.stub(layout, '_toggleVisibility');
            validHideLastStateKey = 'default:hide';
        });

        describe('when "true" is passed', function() {
            it('should set key to 0 and call _toggleVisibility with "true"', function() {
                isSidePaneVisible = false;
                layout.toggleSidePane(true);
                expect(lastStateSetStub).toHaveBeenCalledWith(validHideLastStateKey, '0');
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });

            it('should ignore because side pane is already visible', function() {
                isSidePaneVisible = true;
                layout.toggleSidePane(true);
                expect(lastStateSetStub).not.toHaveBeenCalled();
                expect(_toggleVisibilityStub).not.toHaveBeenCalled();
            });
        });

        describe('when "false" is passed', function() {
            it('should set key to 1 and call _toggleVisibility with "false"', function() {
                isSidePaneVisible = true;
                layout.toggleSidePane(false);
                expect(lastStateSetStub).toHaveBeenCalledWith(validHideLastStateKey, '1');
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });

            it('should ignore because side pane is already hidden', function() {
                isSidePaneVisible = false;
                layout.toggleSidePane(false);
                expect(lastStateSetStub).not.toHaveBeenCalled();
                expect(_toggleVisibilityStub).not.toHaveBeenCalled();
            });
        });

        describe('when nothing is passed', function() {
            it('should set key to 1 and call _toggleVisibility with "false"', function() {
                isSidePaneVisible = true;
                layout.toggleSidePane();
                expect(lastStateSetStub).toHaveBeenCalledWith(validHideLastStateKey, '1');
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });

            it('should set key to 0 and call _toggleVisibility with "true"', function() {
                isSidePaneVisible = false;
                layout.toggleSidePane();
                expect(lastStateSetStub).toHaveBeenCalledWith(validHideLastStateKey, '0');
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });
        });

        describe('when the last state key is manually defined', function(){
            beforeEach(function () {
                validHideLastStateKey = 'default:hide-test';
                def['hide_key'] = 'hide-test';
                layout.initialize({ meta: def });
            });
            it('should use the defined last state key', function () {
                isSidePaneVisible = undefined;
                layout.toggleSidePane();
                expect(lastStateSetStub).toHaveBeenCalledWith(validHideLastStateKey, '0');
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });
        })
    });


    describe('_toggleVisibility', function() {
        var resizeStub, triggerStub;

        beforeEach(function() {
            resizeStub = sinon.collection.stub($.fn, 'trigger');
            triggerStub = sinon.collection.stub(layout, 'trigger');
        });

        it('should call window "resize"', function() {
            layout._toggleVisibility(true);
            expect(resizeStub).toHaveBeenCalledWith('resize');
            expect(triggerStub).toHaveBeenCalledWith('sidebar:state:changed');
        });
    });

});
