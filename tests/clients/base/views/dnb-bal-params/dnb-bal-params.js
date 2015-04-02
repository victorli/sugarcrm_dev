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
describe("Dnb-bal-params View", function() {

    var app, view,
        moduleName = 'Contacts',
        viewName = 'dnb-bal-params';

    beforeEach(function() {
        var def = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        var meta = {
            'balSelector' : {
                'dnb_bal_ctry' : {
                    'modelSubKey' : 'CountryISOAlpha2Code-',
                    'modelKey' : 'country',
                    'multiple': true
                },
                'dnb_bal_prescreen_score' : {
                    'modelSubKey' : 'MarketingRiskClassCode-',
                    'modelKey' : 'prescreenScore',
                    'multiple': true
                },
                'dnb_bal_cntct_filter' : {
                    'modelSubKey' : 'InclusionDataDescription-1',
                    'modelKey' : 'balFilter',
                    'multiple' : false,
                    'lookup' : {
                        'prem' : 'IncludeContactsOnlyWithDirectEmailOrDirectPhone'
                    }
                }
            },
            'balParamGroups': {
                'people' : {
                    'balFilter' : {
                        'label' : 'LBL_DNB_BAL_CNTCT_TYPE',
                        'select2' : 'dnb_bal_cntct_filter'
                    }
                }
            }
        };
        app = SUGAR.App;
        var context = app.context.getContext({
            module: moduleName
        });
        context.prepare();
        var layout = SugarTest.createLayout('base', null, 'default', def, null);
        view = SugarTest.createView("base", moduleName, viewName, meta, context, false, layout);
        view.model = new Backbone.Model();
        view.settings = new Backbone.Model();
        view.triggerBAL = jasmine.createSpy("triggerBAL");
    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view.model = null;
        view = null;
    });

    //multiple country parameters can be passed to bal
    //this spec ensures that country params are set correctly
    it("Country params must be set correctly", function() {
        var event = {
            'added': {
                'id': 'IN'
            },
            'target': {
                'name': 'dnb_bal_ctry'
            }
        };
        view.loadData();
        view.mapSelect2Params(event);
        expect(view.model.get('country')).toEqual({'CountryISOAlpha2Code-1': "IN"});
    });

    //multiple d&b prescreen score parameters can be passed to bal
    //this spec ensures that d&b prescreen score params are set correctly
    it("D&B Prescreen score params must be set correctly", function() {
        var event = {
            'added': {
                'id': '10925'
            },
            'target': {
                'name': 'dnb_bal_prescreen_score'
            }
        };
        view.loadData();
        view.mapSelect2Params(event);
        expect(view.model.get('prescreenScore')).toEqual({'MarketingRiskClassCode-1': "10925"});
    });

    it("Bal contact filter must be setup correctly", function() {
        var event = {
            'added': {
                'id': 'all'
            },
            'target': {
                'name': 'dnb_bal_cntct_filter'
            }
        };
        view.loadData();
        view.setBalFilter(event);
        var balFilter = view.model.get('balFilter');
        expect(typeof balFilter).toEqual('undefined');
        event = {
            'added': {
                'id': 'prem'
            },
            'target': {
                'name': 'dnb_bal_cntct_filter'
            }
        };
        view.setBalFilter(event);
        expect(view.model.get('balFilter')).toEqual({'InclusionDataDescription-1': 'IncludeContactsOnlyWithDirectEmailOrDirectPhone'});
    });
});