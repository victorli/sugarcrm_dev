
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

describe('Opportunities.Base.Views.Record', function() {
    var app, view, options, sinonSandbox;

    afterEach(function() {
        sinonSandbox.restore();
    });

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();
        options = {
            meta: {
                panels: [
                    {
                        fields: [
                            {
                                name: 'name'
                            },{
                                name: 'commit_stage',
                                label: 'LBL_COMMIT_STAGE'
                            }
                        ]
                    }
                ]
            }
        };

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();
        SugarTest.seedMetadata(true, './fixtures');


        var context = app.context.getContext();

        var model = app.data.createBean('Opportunities'),
            tmpModel = new Backbone.Model();
        model.getRelatedCollection = function() { return tmpModel; };
        sinonSandbox.stub(tmpModel, 'fetch', function() {});
        context.set({
            model: model,
            module: 'Opportunities'
        });
        context.prepare();


        view = SugarTest.createView('base', 'Opportunities', 'record', options.meta, context, true);
    });

});
