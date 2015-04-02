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

describe('Opportunities.Base.Views.Create', function() {
    var app, view, options;

    beforeEach(function() {
        app = SugarTest.app;
        options = {
            meta: {
                panels: [{
                    fields: [{
                        name: 'name'
                    },{
                        name: 'commit_stage',
                        label: 'LBL_COMMIT_STAGE'
                    }]
                }]
            }
        };

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'create');
        SugarTest.testMetadata.set();

        SugarTest.seedMetadata(true, './fixtures');
        view = SugarTest.createView('base', 'Opportunities', 'create', options.meta, null, true);
    });

    afterEach(function() {
        sinon.sandbox.restore();
    });

});
