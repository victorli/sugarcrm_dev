describe('Sugar7.View.Handlebars.helpers', function() {
    var app, savedHelpers;

    beforeEach(function () {
        app = SugarTest.app;
        savedHelpers = Handlebars.helpers;
        SugarTest.loadFile('../include/javascript/sugar7', 'hbs-helpers', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        Handlebars.helpers = savedHelpers;
        app = null;
        SugarTest.testMetadata.dispose();
        sinon.collection.restore();
    });

    describe('moduleIconLabel', function() {
        using('different values', [
            {
                // Exists in app_list_strings['moduleIconList']
                module: 'Accounts',
                expected: 'Ac'
            },
            {
                // Doesn't exist in app_list_strings['moduleIconList']
                // Has LBL_MODULE_NAME_SINGULAR defined.
                module: 'Contacts',
                expected: 'Co'
            },
            {
                // Doesn't exist in app_list_strings['moduleIconList']
                // Doesn't have LBL_MODULE_NAME_SINGULAR defined.
                // Has LBL_MODULE_NAME defined.
                module: 'Leads',
                expected: 'Le'
            },
            {
                // Doesn't exist in app_list_strings['moduleIconList']
                // Has LBL_MODULE_NAME_SINGULAR defined.
                // Is a multi-word module.
                // Note: Product Templates maps to Product Catalog, hence 'PC'.
                module: 'ProductTemplates',
                expected: 'PC'
            },
            {
                // Doesn't exist in app_list_strings['moduleIconList']
                // Has no LBL_MODULE_NAME labels defined in mod strings.
                module: 'FakeModule',
                expected: 'Fa'
            }
        ], function(options) {
            it('should return an appropriate 2-letter icon label', function() {
                expect(Handlebars.helpers.moduleIconLabel(options.module)).toBe(options.expected);
            });
        });
    });

    describe('sub-template helpers', function() {
        var data, options, spy, stub;

        beforeEach(function() {
            spy = sinon.spy();
            data = {name: 'Jack'};
            options = {};
            options.hash = {hashArg1: 'foo', hashArg2: 'bar'};
        });

        afterEach(function() {
            stub.restore();
        });

        describe('subViewTemplate helper', function() {
            it('should make private @variables out of the hash arguments', function() {
                stub = sinon.stub(app.template, 'getView').returns(spy);
                Handlebars.helpers.subViewTemplate('key', data, options);
                expect(spy.args[0][1].data.hashArg1).toEqual('foo');
                expect(spy.args[0][1].data.hashArg2).toEqual('bar');
            });
        });

        describe('subFieldTemplate helper', function() {
            it('should make private @variables out of the hash arguments', function() {
                stub = sinon.stub(app.template, 'getField').returns(spy);
                Handlebars.helpers.subFieldTemplate('fieldName', 'view', data, options);
                expect(spy.args[0][1].data.hashArg1).toEqual('foo');
                expect(spy.args[0][1].data.hashArg2).toEqual('bar');
            });
        });

        describe('subLayoutTemplate helper', function() {
            it('should make private @variables out of the hash arguments', function() {
                stub = sinon.stub(app.template, 'getLayout').returns(spy);
                Handlebars.helpers.subLayoutTemplate('key', data, options);
                expect(spy.args[0][1].data.hashArg1).toEqual('foo');
                expect(spy.args[0][1].data.hashArg2).toEqual('bar');
            });
        });
    });

    describe('loading', function() {

        it('should translate the string passed and escape it if needed', function() {
            sinon.collection.stub(app.lang, 'get').withArgs('LBL_LOADING').returns('Loading Text');

            var tpl = Handlebars.compile('{{loading "LBL_LOADING"}}');
            var result = tpl();

            expect($(result).text()).toEqual('Loading Text...');
        });

        it('should escape the string passed', function() {
            sinon.collection.stub(app.lang, 'get').withArgs('LBL_HTML').returns('<script>alert()</script>');

            var tpl = Handlebars.compile('{{loading "LBL_HTML"}}');
            var result = tpl();

            expect($(result).text()).toEqual('<script>alert()</script>...');
        });

        it('should allow classes to be passed to the helper', function() {
            sinon.collection.stub(app.lang, 'get').withArgs('LBL_LOADING').returns('Loading Text');

            var tpl = Handlebars.compile('{{loading "LBL_HTML" cssClass="my-class other-class"}}');
            var result = tpl();

            var $el = $(result);
            expect($el).toHaveClass('my-class');
            expect($el).toHaveClass('other-class');
        });

    });

});
