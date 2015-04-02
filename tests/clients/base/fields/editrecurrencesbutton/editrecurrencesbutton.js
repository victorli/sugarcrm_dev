describe('Base.Fields.Editrecurrencesbutton', function() {
    var app, field, sandbox;

    beforeEach(function() {
        var fieldName = 'editrecurrencesbutton';
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        field = SugarTest.createField('base', fieldName, fieldName);
        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('Hiding the button for non-recurring events', function() {
        var hideStub;

        beforeEach(function() {
            hideStub = sandbox.stub(field, 'hide');
        });

        it('should not hide button when repeat_type field is set', function() {
            field.model.set('repeat_type', 'Weekly');
            expect(hideStub).not.toHaveBeenCalled();
        });

        it('should hide button when repeat_type field is not set', function() {
            field.model.set('repeat_type', '');
            expect(hideStub).toHaveBeenCalled();
        });
    });

    it('should re-render field when repeat_type field is changed', function() {
        var spy = sandbox.spy(field, '_render');
        field.model.set('repeat_type', '', {silent: true});
        field.model.set('repeat_type', 'true');
        expect(spy.callCount).toBe(1);
    });
});
