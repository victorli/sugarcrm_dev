
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
if (!(fixtures)) {
    var fixtures = {};
}
// Make this play nice if fixtures has already been defined for other tests
// so we dont overwrite data
if (!_.has(fixtures, 'metadata')) {
    fixtures.metadata = {};
}
fixtures.metadata.currencies = {
    "-99": {
        id: '-99',
        symbol: "$",
        conversion_rate: "1.0",
        iso4217: "USD"
    },
    //Because obviously everyone loves 1970's Jackson5 hits
    "abc123": {
        id: 'abc123',
        symbol: "€",
        conversion_rate: "0.9",
        iso4217: "EUR"
    }
}
describe("RevenueLineItems.Base.View.Record", function() {
    var app, view, options;

    beforeEach(function() {
        options = {
            meta: {
                panels: [
                    {
                        fields: [
                            {
                                name: "commit_stage"
                            }
                        ]
                    }
                ]
            }
        };

        app = SugarTest.app;
        SugarTest.seedMetadata(true, './fixtures');
        app.user.setPreference('decimal_precision', 2);
        SugarTest.loadComponent('base', 'view', 'record');

        view = SugarTest.createView('base', 'RevenueLineItems', 'record', options.meta, null, true);
    });

    describe('_handleDuplicateBefore', function() {
        var new_model;
        beforeEach(function() {
            new_model = new Backbone.Model();
        });

        afterEach(function() {
            new_model = undefined;
        });

        it('should unset quote_id and quote_name', function() {
            new_model.set({quote_id: '123', quote_name: 'name'});

            view._handleDuplicateBefore(new_model);

            expect(new_model.attributes.quote_id).toBeUndefined();
            expect(new_model.attributes.quote_name).toBeUndefined();
        });
    });

    describe('_handleLikelyChange', function() {
        var model;
        beforeEach(function() {
            model = new app.data.beanModel({'quantity': 2});
        });

        afterEach(function() {
            delete model;
        });

        it('discount_price should not be NaN', function() {
            view._handleLikelyChange(model, '');

            var da = model.get('discount_price');
            expect(_.isNaN(da)).toBeFalsy();
            expect(da).toEqual('');
        });

        it('should not overwrite discount_price', function() {
            model.set('discount_price', '100.00');

            view._handleLikelyChange(model, '200.00');

            var da = model.get('discount_price');
            expect(da).toEqual('100.00');
        });
    });

})
