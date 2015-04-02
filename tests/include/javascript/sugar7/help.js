describe('Sugar7 Help Extension', function () {
    var app;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        sinon.collection.stub(app.metadata, 'getModuleNames').returns([
            'Accounts',
            'Bugs',
            'Cases',
            'Contacts',
            'RevenueLineItems'
        ]);
    });

    afterEach(function () {
        app.help.clearModuleLabelMap();
        SugarTest.testMetadata.dispose();
        sinon.collection.restore();
    });

    describe('help.get', function() {
        it('should return the correct language strings', function() {
            var helpText = app.help.get('Accounts', 'Record');

            expect(helpText.title).toEqual('Accounts Help Record Title');
            expect(helpText.body).toEqual('Accounts Help Record Body');
        });

        it('should return undefined for title and body', function() {
            var helpText = app.help.get('Accounts', 'Compose');

            expect(helpText.title).toBeUndefined();
            expect(helpText.body).toBeUndefined();
        });

        it('should fall back to defaults when not found in module', function() {
            var helpText = app.help.get('RevenueLineItems', 'Record');

            expect(helpText.title).toEqual("Default Help Record Title");
            expect(helpText.body).toEqual("Default Help Record Body");
        });
    });

    describe('help.get module substitution', function() {
        it('should return the correct language strings for current module', function() {
            var helpText = app.help.get('Accounts', 'Records');

            expect(helpText.title).toEqual('Accounts Help Records Title');
            expect(helpText.body).toEqual('Account Help Records Body');
        });

        it('should return the correct language strings with other module names', function() {
            var helpText = app.help.get('Accounts', 'Create');

            expect(helpText.title).toEqual('My Revenue Line Item');
            expect(helpText.body).toEqual('My Revenue Line Items');
        });
    });

    describe('clearModuleLabelMap', function() {
        it('should set moduleLabelMap to undefined', function() {
            // should be undefined to start with
            expect(app.help._moduleLabelMap).toBeUndefined();
            var helpText = app.help.get('Accounts', 'Create');
            // we should have something after the initial call was made
            expect(app.help._moduleLabelMap).not.toBeUndefined();
            app.help.clearModuleLabelMap();

            // should be set back to undfined now
            expect(app.help._moduleLabelMap).toBeUndefined();
        });
    });
});
