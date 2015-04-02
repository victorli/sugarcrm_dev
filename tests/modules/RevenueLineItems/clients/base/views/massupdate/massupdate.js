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
describe("RevenueLineItems.Base.Views.Massupdate", function() {
    var app, view, layout, moduleName = "RevenueLineItems", context, options, message;
    
    beforeEach(function() {
        app = SUGAR.App;
        context = app.context.getContext();
        options = {
            meta: {
                panels: [{
                    fields: []
                }]
            }
        };
        
        SugarTest.seedMetadata(true);
        SugarTest.loadComponent('base', 'view', 'massupdate');

        layout = SugarTest.createLayout("base", moduleName, "list", null, null);
        view = SugarTest.createView("base", moduleName, "massupdate", options, context, true, layout);
    });
    
    afterEach(function() {
        view = null;
        app = null;
        context = null;
        options = null;
        layout = null;
        message = null;
    });
    
    describe("when trying to delete an RLI with commit_stage = include", function() {
        
        beforeEach(function() {
            message = null;
            sinon.stub(view, "getMassUpdateModel", function() {
                return {models:[new Backbone.Model({
                        id: "aaa",
                        name: "boo",
                        module: moduleName,
                        commit_stage: "include"
                    })]
                };
            });
        });
        
        afterEach(function() {
            view.getMassUpdateModel.restore();
        });
        
        it("should should return WARNING_DELETED_RECORD_LIST_RECOMMIT_1 and _2 combined", function() {
            message = view.deleteCommitWarning(view.getMassUpdateModel().models);
            expect(message).toEqual('WARNING_DELETED_RECORD_LIST_RECOMMIT_1<a href="#Forecasts">Forecasts</a>.  WARNING_DELETED_RECORD_LIST_RECOMMIT_2<a href="#Forecasts">Forecasts</a>.');
        });
    });
    
    describe("when trying to delete an RLI with commit_stage != include", function() {
        
        beforeEach(function() {
            message = null;
            sinon.stub(view, "getMassUpdateModel", function() {
                return {models:[new Backbone.Model({
                        id: "aaa",
                        name: "boo",
                        module: moduleName,
                        commit_stage: "exclude"
                    })]
                };
            });
        });
        
        afterEach(function() {
            view.getMassUpdateModel.restore();
        });
        
        it("should should return NULL", function() {
            message = view.deleteCommitWarning(view.getMassUpdateModel().models);
            expect(message).toEqual(null);
        });
    });
});
