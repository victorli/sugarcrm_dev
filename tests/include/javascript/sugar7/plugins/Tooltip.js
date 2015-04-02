describe('Tooltip Plugin', function() {
    var field, tooltipInitializeSpy, tooltipDestroySpy, origFieldPlugins;

    beforeEach(function() {
        origFieldPlugins = SugarTest.app.plugins.plugins.field;
        SugarTest.app.plugins.plugins.field = {};
        field = new Backbone.View();
        field.$el.append('<a rel="tooltip" title="foo">foo</a>');
        field.plugins = ['Tooltip'];
        SugarTest.loadPlugin('Tooltip');
        SugarTest.app.plugins.attach(field, 'field');

        tooltipInitializeSpy = sinon.spy(SugarTest.app.utils.tooltip, 'initialize');
        tooltipDestroySpy = sinon.spy(SugarTest.app.utils.tooltip, 'destroy');

    });

    afterEach(function() {
        field.destroyAllPluginTooltips();
        SugarTest.app.plugins.plugins.field = {};
        tooltipInitializeSpy.restore();
        tooltipDestroySpy.restore();
        SugarTest.app.plugins.plugins.field = origFieldPlugins;
    });

    describe('Bootstrap Tooltip plugin', function() {
        it('should exist', function() {
            expect($.fn.tooltip).toBeDefined();
        });
    });

    describe('initializeAllPluginTooltips', function() {
        it('should create tooltips that has rel attribute value of tooltip', function() {
            field.initializeAllPluginTooltips();

            expect(field._$pluginTooltips.length).toBe(1);
            expect(tooltipInitializeSpy.calledOnce).toBe(true);
        });
    });

    describe('destroyAllPluginTooltips', function() {
        it('should destroy all existing tooltips', function() {
            field.initializeAllPluginTooltips();
            field.destroyAllPluginTooltips();

            expect(tooltipDestroySpy.calledOnce).toBe(true);
            expect(field._$pluginTooltips).toBeNull();
        });
    });

    describe('addPluginTooltips', function() {
        it('should add tooltips given a specific element', function() {
            field.initializeAllPluginTooltips();

            expect(field._$pluginTooltips.length).toBe(1);

            field.$el.append('<span id="more"><a rel="tooltip" title="bar">tooltip</a></span>');
            field.addPluginTooltips(field.$('#more'));

            expect(field._$pluginTooltips.length).toBe(2);
            expect(tooltipInitializeSpy.calledTwice).toBe(true);
        });
    });

    describe('removePluginTooltips', function() {
        it('should remove tooltips within a given element', function() {
            field.$el.append('<span id="more"><a rel="tooltip" title="bar">tooltip</a></span>');
            field.initializeAllPluginTooltips();

            field.removePluginTooltips(field.$('#more'));

            expect(field._$pluginTooltips.length).toBe(2);
            expect(tooltipDestroySpy.calledOnce).toBe(true);
        });
    });
});
