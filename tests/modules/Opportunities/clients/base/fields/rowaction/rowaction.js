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

describe("Opportunities.Base.Fields.Rowaction", function() {
    
    var app, field, moduleName = 'Opportunities', context, def, model;
    
    beforeEach(function() {
        app = SUGAR.App;
        context = app.context.getContext();
        
        SugarTest.loadFile('../modules/Forecasts/clients/base/plugins', 'DisableDelete', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

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
        delete app.plugins.plugins['field']['DisableDelete'];
        field = null;
        app = null;
        context = null;
        def = null;
        model = null;
    });
    
    describe("when closed_revenue_line_items changes", function() {
        beforeEach(function() {
            field = SugarTest.createField("base", "delete_button", "rowaction", "detail", def, moduleName, model, context, true);
            field.view = {
                    STATE: {VIEW:"detail"},
                    initButtons: function(){},
                    setButtonStates: function(){}
            };
            
            sinon.spy(field, "render");
            sinon.spy(field.view, "initButtons");
            sinon.spy(field.view, "setButtonStates");
            
            model.set("closed_revenue_line_items", "1");
        });
        afterEach(function() {
            field.render.restore();
            field.view.setButtonStates.restore();
            field.view.initButtons.restore();
        });
        
        it("should call render on the rowaction", function() {
            expect(field.render.called).toBe(true);
        });
        
        it("should call initButtons on the view", function() {
            expect(field.view.initButtons.called).toBe(true);
        });
        
        it("should call setButtonStates on the view", function() {
            expect(field.view.setButtonStates.called).toBe(true);
        });
    });
});
