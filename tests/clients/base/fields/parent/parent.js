describe('Base.Field.Parent', function() {

    var app, field;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        var fieldDef = {
            "name": "parent_name",
            "rname": "name",
            "vname": "LBL_ACCOUNT_NAME",
            "type": "relate",
            "link": "accounts",
            "table": "accounts",
            "join_name": "accounts",
            "isnull": "true",
            "module": "Accounts",
            "dbType": "varchar",
            "len": 100,
            "source": "non-db",
            "unified_search": true,
            "comment": "The name of the account represented by the account_id field",
            "required": true, "importable": "required"
        };

        SugarTest.loadComponent("base", "field", "relate");
        field = SugarTest.createField("base","parent_name", "parent", "edit", fieldDef);
        field.model = new Backbone.Model({parent_type: "Contacts", parent_id: "111-222-33333", parent_name: "blob"});

        if (!$.fn.select2) {
            $.fn.select2 = function(options) {
                var obj = {
                    on : function() {
                        return obj;
                    }
                };
                return obj;
            };
        }
    });
    afterEach(function() {
        sinon.collection.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.dispose();
    });

    it('should not set value when id is undefined', function() {
        var expected_module = 'Accounts';

        field.model.clear();
        field.setValue({id: undefined, value: undefined, module: expected_module});
        var actual_id = field.model.get('parent_id'),
            actual_name = field.model.get('parent_name'),
            actual_module = field.model.get('parent_type');
        expect(actual_id).toBeUndefined();
        expect(actual_name).toBeUndefined();
        expect(actual_module).toEqual(expected_module);
    });

    it("should set value correctly", function() {
        var expected_id = '0987',
            expected_name = 'blahblah',
            expected_module = 'Accounts';

        field.setValue({id: expected_id, value: expected_name, module: expected_module});
        var actual_id = field.model.get('parent_id'),
            actual_name = field.model.get('parent_name'),
            actual_module = field.model.get('parent_type');
        expect(actual_id).toEqual(expected_id);
        expect(actual_name).toEqual(expected_name);
        expect(actual_module).toEqual(expected_module);
    });
    it("should deal get related module for parent", function() {
        var actual_id = field.model.get('parent_id'),
            actual_module = field.model.get('parent_type'),
            _relatedModuleSpy = sinon.collection.spy(field, 'getSearchModule'),
            _relateIdSpy = sinon.collection.spy(field, '_getRelateId');

        field.format();
        expect(_relatedModuleSpy).toHaveBeenCalled();
        expect(_relateIdSpy).toHaveBeenCalled();
        expect(field.href).toEqual("#"+actual_module+"/"+actual_id);
    });

    describe('isAvailableParentType', function () {
        it('should return true if the specified module is an option on the field', function () {
            field.typeFieldTag = 'select';
            field.$el.html('<select><option value="Accounts">Account</option></select>');
            expect(field.isAvailableParentType('Accounts')).toBe(true);
        });
        it('should return false if the specified module is not an option on the field', function () {
            field.typeFieldTag = 'select';
            field.$el.html('<select><option value="Accounts">Account</option></select>');
            expect(field.isAvailableParentType('Contacts')).toBe(false);
        });
    });

    describe('render', function() {
        var _renderStub, getSearchModuleStub;

        beforeEach(function() {
            _renderStub = sinon.collection.stub(app.view.Field.prototype, '_render');
            getSearchModuleStub = sinon.collection.stub(field, 'getSearchModule');
        });

        using('different search modules', [
            {
                module: undefined,
                render: true
            },
            {
                module: 'invalidModule',
                render: false
            },
            {
                module: 'Cases',
                render: true
            }
        ], function(options) {

            it('should not render if the related module is invalid', function() {
                getSearchModuleStub.returns(options.module);
                field.render();

                expect(_renderStub.called).toBe(options.render);
            });
        });
    });
});
