describe('Base.Layout.Shortcuts', function() {
    var app, layout, origMousetrap;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'shortcuts');
        SugarTest.loadHandlebarsTemplate('shortcuts', 'layout', 'base');
        SugarTest.loadHandlebarsTemplate('shortcuts', 'layout', 'base', 'shortcuts-help-table');
        SugarTest.testMetadata.set();

        origMousetrap = Mousetrap;
        Mousetrap = {
            bind: sinon.stub(),
            unbind: sinon.stub()
        }
    });

    afterEach(function() {
        app.shortcuts._activeSession = null;
        app.shortcuts._savedSessions = [];
        app.shortcuts._globalShortcuts = {};
        Mousetrap = origMousetrap;

        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    it('Should display global shortcut help table', function() {
        var expectedResult = '<tr><td>a</td><td>Foo</td></tr><tr><td>b, c</td><td>Bar</td></tr>';

        app.shortcuts.register(app.shortcuts.GLOBAL + 'foo', 'a', $.noop, new Backbone.View());
        app.shortcuts.register(app.shortcuts.GLOBAL + 'bar', ['b','c'], $.noop, new Backbone.View());

        layout = SugarTest.createLayout('base', 'Contacts', 'shortcuts', {
            help: {
                'Global:foo': 'Foo',
                'Global:bar': 'Bar'
            }
        });

        expect(layout.$('[data-display=global]').html()).toBe(expectedResult);
    });

    it('Should display contextual shortcut help table', function() {
        var expectedResult = '<tr><td>a</td><td>Foo</td></tr><tr><td>b, c</td><td>Bar</td></tr>',
            firstLayout = new Backbone.View();

        app.shortcuts.createSession(['foo','bar'], firstLayout);
        app.shortcuts.register('foo', 'a', $.noop, firstLayout);
        app.shortcuts.register('bar', ['b','c'], $.noop, firstLayout);
        app.shortcuts.saveSession();

        layout = SugarTest.createLayout('base', 'Contacts', 'shortcuts', {
            help: {
                foo: 'Foo',
                bar: 'Bar'
            }
        });

        expect(layout.$('[data-display=contextual]').html()).toBe(expectedResult);
    });
});
