describe('Base.Fields.Int', function() {

    var app, field;

    describe('default field definition', function() {
        beforeEach(function() {
            app = SugarTest.app;

            field = SugarTest.createField('base', 'int', 'int', 'detail');
        });

        afterEach(function() {
            sinon.collection.restore();
            field.dispose();
            field = null;
        });

        it('should format/unformat the value based on user preferences', function() {

            var preferenceStub = sinon.collection.stub(app.user, 'getPreference'),
                value = 123456.502;

            // this is to make sure that test still fails if we are relying on a
            // /possible system default precision set to 0
            preferenceStub.withArgs('decimal_precision').returns(4);

            preferenceStub.withArgs('number_grouping_separator').returns('.');
            preferenceStub.withArgs('decimal_separator').returns(',');

            expect(field.format(value)).toEqual('123.457');
            expect(field.unformat('123.457')).toEqual(123457);

            preferenceStub.withArgs('number_grouping_separator').returns(',');
            preferenceStub.withArgs('decimal_separator').returns('.');

            expect(field.format(value)).toEqual('123,457');
            expect(field.unformat('123,457')).toEqual(123457);
        });

        it('should format/unformat zero', function() {
            expect(field.format(0)).toEqual('0');
            expect(field.unformat('0')).toEqual(0);
        });

        it('should not format/unformat a non number string', function() {
            expect(field.format('Asdt')).toEqual('Asdt');
            expect(field.unformat('Asdt')).toEqual('Asdt');
        });
    });

    describe('with disable format', function() {
        beforeEach(function() {
            app = SugarTest.app;

            field = SugarTest.createField('base', 'int', 'int', 'detail', {
                disable_num_format: true
            });
        });

        afterEach(function() {
            sinon.collection.restore();
            field.dispose();
            field = null;
        });

        it('should format the value not based on user preferences', function() {

            var preferenceStub = sinon.collection.stub(app.user, 'getPreference'),
                value = 123456.502;

            // this is to make sure that test still fails if we are relying on
            // a possible system default precision set to 0
            preferenceStub.withArgs('decimal_precision').returns(4);

            preferenceStub.withArgs('number_grouping_separator').returns('.');
            preferenceStub.withArgs('decimal_separator').returns(',');

            expect(field.format(value)).toEqual('123457');
            // unformat should still be based on user preferences, since the
            // user might paste a number from other app
            expect(field.unformat('123.456,502')).toEqual(123456.502);
            expect(field.unformat('123456,502')).toEqual(123456.502);

            preferenceStub.withArgs('number_grouping_separator').returns(',');
            preferenceStub.withArgs('decimal_separator').returns('.');

            expect(field.unformat('123,456.502')).toEqual(123456.502);
            expect(field.unformat('123456.502')).toEqual(123456.502);

        });
    });
});
