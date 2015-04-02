/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

describe("ForecastWorksheets.Base.Field.Int", function () {

    var app, field, moduleName = 'ForecastWorksheets';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadPlugin('ClickToEdit')
        SugarTest.loadComponent('base', 'field', 'int');

        var fieldDef = {
            "name": "test_field",
            "type": "int",
            "len": 4
        };

        field = SugarTest.createField("base", "int", 'int', 'record', fieldDef, moduleName, null, null, true);
    });

    afterEach(function() {
        delete app.plugins.plugins['field']['ClickToEdit'];
        delete app.plugins.plugins['view']['CteTabbing'];
        field = null;
        app = null;
    });

    it('should have ClickToEdit Plugin', function() {
        expect(field.plugins).toContain('ClickToEdit');
    });

    describe('ClickToEdit fieldValueChanged', function() {
        var sandbox = sinon.sandbox.create();
        beforeEach(function() {
            field.value = '1';
        });
        afterEach(function() {
            field.value = undefined;
            sandbox.restore();
        });

        it('should return true when adding 1', function() {
            sandbox.stub(field.$el, 'find', function() {
                return {
                    val: function() {
                        return '+1';
                    }
                }
            });
            expect(field.fieldValueChanged(field)).toBeTruthy();
        });

        it('should return true when subtracting 1', function() {
            sandbox.stub(field.$el, 'find', function() {
                return {
                    val: function() {
                        return '-1';
                    }
                }
            });
            expect(field.fieldValueChanged(field)).toBeTruthy();
        });

        it('should return true when adding a percent', function() {
            sandbox.stub(field.$el, 'find', function() {
                return {
                    val: function() {
                        return '+1%';
                    }
                }
            });
            expect(field.fieldValueChanged(field)).toBeTruthy();
        });

        it('should return true when subtracting a percent', function() {
            sandbox.stub(field.$el, 'find', function() {
                return {
                    val: function() {
                        return '-1%';
                    }
                }
            });
            expect(field.fieldValueChanged(field)).toBeTruthy();
        });

        it('should return false when values are the same', function() {
            sandbox.stub(field.$el, 'find', function() {
                return {
                    val: function() {
                        return '1';
                    }
                }
            });
            expect(field.fieldValueChanged(field)).toBeFalsy();
        });
    });
});
