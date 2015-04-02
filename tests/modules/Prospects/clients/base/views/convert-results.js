describe("Prospects.Views.ConvertResults", function() {
    var app, view, populateLeadCallbackSpy,
       leadId = '123';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('convert-results', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'convert-results');
        SugarTest.loadComponent('base', 'view', 'convert-results', 'Prospects');

        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModel('Leads', {});
        view = SugarTest.createView('base', 'Prospects', 'convert-results', null, null, true);

        populateLeadCallbackSpy = sinon.spy(view, 'populateLeadCallback');
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        populateLeadCallbackSpy.restore();
    });

    it("should have no models in collection", function() {
        expect(view.associatedModels.length).toEqual(0);
        expect(populateLeadCallbackSpy.called).toBeFalsy();
    });

    it("should have lead model with name in collection", function() {
        var leadName ='Test User';

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET",  /.*rest\/v10\/Leads.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify( new Backbone.Model({ id: "xyz", name: leadName}))]);

        view.model.set({
            lead_id: leadId
        });

        SugarTest.server.respond();

        expect(populateLeadCallbackSpy.called).toBeTruthy();
        expect(view.associatedModels.length).toEqual(1);
        expect(view.associatedModels.at(0).get('name')).toEqual(leadName);
    });
});
