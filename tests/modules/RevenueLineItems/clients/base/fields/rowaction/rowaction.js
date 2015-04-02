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

describe("RevenueLineItems.Base.Fields.Rowaction", function() {
    var app, field, moduleName = "RevenueLineItems", context, def, model, message;
    
    beforeEach(function() {
        app = SUGAR.App;
        context = app.context.getContext();
        def = {
            type:"rowaction",
            event:"button:delete_button:click",
            name:"delete_button",
            label:"LBL_DELETE_BUTTON_LABEL",
            acl_action:"delete"
        };
            
        model = new Backbone.Model({
            id: 'aaa',
            name: 'boo',
            module: moduleName
            
        });
        
        SugarTest.seedMetadata(true);
        app.metadata.getModule("Forecasts", "config").is_setup = 1;
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
    });
    
    afterEach(function() {
        field = null;
        app = null;
        context = null;
        def = null;
        model = null;
        message = null;
    });
    
    describe("when deleteCommitWarning is called", function() {
        
        beforeEach(function() {
            message = null;
        });
        
        it("should should return WARNING_DELETED_RECORD_RECOMMIT_1 and _2 combined when commit_stage = include", function() {
            model.set("commit_stage", "include");
            field = SugarTest.createField("base", "delete_button", "rowaction", "detail", def, moduleName, model, context, true);
            message = field.deleteCommitWarning();
            expect(message).toEqual('WARNING_DELETED_RECORD_RECOMMIT_1<a href="#Forecasts">Forecasts</a>.  WARNING_DELETED_RECORD_RECOMMIT_2<a href="#Forecasts">Forecasts</a>.');
        });
        
        it("should should return NULL when commit_stage != include", function() {
            model.commit_stage = "exclude";
            field = SugarTest.createField("base", "delete_button", "rowaction", "detail", def, moduleName, model, context, true);
            message = field.deleteCommitWarning();
            expect(message).toEqual(null);
        });
    });
});
