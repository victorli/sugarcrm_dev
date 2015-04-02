
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
if(!_.has(fixtures, 'metadata')) {
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
describe('RevenueLineItems.Base.View.Create', function() {
    var app, view, options;

    beforeEach(function() {
        options = {
            meta: {
                panels: [{
                    fields: [{
                        name: "commit_stage"
                    }]
                }]
            }
        };

        app = SugarTest.app;
        SugarTest.seedMetadata(true, './fixtures');
        app.user.setPreference('decimal_precision', 2);
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'create');

        view = SugarTest.createView('base', 'RevenueLineItems', 'create', options.meta, null, true);
    });

    describe("initialization", function() {
        beforeEach(function() {
            sinon.stub(app.view.views.BaseCreateView.prototype, "initialize");

            sinon.stub(app.metadata, "getModule", function () {
                return {
                    is_setup: true,
                    buckets_dom: "commit_stage_binary_dom"
                }
            })

        });

        afterEach(function() {
            view._parsePanelFields.restore();
            app.metadata.getModule.restore();
            app.view.views.BaseCreateView.prototype.initialize.restore();
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
